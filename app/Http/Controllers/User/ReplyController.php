<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Reply;
use App\Services\User\Reply\ReplyService;
use App\Repositories\User\Reply\ReplyRepository;
use App\Resources\ReplyResource;
use Inertia\Inertia;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\BulkDeleteActionRequest;

/**
 * Class ReplyController
 *
 * This controller handles retrieving and listing reply records
 * for both API and web (Inertia) responses.
 */
class ReplyController extends BaseController
{

    #region Constructor

    /**
     * @var ReplyService Handles business logic
     */
    protected $replyService;

     /**
     * @var ReplyRepository Handles data access layer
     */
    protected $replyRepository;

    /**
     * @var Reply
     * Reply model instance.
     */
    protected $reply;

    /**
     * ReplyController constructor.
     *
     * @param Reply $reply
     * @param ReplyService $replyService
     * @param ReplyRepository $replyRepository
     */
    public function __construct(Reply $reply, ReplyService $replyService, ReplyRepository $replyRepository)
    {
        $this->reply = $reply;
        $this->replyService = $replyService;
        $this->replyRepository = $replyRepository;
    }
    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of replies.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function repliesComment(Comment $comment)
    {
         $result = $this->replyRepository->repliesComment($this->reply);  // Fetch reply for a category data (may be paginated or collection)

         // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;
        
        // For API requests, respond with data wrapped in ReplyResource
        return $this->respond($result, ReplyResource::class);

    }
    
    #endregion ===================== End CRUD Methods(Get) =====================

    
    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    public function storeReplyComment(Comment $comment)
    {
        $result = $this->commentService->store($this->reply, $comment);
        return $this->respond($result, CommentResource::class);
    }

    public function updateReplyComment(Reply $reply)
    {
        $result = $this->commentService->update($this->reply, $forUser = true);
        return $this->respond($result, CommentResource::class);
    }
        
    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a reply (mark as deleted without removing from DB).
     *
     * @param int $id Reply ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->replyService->destroy($id, $this->reply, $forUser = true);
        return $this->respond($result, ReplyResource::class);
    }

    /**
     * Soft delete multiple replys at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->replyService->destroyMany($request, $this->reply, $forUser = true);
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a multiple file (image or other) related to a reply.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Reply ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->reply->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a reply
        $result = $this->replyService->uploadFiles($request, $id, $this->reply, $forUser = true);
        // Return reply data wrapped in ReplyResource with updated files this reply
        return $this->respond($result, ReplyResource::class);
    }

    /**
     * Delete a single file associated with a reply.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
     public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a reply
        $result = $this->replyService->deleteFiles($request, $id, $this->reply, $forUser = true);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End File Handling Methods =====================

    
}
