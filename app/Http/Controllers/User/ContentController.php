<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Content;
use App\Services\User\Content\ContentService;
use App\Repositories\User\Content\ContentRepository;
use App\Resources\ContentResource;
use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

use App\Http\Requests\User\ContentRequest;
use App\Http\Requests\File\UploadFilesRequest;

/**
 * Class ContentController
 *
 * This controller handles retrieving and listing content records
 * for both API and web (Inertia) responses.
 */
class ContentController extends BaseController
{

    #region Constructor
    /**
     * @var ContentService Handles business logic
     */
    protected $contentService;

    /**
     * @var ContentRepository Handles data access layer
     */
    protected $contentRepository;

    /**
     * @var Content
     */
    protected $content;

    /**
     * ContentController constructor.
     *
     * @param Content $content
     * @param ContentService $contentService
     * @param ContentRepository $contentRepository
     */
    public function __construct(Content $content, ContentService $contentService, ContentRepository $contentRepository)
    {
        $this->content = $content;
        $this->contentService = $contentService;
        $this->contentRepository = $contentRepository;
    }

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of contents.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index() // get all data contents (contents this user + contents other users)
    {
        $result = $this->contentRepository->getData($this->content, $forUser = true);  // Fetch content data (may be paginated or collection)
        
        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;
        
        // For API requests, respond with data wrapped in ContentResource
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Display a listing of contents.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function myContents() // get all data contents (contents this user), $forUser = true flag to know get data all or that only has it this user 
    {
        $result = $this->contentRepository->getData($this->content, $forUser = true);  // Fetch content data (may be paginated or collection)
        
        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;
        
        // For API requests, respond with data wrapped in ContentResource
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Show details of a specific content.
     *
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $this->content->setProp('eagerloading', ['reviews.user', 'comments.user', 'comments.replies.user', 'comments.replies.likes.user', 'comments.likes.user']);
        $result = $this->contentRepository->show($id, $this->content, $forUser = false); // Retrieve content details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('contents', 'show', $id); 
        }

        // Respond with content data wrapped in ContentResource
        return $this->respond($result, ContentResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new content or update an existing one.
     *
     * @param ContentRequest $request Validated content creation/update request
     * @param int|null $id Content ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(ContentRequest $request, $id = null)
    {
        $result = $this->contentService->store($request, $this->content, $id); // Create or update content via service

        // Respond with status 201 Created and redirect route 'User.contents.index'
        return $this->respond($result, ContentResource::class);
        //return $this->respond($result, ContentResource::class, $message = null, 'User.contents.index');
    }

    /**
     * Update an existing content.
     *
     * @param ContentRequest $request Validated content update request
     * @param int $id Content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(ContentRequest $request, $id)
    {
        // $this->content->applyOwnershipScope = true;
        // Update the content data by calling the ContentService
        $result = $this->contentService->update($request, $id, $this->content, $forUser = true);
        // Return the response wrapped with ContentResource,
        // which formats the content data consistently for API or web responses
        return $this->respond($result, ContentResource::class);
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
     * Soft delete a content (mark as deleted without removing from DB).
     *
     * @param int $id content ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->contentService->destroy($id, $this->content);
        return $this->respond($result, ContentResource::class);
    }

    /**
     * Soft delete multiple contents at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->contentService->destroyMany($request, $this->content);
        return $this->respond($result);
    }
    

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


    #region ===================== Start Special Methods =====================

    public function relatedContents($contentId)
    {
        $result = $this->contentRepository->relatedContents($this->content, $contentId);

        return $this->respond($result, ContentResource::class);
    }

    public function nextContents($contentId)
    {
        $result = $this->contentRepository->nextContents($this->content, $contentId);

        // Respond with content data wrapped in ContentResource
        return $this->respond($result, ContentResource::class);
    }

    public function editionsContents($contentId)
    {
        $result = $this->contentRepository->editionsContents($this->content, $contentId);
    
        return $this->respond($result, ContentResource::class);
    }

    public function featuredContents($contentId)
    {
        $result = $this->contentRepository->featuredContents($this->content, $contentId);

        return $this->respond($result, ContentResource::class);
    }

    public function latestContents($contentId)
    {
        $result = $this->contentRepository->latestContents($this->content, $contentId);

        return $this->respond($result, ContentResource::class);
    }

     public function myReads()
    {
        $result = $this->contentRepository->myReads($this->content, $content);

        return $this->respond($result, ContentResource::class);
    }

    public function popularContents()
    {
        $result = $this->contentRepository->popularContents($this->content, $content);

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
    #endregion ===================== End Special Methods =====================

   
}
