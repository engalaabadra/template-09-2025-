<?php
namespace App\Services\User\Profile;

use App\Models\User;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\GeneralClasses\MediaClass;
use App\Enums\ServiceResponseEnum;
use App\Services\ServiceResponse;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ApiResponseException;
use App\Repositories\Base\BaseRepository;

/**
 * Class ProfileService
 *
 * This is the concrete service class for handling user profile actions.
 * 
 * @package App\Services\User\Profile
 * 
 */
class ProfileService implements ProfileServiceInterface
{

    /**
     * @var User
     */
    protected $user;

    /** @var BaseRepository */
    protected $baseRepo;

    /**
     * ProfileController constructor.
     *
     * @param User
     * 
     */
    public function __construct(BaseRepository $baseRepo, User $user)
    {
        $this->baseRepo = $baseRepo;
        $this->user = $user;

    }

    #region ===================== Start CRUD Methods =====================

    /**
     * Update user profile information.
     *
     * @param UpdateProfileRequest $request
     * @param Profile $model
     * @return object
     */
    public function update($request, $model )
    {
        // Find the user by ID (currently hardcoded as 3)
        $user = $this->baseRepo->findOrFailApi(userApi()?->id, $this->user);

        // Get the validated data from the request
        $data = $request->validated();

        // Get the fillable attributes from the model
        $dataFillable = $model->getFillable();

        // Remove profile-related data and keep only user table fields
        $enteredData = Arr::except($data, $dataFillable);

        // Update user basic info
        $user->update($enteredData);

        // Get the existing profile of the user
        $profile = $model->where('user_id', $user->id)->first();

        // Convert birth_date format from d-m-Y to Y-m-d
        $data['birth_date'] = Carbon::createFromFormat('d-m-Y', $data['birth_date'])->format('Y-m-d');

        // If profile doesn't exist, create a new one and send welcome email
        if (!$profile) {
            $data['user_id'] = $user->id;

            // Create profile
            $model->create($data);

            // Send welcome message (currently commented out)
            // Prepare welcome email data
            $dataEmail = [
                'email' => $user->email,
                'user'  => $user->full_name,
                'type'  => 'welcome',
                'to'    => 'user'
            ];
            // app(SendingMessagesService::class)->sendingMessage($dataEmail);

        } else {
            // Update the existing profile with the new data
            $profile->update($data);
        }

        // If image is present, handle image upload
        $folder = modelName(User::class);

        // Check if image are provided
        if (isset($data['image'])) {
            $user->uploadSingleMedia($request->file('image'), 'image', $folder);
        }

        // Check if files are provided
        elseif (isset($data['files'])) {
            // Upload multiple files to the media collection
            $user->uploadMultipleMedia($request->file('files'), 'file', $folder);
        }
        // Return the user with eager-loaded relationships
        return $user->load($this->user->getProp('eagerLoading'));
    }

    /**
     * Update the user's password.
     *
     * @param UpdatePasswordRequest $request
     * @param User $model
     * @return object
     */
    public function updatePassword($request, $model )
    {
        // Get the authenticated user
        $user = $model->find(userApi()->id);

        // Get validated data from request
        $data = $request->validated();

        // Check old, new, and confirmation password validity
        $resultCheckPass = $this->checkDataPass($request, $user);
        if ($resultCheckPass) {
            return $resultCheckPass;
        }

        // Update the password (mutator in model should hash it)
        $user->password = $data['new_password'];
        $user->save();

        return $user;
    }

    /**
     * Check password validation rules before updating.
     *
     * @param UpdatePasswordRequest $request
     * @param User $user
     * @return ServiceResponse|null
     */
    private function checkDataPass($request, $user)
    {
        // Check if old password is incorrect
        if (!Hash::check($request->old_password, $user->password)) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.validation_failed'), [
                'old_password' => 'validation.current_wrong'
            ]);

        }

        // Check if new password is the same as the old one
        if ($request->old_password === $request->new_password) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.validation_failed'), [
                'new_password' => 'passwords.same_as_old'
            ]);
            
        }

        // Check if confirmation password does not match the new one
        if ($request->new_password !== $request->confirmation_new_password) {
            throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, trans('messages.validation_failed'), [
                'confirmation_new_password' => 'passwords.confirmation_mismatch'
            ]);
        }

        return null;
    }
    #endregion ===================== End CRUD Methods =====================


    #region ===================== Start File Handling Methods =====================

    /**
     * Upload single file or image for a model.
     *
     * @param  Request $request
     * @param  Model   $model
     * @return JsonResponse
     */
    public function uploadFile($request, $model )
    {
        $data = $request->validated();
        $id = userApi()?->id;
        $item = $model->withoutGlobalScopes()->find($id);

        if (!$item) {
            throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
        }

        $folder = modelName($model);

        if (isset($data['image'])) {
            $item->uploadSingleMedia($request->file('image'), 'image', $folder);
        }

        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;
    }
    /**
     * Upload multiple images or files for a specific model.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFiles($request, $model )
    {
        // Get validated data from request
        $data = $request->validated();

        // Find the model item
        $id = userApi()?->id;
        $item = $this->baseRepo->findOrFailApi($id, $model);

        // Get folder name from model class name
        $folder = modelName($model);

        // Check if files are provided
        if (isset($data['files'])) {
            // Upload multiple files to the media collection
            $item->uploadMultipleMedia($request->file('files'), 'file', $folder);
        }

        // Load eager relationships if defined on the model
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;
        
    }
    /**
     * Delete a single image or file from the model.
     *
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile($model)
    {
        // Find the model item
        $id = userApi()?->id;
        $item = $this->baseRepo->findOrFailApi($id, $model);

        // If image doesn't exist, try deleting file
        if (method_exists($item, 'image')) {
            $item->deleteSingleMedia('image');
        }

        // Load eager relationships if defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;

    }

    /**
     * Delete multiple images and/or files from a model.
     *
     * @param  int  $id
     * @param  mixed  $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFiles($request, $model )
    {
        // Find the model item
        $id = userApi()?->id;
        $item = $this->baseRepo->findOrFailApi($id, $model);

        $inputIds = $request->input('ids', []);
        $isAll = $inputIds === "all";

        $query = $model;
        // Fetch items based on whether 'all' is selected or specific IDs are provided
        $items = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds);

        $item->deleteMediaByIds($inputIds, 'files');

        // Load eager relationships if defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;
        
        return $data;
    
    }

    #endregion ===================== End File Handling Methods =====================

    #region ===================== Start Private and Protected Methods =====================

    /**
     * Retrieve items by IDs or all items if $isAll is true.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $isAll
     * @param  array|string                            $inputIds
     * @return \Illuminate\Support\Collection
     */
    protected function fetchItemsByIdsOrAll($query, bool $isAll, $inputIds)
    {
        if ($isAll) {
            // Fetch all items (no filtering by ID)
            $items = $query->get();

            $ids = $items->pluck('id')->toArray(); // Set $ids for comparison later

        } else {
            $ids = $inputIds; // Already validated array of integers
            // Fetch only items with those IDs
            $items = $query->whereIn('id', $ids)->get();
            
        }
        // Return 404 response if no items were found
        if ($items->isEmpty() && !$isAll) {
            throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);           // throw 404 if SoftDeletes not used
        }
        return $items;
    }
    #endregion ===================== End Private and Protected Methods =====================

}

