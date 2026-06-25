<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Board\BoardRepository;
use App\Services\Dashboard\Board\BoardService;
use App\Models\Board;
use App\Resources\BoardResource;
use App\Http\Requests\Image\UploadImageRequest;
use App\Http\Requests\Dashboard\BoardRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class BoardController
 *
 * Handles board management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management and file uploads.
 */
class BoardController extends BaseController
{

    #region Constructor

    /**
     * @var BoardService Handles business logic
     */
    protected $boardService;

    /**
     * @var BoardRepository  Handles data access layer
     */
    protected $boardRepository;

    /**
     * @var Board
     * The Board model instance.
     */
    protected $board;

    /**
     * BoardController constructor.
     * Dependency Injection for Board model, BoardService.
     *
     * @param Board $board
     * @param BoardService $boardService
     * @param BoardRepository $boardRepository
     */
    public function __construct(Board $board, BoardService $boardService, BoardRepository $boardRepository)
    {
        $this->board = $board;
        $this->boardService = $boardService;
        $this->boardRepository = $boardRepository;
    }

    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of boards.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->boardRepository->getData($this->board);  // Fetch board data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in BoardResource
        return $this->respond($result, BoardResource::class);
    }

    /**
     * Show details of a specific board.
     *
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->boardRepository->show($id, $this->board); // Retrieve board details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('boards', 'show', $id); 
        }

        // Respond with board data wrapped in BoardResource
        return $this->respond($result, BoardResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new board or update an existing one.
     *
     * @param BoardRequest $request Validated board creation/update request
     * @param int|null $id Board ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(BoardRequest $request, $id = null)
    {
        $result = $this->boardService->store($request, $this->board, $id); // Create or update board via service
        // Respond with status 201 Created and redirect route 'dashboard.boards.index'
        return $this->respond($result, BoardResource::class);
        //return $this->respond($result, BoardResource::class, $message = null, 'dashboard.boards.index');
    }

    /**
     * Update an existing board.
     *
     * @param BoardRequest $request Validated board update request
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(BoardRequest $request, $id)
    {
        // Update the board data by calling the BoardService
        $result = $this->boardService->update($request, $id, $this->board);
        // Return the response wrapped with BoardResource,
        // which formats the board data consistently for API or web responses
        return $this->respond($result, BoardResource::class);
    }

    
    /**
     * Permanently delete a board from the database.
     *
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $result = $this->boardService->forceDelete($id, $this->board);
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple boards at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        $result = $this->boardService->forceDeleteMany($request, $this->board);
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a board (mark as deleted without removing from DB).
     *
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->boardService->destroy($id, $this->board);
        return $this->respond($result, BoardResource::class);
    }

    /**
     * Soft delete multiple boards at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->boardService->destroyMany($request, $this->board);
        return $this->respond($result);
    }

    /**
     * Restore a soft deleted board.
     *
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        $result = $this->boardService->restore($request, $id, $this->board);
        return $this->respond($result, BoardResource::class);
    }

    /**
     * Restore multiple soft deleted boards at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        $result = $this->boardService->restoreMany($request, $this->board);
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific board.
     *
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->boardService->changeActivate($request, $id, $this->board);
        return $this->respond($result, BoardResource::class);
    }

    /**
     * Activate or deactivate multiple boards at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        $result = $this->boardService->changeActivateMany($request, $this->board);
        return $this->respond($result);
    }
    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a single file (image or other) related to a board.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Board ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFile(UploadImageRequest $request, $id)
    {
        $result = $this->boardService->uploadFile($request, $id, $this->board);
        return $this->respond($result, BoardResource::class);
    }

    /**
     * Delete a single file associated with a board.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFile($id)
    {
        $result = $this->boardService->deleteFile($id, $this->board);
        return $this->respond($result);
    }

    #endregion ===================== End File Handling Methods =====================

}
