<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Review\ReviewRepository;
use App\Services\Dashboard\Review\ReviewService;
use App\Models\Review;
use App\Resources\ReviewResource;
use App\Http\Requests\Dashboard\ReviewRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

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
     * Dependency Injection for Review model, ReviewService.
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
    public function index()
    {
        $result = $this->reviewRepository->getData($this->review);  // Fetch review data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;


        // For API requests, respond with data wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    /**
     * Show details of a specific review.
     *
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->reviewRepository->show($id, $this->review); // Retrieve review details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('reviews', 'show', $id); 
        }

        // Respond with review data wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new review or update an existing one.
     *
     * @param ReviewRequest $request Validated request data for storing/updating review
     * @param int|null $id Review ID to update; null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(ReviewRequest $request, $id = null)
    {
        $result = $this->reviewService->store($request, $this->review, $id); // Create or update review via service
        // Respond with status 201 Created and redirect route 'dashboard.reviews.index'
        return $this->respond($result, ReviewResource::class);
        //return $this->respond($result, ReviewResource::class, $message = null, 'dashboard.reviews.index');
    }

    /**
     * Update an existing review.
     *
     * @param ReviewRequest $request Validated request data
     * @param int $id Review ID to update
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(ReviewRequest $request, $id)
    {
        // Update the review data by calling the ReviewService
        $result = $this->reviewService->update($request, $id, $this->review);
        // Return the response wrapped with ReviewResource,
        // which formats the review data consistently for API or web responses
        return $this->respond($result, ReviewResource::class);
    }
    
    /**
     * Permanently delete a review from the database.
     *
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        // Permanently delete a review from the database
        $result = $this->reviewService->forceDelete($id, $this->review);
        // Return the result (often a success message or empty data)
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple reviews at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        // Permanently delete multiple reviews at once
        $result = $this->reviewService->forceDeleteMany($request, $this->review);
        // Return the response
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================


    /**
     * Soft delete a review (mark as deleted without removing from DB).
     *
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Soft delete a review (mark as deleted without removing from DB)
        $result = $this->reviewService->destroy($id, $this->review);
        // Return deleted review data wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    /**
     * Soft delete multiple reviews at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        // Soft delete multiple reviews at once
        $result = $this->reviewService->destroyMany($request, $this->review);
        // Return response directly (likely success message)
        return $this->respond($result);
    }

    /**
     * Restore a soft deleted review.
     *
     * @param int $id Review ID to restore
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        // Restore a soft deleted review
        $result = $this->reviewService->restore($request, $id, $this->review);
        // Return the restored review wrapped in ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    /**
     * Restore multiple soft deleted reviews at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        // Restore multiple soft deleted reviews at once
        $result = $this->reviewService->restoreMany($request, $this->review);
        // Return response (usually success message)
        return $this->respond($result);
    }

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific review.
     *
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        // Toggle activation status (activate/deactivate) for a specific review
        $result = $this->reviewService->changeActivate($request, $id, $this->review);
        // Return the result wrapped with ReviewResource
        return $this->respond($result, ReviewResource::class);
    }

    /**
     * Activate or deactivate multiple reviews at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        // Activate or deactivate multiple reviews at once
        $result = $this->reviewService->changeActivateMany($request, $this->review);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }
    #endregion ===================== End ACTIVATION Methods =====================

}
