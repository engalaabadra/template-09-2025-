<?php

namespace App\Services\Dashboard\Chat;

use App\Services\Eloquent\EloquentService;
use App\Events\MessageCreated;
use App\GeneralClasses\MediaClass;
use App\Models\User;
use App\Repositories\Eloquent\EloquentRepository;
use App\Repositories\Base\BaseRepository;
use App\Services\ServiceResponse;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Enums\RestoringDeletingActionEnum;

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

    /** @var EloquentRepository */
    protected $eloquentRepo;
    
    /**
     * Constructor
     *
     * @param BaseRepository    $baseRepo
     * @param EloquentRepository    $eloquentRepo
     * 
     */
    public function __construct(BaseRepository $baseRepo, EloquentRepository $eloquentRepo)
    {
        $this->baseRepo = $baseRepo;
        $this->eloquentRepo    = $eloquentRepo;
    }

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================
    /**
     * Store a new chat record.
     *
     * @param object $request Validated request object.
     * @param object $model   Chat model instance.
     * @return object         Newly created chat record with eager loaded relations.
     */
    protected function store($request, $model)
    {
        // Extract validated data and remove 'files' key
        $data = $request->validated();
        
        $enteredData = $model::onlyFillable($data); // Filter given data and return only fillable fields of the model(excluded: 'files')

        // Assign static user_id and client_id (replace with dynamic logic if needed)
        $enteredData['user_id'] = adminApi()?->id;

        $client = $this->baseRepo->findOrFailApi($enteredData['client_id'], User::class);

        // Create chat record
        $chat = $model->create($enteredData);

        // Broadcast new message event to others
        broadcast(new MessageCreated($chat))->toOthers();

        // Handle multiple file uploads if present
        $folder = modelName($model);

        // Check if files are provided
        if (isset($data['files'])) {
            // Upload multiple files to the media collection
            $newItem->uploadMultipleMedia($request->file('files'), 'file', $folder);
        }

        // Return created chat with eager loaded relationships if any
        return $model->getProp('eagerLoading') ? $chat->load($model->getProp('eagerLoading')) : $chat;
    }

    #endregion ===================== End CRUD Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model), changeActivateMany($request, $model)=====================

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start TRASH Methods: destroy($id, $model), destroyMany($request, $model), restore($request, $id, $model), restoreMany($request, $model) =====================

    /**
     * Bulk delete items.
     *
     * @param object $model The model to query.
     * @return mixed
     */
    public function destroyMany($request, $model , $forUser = false)
    {
        // Handle bulk deletion logic
        return $this->handleBulkAction($request, $model, RestoringDeletingActionEnum::DESTROY->value, $forUser);
    }

    /**
     * Restore multiple trashed records.
     *
     * @param object $model The model to query.
     * @return array Restored records.
     */
    // public function restoreMany($request, $model )
    // {
    //     return $this->handleBulkAction($request, $model, RestoringDeletingActionEnum::RESTORE->value);
    // }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start Protected & Private Methods =====================

    #endregion ===================== End Protected & Private Methods =====================

}
