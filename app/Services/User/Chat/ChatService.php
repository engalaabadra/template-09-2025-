<?php
namespace App\Services\User\Chat;

use App\Services\Eloquent\EloquentService;
use App\Events\MessageCreated;
use App\Models\User;
use App\Repositories\Eloquent\EloquentRepository;
use App\Services\ServiceResponse;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Services\Translation\TranslationService;
use App\Repositories\Base\BaseRepository;


/**
 * ChatService
 *
 * This service class implements ChatServiceInterface and handles
 * chat-related business logic including storing, updating, deleting,
 * force deleting, and bulk actions on chat records.
 */
class ChatService extends EloquentService implements ChatServiceInterface
{

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
     * Store a new chat record.
     *
     * @param object $request Validated request object.
     * @param object $model   Chat model instance.
     * @return object         Newly created chat record with eager loaded relations.
     */
    public function store($request, $model )
    {
        // Extract validated data and remove 'files' key
        $data = $request->validated();
        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model

        // Assign static user_id and client_id (replace with dynamic logic if needed)
        $enteredData['user_id'] = userApi()?->id;

        // Create chat record
        $chat = $model->create($enteredData);

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($enteredData, $newItem);

        // Broadcast new message event to others
        broadcast(new MessageCreated($chat))->toOthers();

        //Handle Files
        if ($request->filled('files')) {
            $chat->handleFiles($request, $model, $chat);//exist in HasMediaTrait
        }

        // Return created chat with eager loaded relationships if any
        return $model->getProp('eagerLoading') ? $chat->load($model->getProp('eagerLoading')) : $chat;
    }

        
    #endregion ===================== End CRUD Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model), changeActivateMany($request, $model)=====================

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start TRASH Methods: destroy($id, $model), destroyMany($request, $model), restore($request, $id, $model), restoreMany($request, $model) =====================

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
        $item = $this->baseRepo->findWithoutTrashedOrFail($id, $model, $forUser = true);// Only soft delete if not trashed
        
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

    #endregion ===================== End TRASH Methods =====================

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
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser = true);

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
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser = true);

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
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser = true);

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
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser = true);

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

