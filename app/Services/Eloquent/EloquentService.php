<?php

namespace App\Services\Eloquent;

use App\Repositories\Base\BaseRepository;
use App\Services\Translation\TranslationService;
use App\Enums\IsActiveEnum;
use DB;
use App\Traits\Services\HandlesServiceTransactions;
use App\Traits\Services\HandlesBulkOperationsTrait;
use App\Enums\ActivationActionEnum;
use App\Enums\StrategyActionEnum;
use App\Enums\RestoringDeletingActionEnum;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
 

/**
 * Class EloquentService
 *
 * Base service class for handling Eloquent model logic and business rules.
 * Provides generic CRUD functionality with support for translation and scoping.
 */
class EloquentService 
{
    use HandlesServiceTransactions, HandlesBulkOperationsTrait;

    #region Constructor

    /** @var BaseRepository */
    protected $baseRepo;

    /** @var TranslationService */
    protected $translationService;

    /**
     * Constructor
     *
     * @param BaseRepository    $baseRepo
     * @param TranslationService    $translationService
     */

    public function __construct(BaseRepository $baseRepo, TranslationService $translationService) {
        $this->baseRepo = $baseRepo;
        $this->translationService = $translationService;
    }
    #endregion Constructor


    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================

    /**
     * Store a new record.
     *
     * @param object $request  The request object containing validated data.
     * @param object $model    The model to query.
     * @return object          Created record with optional eager loading.
     */
    protected function store($request, $model)
    {
        // Get validated data and filter out 'file' and 'image'
        $data = $request->validated();

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model

        // $enteredData = array_diff_key($data, array_flip(['file', 'image', 'files', 'images']));

        // Create the new model record
        $newItem = $model->create($enteredData);

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($enteredData, $newItem);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $newItem, $request->get('translations'));
        }
         //Handle Files
        if (isset($data['files']) || isset($data['images']) || isset($data['file']) || isset($data['image'])) {
            $newItem->handleFiles($request, $model, $newItem);//exist in HasMediaTrait
        }
        
        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $newItem->load($model->getProp('eagerLoading')) : $newItem;

        return $data;
    }


    /**
     * Update a specific record.
     *
     * @param object $request  The request object containing validated data.
     * @param int    $id       The ID of the record to update.
     * @param object $model    The model to query.
     * @return object          Updated record.
     */
    protected function update($request, $id, $model, $forUser = false)
    {
         $forUser = $forUser ?? false;
        // Get validated data and find the record
        $data = $request->validated();

        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'file', 'image', 'files', 'images')

        // Update the record
        $item->update($enteredData);

        // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $item, $request->get('translations'));
        }

        //Handle Files
        if ($request->filled('files') || $request->filled('images') || $request->filled('file') || $request->filled('image')) {
            $item->handleFiles($request, $model, $item);//exist in HasMediaTrait
        }
        
        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;

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
        // Get the trashed item by ID without global scopes
        $item = $this->baseRepo->findOnlyTrashedOrFail($id, $model, $forUser);

        // Permanently delete the item
        $item->forceDelete();
    }

    /**
     * Permanently delete multiple trashed items.
     *
     * @param object $model The model to query.
     * @return mixed
     */
    protected function forceDeleteMany($request, $model, $forUser = false)
    {
        // Handle bulk force deletion logic
        return $this->handleBulkAction($request, $model, RestoringDeletingActionEnum::FORCE_DELETE->value, $forUser);
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
        $item = $this->baseRepo->findWithoutTrashedOrFail($id, $model, $forUser);// Only soft delete if not trashed
        
        // Soft delete the item (if model uses SoftDeletes), or permanently delete it
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
            $this->destroyItem($item);
        } else {
            $item->delete();//permanently delete it, because this mode not contain trash
        }
        

        $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
            ? $item->load($model->getProp('eagerLoading'))
            : $item;

        return $data;

    }

    /**
     * Bulk delete items.
     *
     * @param object $model The model to query.
     * @return mixed
     */
    protected function destroyMany($request, $model, $forUser = false)
    {
        // Handle bulk deletion logic
        return $this->handleBulkAction($request, $model, RestoringDeletingActionEnum::DESTROY->value, $forUser);
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
        $item = $this->baseRepo->findOnlyTrashedOrFail($id, $model, $forUser);

        if ($item->trashed()) { // Only restore if actually trashed
            $this->restoreItem($item, $model, $strategy);
        }
        // Eager load relations if defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;

    }

    /**
     * Restore multiple trashed records.
     *
     * @param object $model The model to query.
     * @return array Restored records.
     */
    protected function restoreMany($request, $model, $forUser = false)
    {
        return $this->handleBulkAction($request, $model, RestoringDeletingActionEnum::RESTORE->value, $forUser);
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

        // Find the record without elements trash
        $item = $this->baseRepo->findWithoutTrashedOrFail($id, $model, $forUser); // find item only in table not in trash to activate it

        // Get the action to perform: 'activate', 'deactivate', or 'toggle' (default: toggle)
        $action = $data['action_activation'] ?? ActivationActionEnum::TOGGLE->value;

        // Get the action to perform: modify (default), replace, or prevent
        $strategy = $data['strategy'] ?? StrategyActionEnum::MODIFY->value;

        switch ($action) {
            case ActivationActionEnum::ACTIVATE->value: // from not_active to active
                if ($item->is_active->value !== IsActiveEnum::ACTIVE->value) { // if not active -> will be activate , elseif active -> not changed(because it already active , and here activate)
                    $this->activateItem($item, $model, $strategy);
                }
                break;

            case ActivationActionEnum::DEACTIVATE->value: // from active to not active
                if ($item->is_active->value !== IsActiveEnum::NOT_ACTIVE->value) { // if active -> will be deactivate , elseif not_active -> not changed(because it already not_active , and here deactivate)
                    $this->deactivateItem($item);
                }
                break;

            case ActivationActionEnum::TOGGLE->value: // from active to not active && from not_active to active
                if ($item->is_active->value == IsActiveEnum::ACTIVE->value) { // from active to  not_active
                    $this->deactivateItem($item);
                } else {
                    $this->activateItem($item, $model, $strategy);
                }
                break;

            default:
                throw new \InvalidArgumentException("Unsupported activation action: $action");
        }
        
        // Load related models if eager loading is defined
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

        return $data;
        
    }

    /**
     * Activate multiple records.
     *
     * @param object $model  The model to query.
     * @return array         Activated records.
     */
    public function changeActivateMany($request, $model, $forUser = false)
    {
        $data = $request->validated(); // $data['action_activation'] : activate, deactivate, toggle
       
        // Get the action to perform: 'activate', 'deactivate', or 'toggle' (default: toggle)
        $actionActivation = $data['action_activation'] ?? ActivationActionEnum::TOGGLE->value;

        return $this->handleBulkAction($request, $model, $actionActivation, $forUser);
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

        // Find the model item without global scopes
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        // Get folder name from model class name
        $folder = modelName($model);

        // Upload file or image if provided and supported by the model
        foreach (['file', 'image'] as $type) {
            if (isset($data[$type]) && method_exists($item, $type)) {
                $item->uploadSingleMedia($request->file($type), $type, $folder);
            }
        }

        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

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
        
        // Find the model item without global scopes
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        // Get folder name from model class name
        $folder = modelName($model);

        // Upload files or images if provided and supported by the model
        foreach (['files', 'images'] as $type) {

            if (isset($data[$type]) && method_exists($item, $type)) {

                if($type == 'files') $typeMedia = 'file';
                
                if($type == 'images') $typeMedia = 'image';
                $item->uploadMultipleMedia($request->file($type), $typeMedia, $folder);
            }           
        }

        // Load eager relationships if defined on the model
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;

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
        // Find the model item without global scopes
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        // Delete image or file if supported by the model  
        foreach (['image', 'file'] as $type) {  
            if (method_exists($item, $type)) {  
                $item->deleteSingleMedia($type);  
                break;  
            }  
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
    public function deleteFiles($request, $id, $model, $forUser = false)
    {
        // Find the model item without global scopes
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        $inputIds = $request->input('ids', []);
        $isAll = $inputIds === "all";

        $query = $model;
        
        // Fetch items based on whether 'all' is selected or specific IDs are provided
        $items = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds);
        if(empty($items)) throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

        $item->deleteMediaByIds($inputIds, 'files');

        // Load eager relationships if defined
        $eagerLoading = $model->getProp('eagerLoading');

        return $eagerLoading ? $item->load($eagerLoading) : $item;
        
    }
    #endregion ===================== End File Handling Methods =====================
   
}

