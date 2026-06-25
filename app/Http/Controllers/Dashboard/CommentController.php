<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\BaseController;
use App\Models\Comment;
use App\Services\Comment\CommentService;
use App\Repositories\Comment\CommentRepository;
use App\Resources\CommentResource;
use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;

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


    /**
     * Display a listing of comments.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function commentsContent(Content $content)
    {
        $result = $this->commentRepository->commentsContent($this->comment);
        return $this->respond($result, CommentResource::class);

    }
    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    public function storeCommentContent(Content $content)
    {
        $result = $this->commentService->store($this->comment, $content);
        return $this->respond($result, CommentResource::class);
    }

    public function updateCommentContent(Comment $comment)
    {
        $result = $this->commentService->update($this->comment, $forUser = true);
        return $this->respond($result, CommentResource::class);
    }
    /**
     * Permanently delete a content from the database.
     *
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $result = $this->contentService->forceDelete($id, $this->content, $forUser = true);
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple contents at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        $result = $this->contentService->forceDeleteMany($request, $this->content, $forUser = true);
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================


    /**
     * Restore a soft deleted content.
     *
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        $result = $this->contentService->restore($request, $id, $this->content, $forUser = true);
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Restore multiple soft deleted contents at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        $result = $this->contentService->restoreMany($request, $this->content, $forUser = true);
        return $this->respond($result);
    }

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific content.
     *
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->contentService->changeActivate($request, $id, $this->content, $forUser = true);
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Activate or deactivate multiple contents at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        $result = $this->contentService->changeActivateMany($request, $this->content, $forUser = true);
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a multiple file (image or other) related to a content.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->content->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a content
        $result = $this->contentService->uploadFiles($request, $id, $this->content, $forUser = true);
        // Return content data wrapped in ContentResource with updated files this content
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Delete a single file associated with a content.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
     public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a content
        $result = $this->contentService->deleteFiles($request, $id, $this->content, $forUser = true);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End File Handling Methods =====================

    public function relatedContents(Content $content)
    {
        $result = $this->contentRepository->relatedContents($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }

    public function nextContents(Content $content)
    {
        $result = $this->contentRepository->nextContents($this->content, $content);

        // Respond with content data wrapped in ContentResource
        return $this->respond($result, ContentResource::class);
    }

    public function editionsContents(Content $content)
    {
        $result = $this->contentRepository->editionsContents($this->content, $content);
    
        return $this->respond($result, ContentResource::class);
    }

    public function featuredContents(Content $content)
    {
        $result = $this->contentRepository->featuredContents($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }

    public function latestContents(Content $content)
    {
        $result = $this->contentRepository->latestContents($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }

    /**
     * Toggle saving status (save/unsave) for a specific content.
     *
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function addToMySaved(SaveContentRequest $request, $id)
    {
        $result = $this->contentService->addToMySaved($request, $id, $this->content, $forUser = true);
        return $this->respond($result, ContentResource::class);
    }

    public function myReads()
    {
        $result = $this->contentService->myReads($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }

    public function popularContents()
    {
        $result = $this->contentService->popularContents($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }
}

