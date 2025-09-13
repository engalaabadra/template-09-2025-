<?php

namespace App\Services\Dashboard\Auth\Role;
use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;
use App\Services\Translation\TranslationService;
use Illuminate\Support\Facades\Config;
use App\Models\Role;
use App\Services\ServiceResponse;
use App\Enums\IsActiveEnum;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Repositories\Base\BaseRepository;
use App\Enums\ActivationActionEnum;
use App\Enums\StrategyActionEnum;
use App\Enums\ActionMethodNameEnum;
use App\Enums\RestoringDeletingActionEnum;

/**
 * Class RoleService
 *
 * This service handles all business logic related to Role management.
 * Including storing, updating, activating, deleting, and restoring roles.
 */
class RoleService extends EloquentService implements RoleServiceInterface
{
    /** @var EloquentRepository */
    protected $eloquentRepo;

    /** @var BaseRepository */
    protected $baseRepo;

    /** @var TranslationService */
    protected $translationService;

    
    /**
     * Constructor
     *
     * @param EloquentRepository    $eloquentRepo
     * @param TranslationService    $translationService
     * @param BaseRepository    $baseRepo

    */
    public function __construct(EloquentRepository $eloquentRepo, BaseRepository $baseRepo, TranslationService $translationService)
    {
        $this->baseRepo = $baseRepo;
        $this->eloquentRepo       = $eloquentRepo;
        $this->translationService = $translationService;
        
    }

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================

    /**
     * Store a new record.
     *
     * @param object $request The request object containing validated data.
     * @param object $model   The model to be created.
     * @return object         Created record with optional eager loading.
     */
    protected function store($request, $model)
    {
        // Get validated data from the request
        $data = $request->validated();

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'roles', 'image', 'files')
        
        // Create the new record
        $newRole = $model->create($enteredData);

        // Assign permissions to the new role if provided
        if (isset($data['permissions'])) {
            $newRole->permissions()->attach($data['permissions']);
        }

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($enteredData, $newRole);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $newRole, $request->get('translations'));
        }
        // Return the newly created record with eager loading if defined
        return $model->getProp('eagerLoading') ? $newRole->load($model->getProp('eagerLoading')) : $newRole;

    }

    /**
     * Update a specific record.
     *
     * @param object     $request The request object containing validated data.
     * @param int        $id      The ID of the record to update.
     * @param object     $model   The model to update.
     * @return object             Updated record with optional eager loading.
     */
    protected function update($request, $id, $model, $forUser = false)
    {
        
        // Get validated data from the request
        $data = $request->validated();

        // Find the role by ID with ignore protected main roles
        $role = $model->findRoleExceptMain($id);

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'permissions')

        // Update the role with new data
        $role->update($enteredData);

        // Sync permissions if provided
        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $role, $request->get('translations'));
        }

        //Handle Files
        if ($request->filled('translations')) {
            $role->handleFiles($request, $model, $role);//exist in HasMediaTrait
        }
        // Return the updated role with eager loading if defined
        return $model->getProp('eagerLoading') ? $role->load($model->getProp('eagerLoading')) : $role;
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
        // Find a user by ID, ignoring protected main roles
        $role = $model->findRoleExceptMainTrash($id);

        // Permanently delete the item
        $role->forceDelete();
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
        
        // Find a user by ID, ignoring protected main roles , without trash
        $role = $model->findRoleExceptMainWithoutTrash($id);

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

        // Find a trashed user by ID, ignoring protected main roles
        $role = $model->findRoleExceptMainTrash($id);

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

        // Find a user by ID, ignoring protected main roles , without trash
        $role = $model->findRoleExceptMainWithoutTrash($id);

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

}

