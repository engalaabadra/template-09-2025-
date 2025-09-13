<?php
namespace App\Services\Dashboard\Auth\User;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Base\BaseRepository;
use App\Services\Dashboard\Auth\User\UserServiceInterface;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Scopes\ActiveScope;
use App\Services\Translation\TranslationService;
use App\Models\Profile;
use App\Repositories\Dashboard\Auth\User\UserRepository;
use App\Services\ServiceResponse;
use App\Enums\IsActiveEnum;
use App\Repositories\Eloquent\EloquentRepository;
use App\Helpers\RoleHelper;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Traits\Services\HandlesServiceTransactions;
use App\Traits\Services\HandlesBulkOperationsTrait;
use App\Enums\ActionMethodNameEnum;
use App\Enums\ActivationActionEnum;
use App\Enums\StrategyActionEnum;
use App\Enums\RestoringDeletingActionEnum;

/**
 * Class UserService
 *
 * Provides business logic for user-related operations such as
 * creating, updating, deleting, restoring, and managing user activation.
 */
class UserService extends EloquentService implements UserServiceInterface
{
    use HandlesServiceTransactions, HandlesBulkOperationsTrait;

    /** @var EloquentRepository */
    protected $eloquentRepo;

    /** @var BaseRepository */
    protected $baseRepo;

    /** @var TranslationService */
    protected $translationService;

    /** @var Profile */
    protected $profile;

    /**
     * Constructor
     *
     * @param EloquentRepository    $eloquentRepo
     * @param BaseRepository    $baseRepo
     * @param UserRepository        $userRepo
     * @param Profile               $profile
     * @param TranslationService    $translationService
     */
    public function __construct( EloquentRepository $eloquentRepo, BaseRepository $baseRepo, UserRepository $userRepo, Profile $profile, TranslationService $translationService)
    {
        $this->eloquentRepo = $eloquentRepo;
        $this->baseRepo = $baseRepo;
        $this->userRepo = $userRepo;
        $this->profile = $profile;
        $this->translationService = $translationService;
    }
     
    #region ===================== Start CRUD Methods: index(), show($id) ====================
    
    /**
     * Store a new record.
     * @param object $request The request object containing validated data.
     * @param object $model The model to query.
     * @return object Created record with optional eager loading.
     */
    protected function store($request, $model)
    {
        // Get validated data and filter out 'roles' and 'image'
        $data = $request->validated();

        $enteredDataUser = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'roles', 'image', 'files')
        $enteredDataProfile = Profile::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'roles', 'image', 'files')

        // Generate random password
        $randomStr = Str::random(8);
        $enteredDataUser['password'] = $randomStr;

        // Create the model item
        $newUser = $model->create($enteredDataUser);

        // Assign roles to user
        $newUser->roles()->attach($data['roles']);

        // Create profile for this user
        $newUser->profile()->create($enteredDataProfile);

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($enteredDataUser, $newUser);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $newUser->profile, $request->get('translations'));
        }

        //Handle Files
        $newUser->handleFiles($request, $model, $newUser);//exist in HasMediaTrait
        
        // Load related models if eager loading is defined
        return $model->getProp('eagerLoading') ? $newUser->load($model->getProp('eagerLoading')) : $newUser;
    }

    /**
     * Update a specific record.
     * @param object $request The request object containing validated data.
     * @param int $id The ID of the record to update.
     * @param object $model The model to query.
     * @return object Updated record.
     */
    protected function update($request, $id, $model, $forUser = false)
    {
        // Get validated data and find the record
        $data = $request->validated();

        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'roles', 'image', 'files')

        // Update user
        $user->update($enteredData);

        // Sync roles
        $user->roles()->sync($data['roles']);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $user->profile, $request->get('translations'));
        }

        //Handle Files
        $user->handleFiles($request, $model, $user);//exist in HasMediaTrait

        // Load related models if eager loading is defined
        return $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;
    }

       
    /**
     * Permanently delete a single trashed item.
     *
     * @param mixed $id The ID of the item to force delete.
     * @param object $model The model to query.
     * @return \Illuminate\Http\JsonResponse|null
     */
    protected function forceDelete($id, $model, $forUser = false)
    {
        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMainTrash($id);

        // Permanently delete the item
        $user->forceDelete();
    }


    #endregion ===================== End CRUD Methods =====================

    #region ===================== Start TRASH Methods: destroy($id, $model, $forUser), destroyMany($request, $model, $forUser), restore($request, $id, $model, $forUser), restoreMany($request, $model, $forUser) =====================
    /**
     * Delete a single item by ID.
     *
     * @param mixed $id The ID of the item to delete.
     * @param object $model The model to query.
     * @return object|\Illuminate\Http\JsonResponse The deleted item or 404 response.
     */
    protected function destroy($id, $model, $forUser = false)
    {
        // Get the item by ID, excluding trashed and global scopes
        $user = $this->baseRepo->findWithoutTrashedOrFail($id, $model, $forUser);// Only soft delete if not trashed
        
        // Find a user by ID, ignoring protected main users , without trash
        $user = $model->findUserExceptMainWithoutTrash($id);

        // Soft delete the item (if model uses SoftDeletes), or permanently delete it
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
            $this->destroyItem($user);
        } else {
            $user->delete();//permanently delete it, because this mode not contain trash
        }
        

        $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
            ? $user->load($model->getProp('eagerLoading'))
            : $user;

        return $data;

    }



    /**
     * Restore a trashed record.
     *
     * @param int $id The ID of the trashed record to restore.
     * @param object $model The model to query.
     * @return object Restored record.
     */
    protected function restore($request, $id, $model, $forUser = false)
    {
        $data = $request->validated();

        // Get the action to perform: modify (default), replace, or prevent
        $strategy = $data['strategy'] ?? StrategyActionEnum::MODIFY->value;

        // Find the trashed record by ID or return 404
        $user = $this->baseRepo->findOnlyTrashedOrFail($id, $model, $forUser);

        // Find a trashed user by ID, ignoring protected main users
        $user = $model->findUserExceptMainTrash($id);

        if ($user->trashed()) { // Only restore if actually trashed
            $this->restoreItem($user, $model, $strategy);
        }
        // Eager load relations if defined
        $data = $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;

        return $data;

    }



    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model, $forUser), changeActivateMany($request, $model, $forUser)=====================

    /**
     * Toggle activation status for a record.
     *
     * @param int    $id     The ID of the record to toggle activation.
     * @param object $model  The model to query.
     * @return object        Updated record with toggled activation status.
     */
    public function changeActivate($request, $id, $model, $forUser = false)
    {
        $data = $request->validated();

        // Find a user by ID, ignoring protected main users , without trash
        $user = $model->findUserExceptMainWithoutTrash($id);

        // Get the action to perform: 'activate', 'deactivate', or 'toggle' (default: toggle)
        $action = $data['action_activation'] ?? ActivationActionEnum::TOGGLE->value;

        // Get the action to perform: modify (default), replace, or prevent
        $strategy = $data['strategy'] ?? StrategyActionEnum::MODIFY->value;

        switch ($action) {
            case ActivationActionEnum::ACTIVATE->value: // from not_active to active
                if ($user->is_active->value !== IsActiveEnum::ACTIVE->value) { // if not active -> will be activate , elseif active -> not changed(because it already active , and here activate)
                    $this->activateItem($user, $model, $strategy);
                }
                break;

            case ActivationActionEnum::DEACTIVATE->value: // from active to not active
                if ($user->is_active->value !== IsActiveEnum::NOT_ACTIVE->value) { // if active -> will be deactivate , elseif not_active -> not changed(because it already not_active , and here deactivate)
                    $this->deactivateItem($user);
                }
                break;

            case ActivationActionEnum::TOGGLE->value: // from active to not active && from not_active to active
                if ($user->is_active->value == IsActiveEnum::ACTIVE->value) { // from active to  not_active
                    $this->deactivateItem($user);
                } else {
                    $this->activateItem($user, $model, $strategy);
                }
                break;

            default:
                throw new \InvalidArgumentException("Unsupported activation action: $action");
        }
        
        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;

        return $data;
        
    }


    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile($request, $id, $model, $forUser), uploadFiles($request, $id, $model, $forUser), deleteFile($id, $model, $forUser), deleteFiles($request, $id, $model, $forUser)=====================

    /**
     * Upload single file or image for a model.
     *
     * @param  Request $request
     * @param  int     $id
     * @param  Model   $model
     * @return JsonResponse
     */
    public function uploadFile($request, $id, $model, $forUser = false)
    {
        // Get validated data from request
        $data = $request->validated();

        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        // Get folder name from model class name
        $folder = modelName($model);

        // Upload file or image if provided and supported by the model
        foreach (['file', 'image'] as $type) {
            if (isset($data[$type]) && method_exists($user, $type)) {
                $user->uploadSingleMedia($request->file($type), $type, $folder);
            }
        }

        $data = $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;

        return $data;

    }
    /**
     * Upload multiple images or files for a specific model.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFiles($request, $id, $model, $forUser = false)
    {
        // Get validated data from request
        $data = $request->validated();
        
        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        // Get folder name from model class name
        $folder = modelName($model);

        // Upload files or images if provided and supported by the model
        foreach (['files', 'images'] as $type) {

            if (isset($data[$type]) && method_exists($user, $type)) {

                if($type == 'files') $typeMedia = 'file';
                
                if($type == 'images') $typeMedia = 'image';
                $user->uploadMultipleMedia($request->file($type), $typeMedia, $folder);
            }           
        }

        // Load eager relationships if defined on the model
        $data = $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;

        return $data;
        
    }

    /**
     * Delete a single image or file from the model.
     *
     * @param  int  $id
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile($id, $model, $forUser = false)
    {

        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        // Delete image or file if supported by the model  
        foreach (['image', 'file'] as $type) {  
            if (method_exists($user, $type)) {  
                $user->deleteSingleMedia($type);  
                break;  
            }  
        }
        // Load eager relationships if defined
        $data = $model->getProp('eagerLoading') ? $user->load($model->getProp('eagerLoading')) : $user;

        return $data;
                
    }

    /**
     * Delete multiple images and/or files from a model.
     *
     * @param  int  $id
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFiles($request, $id, $model, $forUser = false)
    {

        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        $inputIds = $request->input('ids', []);
        $isAll = $inputIds === "all";

        $query = $model;
        
        // Fetch items based on whether 'all' is selected or specific IDs are provided
        $users = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds);
        if(empty($users)) throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

        $user->deleteMediaByIds($inputIds, 'files');

        // Load eager relationships if defined
        $eagerLoading = $model->getProp('eagerLoading');

        return $eagerLoading ? $user->load($eagerLoading) : $user;
        
    }
    #endregion ===================== End File Handling Methods =====================
   
}

