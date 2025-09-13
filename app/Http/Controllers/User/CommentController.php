<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Comment;
use App\Services\User\Comment\CommentService;
use App\Repositories\User\Comment\CommentRepository;
use App\Resources\CommentResource;
use Inertia\Inertia;
use App\Http\Requests\File\DeleteFilesRequest;

/**
 * Class CommentController
 *
 * This controller handles retrieving and listing comment records
 * for both API and web (Inertia) responses.
 */
class CommentController extends BaseController
{
    #region Constructor
    /**
     * @var CommentService Handles business logic
     */
    protected $commentService;
    /**
    * @var CommentRepository Handles data access layer
    */
   protected $commentRepository;


   /**
    * @var Comment
    * Comment model instance.
    */
   protected $comment;

   /**
    * CommentController constructor.
    *
    * @param Comment $comment
    * @param CommentService $commentService
    * @param CommentRepository $commentRepository
    */
   public function __construct(Comment $comment, CommentService $commentService, CommentRepository $commentRepository)
   {
       $this->comment = $comment;
       $this->commentService = $commentService;
       $this->commentRepository = $commentRepository;
   }

    #endregion Constructor

    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    public function storeCommentContent(StoreCommentRequest $request)
    {
        
        $result = $this->commentService->storeCommentContent($request, $this->comment, $contentId);
        return $this->respond($result, CommentResource::class);
    }

    public function updateComment(UpdateCommentRequest $request)
    {
        $result = $this->commentService->update($request, $id, $this->comment, $forUser = true);

        return $this->respond($result, CommentResource::class);
    }
    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a comment (mark as deleted without removing from DB).
     *
     * @param int $id Comment ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->commentService->destroy($id, $this->comment);
        return $this->respond($result, CommentResource::class);
    }

    /**
     * Soft delete multiple comments at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->commentService->destroyMany($request, $this->comment);
        return $this->respond($result);
    }
    
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a multiple file (image or other) related to a comment.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Comment ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->comment->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a comment
        $result = $this->commentService->uploadFiles($request, $id, $this->comment, $forUser = true);
        // Return comment data wrapped in CommentResource with updated files this comment
        return $this->respond($result, CommentResource::class);
    }

    /**
     * Delete a single file associated with a comment.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
     public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a comment
        $result = $this->commentService->deleteFiles($request, $id, $this->comment, $forUser = true);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End File Handling Methods =====================



    
}
