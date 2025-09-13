<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Shelf;
use App\Services\User\Shelf\ShelfService;
use App\Repositories\User\Shelf\ShelfRepository;
use App\Resources\ShelfResource;
use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\User\ShelfRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class ShelfController
 *
 * This controller handles retrieving and listing shelf records
 * for both API and web (Inertia) responses.
 */
class ShelfController extends BaseController
{

    #region Constructor

    /**
     * @var ShelfService Handles business logic
     */
    protected $shelfService;

     /**
     * @var ShelfRepository Handles data access layer
     */
    protected $shelfRepository;

    /**
     * @var Shelf
     * Shelf model instance.
     */
    protected $shelf;

    /**
     * ShelfController constructor.
     *
     * @param Shelf $shelf
     * @param ShelfService $shelfService
     * @param ShelfRepository $shelfRepository
     */
    public function __construct(Shelf $shelf, ShelfService $shelfService, ShelfRepository $shelfRepository)
    {
        $this->shelf = $shelf;
        $this->shelfService = $shelfService;
        $this->shelfRepository = $shelfRepository;
    }
    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of shelves.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->shelfRepository->getData($this->shelf, $forUser = true);  // Fetch shelf data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in ShelfResource
        return $this->respond($result, ShelfResource::class);
    }

    /**
     * Show details of a specific shelf.
     *
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id )
    {
        $result = $this->shelfRepository->show($id, $this->shelf ); // Retrieve shelf details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('shelves', 'show', $id); 
        }

        // Respond with shelf data wrapped in ShelfResource
        return $this->respond($result, ShelfResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new shelf or update an existing one.
     *
     * @param ShelfRequest $request Validated shelf creation/update request
     * @param int|null $id Shelf ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(ShelfRequest $request)
    {//will be is_author=1 (if 0)this user -> become author
        $result = $this->shelfService->store($request, $this->shelf); // Create or update shelf via service
       
        // Respond with status 201 Created and redirect route 'Author.shelves.index'
        return $this->respond($result, ShelfResource::class);
        //return $this->respond($result, ShelfResource::class, $message = null, 'Author.shelves.index');
    }

    /**
     * Update an existing shelf.
     *
     * @param ShelfRequest $request Validated shelf update request
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(ShelfRequest $request, $id )
    {
        // Update the shelf data by calling the ShelfService
        $result = $this->shelfService->update($request, $id, $this->shelf, $forUser = true );
        // Return the response wrapped with ShelfResource,
        // which formats the shelf data consistently for API or web responses
        return $this->respond($result, ShelfResource::class);
    }

        
    /**
     * Permanently delete a shelf from the database.
     *
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id )
    {
        $result = $this->shelfService->forceDelete($id, $this->shelf, $forUser = true );
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple shelves at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request )
    {
        $result = $this->shelfService->forceDeleteMany($request, $this->shelf, $forUser = true );
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a shelf (mark as deleted without removing from DB).
     *
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id )
    {
        $result = $this->shelfService->destroy($id, $this->shelf, $forUser = true );
        return $this->respond($result, ShelfResource::class);
    }

    /**
     * Soft delete multiple shelves at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request )
    {
        $result = $this->shelfService->destroyMany($request, $this->shelf, $forUser = true );
        return $this->respond($result);
    }

    /**
     * Restore a soft deleted shelf.
     *
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id )
    {
        $result = $this->shelfService->restore($request, $id, $this->shelf, $forUser = true );
        return $this->respond($result, ShelfResource::class);
    }

    /**
     * Restore multiple soft deleted shelves at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request )
    {
        $result = $this->shelfService->restoreMany($request, $this->shelf, $forUser = true );
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific shelf.
     *
     * @param int $id Shelf ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->shelfService->changeActivate($request, $id, $this->shelf, $forUser = true );
        return $this->respond($result, ShelfResource::class);
    }

    /**
     * Activate or deactivate multiple shelves at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request )
    {
        $result = $this->shelfService->changeActivateMany($request, $this->shelf, $forUser = true );
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

}
