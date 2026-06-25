<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Repositories\User\Chat\ChatRepository;
use App\Services\User\Chat\ChatService;
use App\Models\Chat;
use App\Resources\ChatResource;
use App\Http\Requests\File\UploadFilesRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Requests\User\ChatRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class ChatController
 *
 * Handles chat management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management and file uploads.
 */
class ChatController extends BaseController
{

    #region Constructor
    /**
     * @var ChatService Handles business logic
     */
    protected $chatService;

    /**
     * @var ChatRepository Handles data access layer
     */
    protected $chatRepository;

    /**
     * @var Chat
     * The Chat model instance.
     */
    protected $chat;

    /**
     * ChatController constructor.
     * Dependency Injection for Chat model, ChatService.
     *
     * @param Chat $chat
     * @param ChatService $chatService
     * @param ChatRepository $chatRepository
     */
    public function __construct(Chat $chat, ChatService $chatService, ChatRepository $chatRepository)
    {
        $this->chat = $chat;
        $this->chatService = $chatService;
        $this->chatRepository = $chatRepository;
    }

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of chats.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->chatRepository->getData($this->chat, $forUser = true);  // Fetch chat data (may be paginated or collection)

       // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in ChatResource
        return $this->respond($result, ChatResource::class);
    }

    /**
     * Show details of a specific chat.
     *
     * @param int $id Chat ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->chatRepository->show($id, $this->chat); // Retrieve chat details

         if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('chats', 'show', $id); 
        }


        // Respond with chat data wrapped in ChatResource
        return $this->respond($result, ChatResource::class);
    }
    
    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================


    /**
     * Store a new chat or update an existing one.
     *
     * @param ChatRequest $request Validated chat creation/update request
     * @param int|null $id Chat ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(ChatRequest $request, $id = null)
    {
        $result = $this->chatService->store($request, $this->chat, $id); // Create or update chat via service
        // Respond with status 201 Created and redirect route 'dashboard.chats.index'
        return $this->respond($result, ChatResource::class);
        //return $this->respond($result, ChatResource::class, $message = null, 'dashboard.chats.index');
    }

    /**
     * Update an existing chat.
     *
     * @param ChatRequest $request Validated chat update request
     * @param int $id Chat ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(ChatRequest $request, $id)
    {
        // Update the chat data by calling the ChatService
        $result = $this->chatService->update($request, $id, $this->chat, $forUser = true);
        // Return the response wrapped with ChatResource,
        // which formats the chat data consistently for API or web responses
        return $this->respond($result, ChatResource::class);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a chat (mark as deleted without removing from DB).
     *
     * @param int $id Chat ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->chatService->destroy($id, $this->chat, $forUser = true);
        return $this->respond($result, ChatResource::class);
    }

    /**
     * Soft delete multiple chats at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->chatService->destroyMany($request, $this->chat);
        return $this->respond($result);
    }


    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================
    
    public function uploadFile(UploadFileRequest $request, $id)
    {
        // Upload a single file (image or other) related to a user
        $result = $this->chatService->uploadFile($request, $id, $this->chat);
        // Return user data wrapped in ChatResource with updated file this user
        return $this->respond($result, ChatResource::class);
    }

    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->chat->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a user
        $result = $this->chatService->uploadFiles($request, $id, $this->chat);
        // Return user data wrapped in ChatResource with updated files this user
        return $this->respond($result, ChatResource::class);
    }

    public function deleteFile($id)
    {
        // Delete a single file associated with a user
        $result = $this->chatService->deleteFile($id, $this->chat);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a user
        $result = $this->chatService->deleteFiles($request, $id, $this->chat);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }
    #endregion ===================== End File Handling Methods =====================

    
}