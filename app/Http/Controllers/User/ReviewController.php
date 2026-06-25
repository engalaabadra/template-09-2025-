<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Repositories\User\Review\ReviewRepository;
use App\Services\User\Review\ReviewService;
use App\Models\Review;
use App\Resources\ReviewResource;
use App\Http\Requests\User\ReviewRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;

/**
 * Class ReviewController
 *
 * Handles review management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management.
 */
class ReviewController extends BaseController
{

    #region Constructor

    /**
     * @var ReviewService Handles business logic
     */
    protected $reviewService;

    /**
     * @var ReviewRepository Handles data access layer
     */
    protected $reviewRepository;

    /**
     * @var Review
     * The Review model instance.
     */
    protected $review;

    /**
     * ReviewController constructor.
     * Dependency Injection for Review model, ReviewService
     *
     * @param Review $review
     * @param ReviewService $reviewService
     * @param ReviewRepository $reviewRepository
     */
    public function __construct(Review $review, ReviewService $reviewService, ReviewRepository $reviewRepository)
    {
        $this->review = $review;
        $this->reviewService = $reviewService;
        $this->reviewRepository = $reviewRepository;
    }
    #endregion Constructor

        
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of reviews.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function reviewsReview(Review $Review)
    {
        $result = $this->reviewRepository->reviewsReview($this->review);  // Fetch review data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    
    public function reviewsAuthor(User $author)
    {
        $result = $this->reviewRepository->reviewsAuthor($this->author);  // Fetch review data (may be paginated or collection)

        if (isWebRequest()) { // Check if request is from web (not API)

            $this->setBreadcrumb('reviews', 'index');// Setup breadcrumb navigation

            // Render the web page with Inertia and pass necessary data
            return $this->renderWebIndexPage('Review/Index', [
                 'rows' => $result,                 // review list data
                'form_data' => $this->getCreateUpdateData(), // Form data for create/update
            ]);
        }

        // For API requests, respond with data wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    public function storeReviewReview(Review $Review)//addReviewReview
    {
        $result = $this->reviewService->storeReviewReview($this->review);

        return $this->respond($result, ReviewResource::class);
    }

    public function storeReviewAuthor(Author $author)//addReviewAuthor
    {
        $result = $this->reviewService->storeReviewAuthor($this->author);

        return $this->respond($result, ReviewResource::class);
    }

    public function update(Review $review)//updateReview
    {
        $result = $this->reviewService->update($this->review, $forUser = true);

        return $this->respond($result, ReviewResource::class);
    }
        
    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a specific order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Soft delete the order
        $result = $this->orderService->destroy($id, $this->order, $forUser = true);
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Soft delete multiple orders.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        // Soft delete many orders
        $result = $this->orderService->destroyMany($request, $this->order, $forUser = true);
        return $this->respond();
    }

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a multiple file (image or other) related to a Review.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->review->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a Review
        $result = $this->reviewService->uploadFiles($request, $id, $this->review, $forUser = true);
        // Return Review data wrapped in ReviewResource with updated files this Review
        return $this->respond($result, ReviewResource::class);
    }

    /**
     * Delete a single file associated with a Review.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
     public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a Review
        $result = $this->reviewService->deleteFiles($request, $id, $this->review, $forUser = true);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }
    #endregion ===================== End File Handling Methods =====================

}
