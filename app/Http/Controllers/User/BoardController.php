<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Board;
use App\Services\User\Board\BoardService;
use App\Repositories\User\Board\BoardRepository;
use App\Resources\BoardResource;
use Inertia\Inertia;

/**
 * Class BoardController
 *
 * This controller handles retrieving and listing board records
 * for both API and web (Inertia) responses.
 */
class BoardController extends BaseController
{

    #region Constructor
    /**
     * @var BoardService Handles business logic
     */
    protected $boardService;

    /**
     * @var BoardRepository Handles data access layer
     */
    protected $boardRepository;

    /**
     * @var Board
     * Board model instance.
     */
    protected $board;

    /**
     * BoardController constructor.
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
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
     public function index()
    {
        $result = $this->boardRepository->getData($this->board);  // Fetch board data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in BoardResource
        return $this->respond($result, BoardResource::class);
    }
    
    #endregion ===================== End CRUD Methods(Get) =====================

}
