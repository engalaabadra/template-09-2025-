<?php

namespace App\Http\Controllers\Reply;

use App\Http\Controllers\BaseController;
use App\Models\Reply;
use App\Services\Reply\Reply\ReplyService;
use App\Repositories\Reply\Reply\ReplyRepository;
use App\Resources\ReplyResource;
use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;

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
        
    /**
     * Update an existing banner.
     *
     * @param BannerRequest $request Validated banner update request
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(BannerRequest $request, $id)
    {
        // Update the banner data by calling the BannerService
        $result = $this->bannerService->update($request, $id, $this->banner);
        // Return the response wrapped with BannerResource,
        // which formats the banner data consistently for API or web responses
        return $this->respond($result, BannerResource::class);
    }

    
    /**
     * Permanently delete a banner from the database.
     *
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $result = $this->bannerService->forceDelete($id, $this->banner);
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple banners at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkRestoreActionRequest $request)
    {
        $result = $this->bannerService->forceDeleteMany($request, $this->banner);
        return $this->respond($result);
    }


    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a banner (mark as deleted without removing from DB).
     *
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->bannerService->destroy($id, $this->banner);
        return $this->respond($result, BannerResource::class);
    }

    /**
     * Soft delete multiple banners at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->bannerService->destroyMany($request, $this->banner);
        return $this->respond($result);
    }
    
    /**
     * Restore a soft deleted banner.
     *
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        $result = $this->bannerService->restore($request, $id, $this->banner);
        return $this->respond($result, BannerResource::class);
    }

    /**
     * Restore multiple soft deleted banners at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        $result = $this->bannerService->restoreMany($request, $this->banner);
        return $this->respond($result);
    }

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific banner.
     *
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->bannerService->changeActivate($request, $id, $this->banner);
        return $this->respond($result, BannerResource::class);
    }

    /**
     * Activate or deactivate multiple banners at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        $result = $this->bannerService->changeActivateMany($request, $this->banner);
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a single file (image or other) related to a banner.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Banner ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFile(UploadImageRequest $request, $id)
    {
        $result = $this->bannerService->uploadFile($request, $id, $this->banner);
        return $this->respond($result, BannerResource::class);
    }

    /**
     * Delete a single file associated with a banner.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFile($id)
    {
        $result = $this->bannerService->deleteFile($id, $this->banner);
        return $this->respond($result);
    }

    #endregion ===================== End File Handling Methods =====================

    
}
