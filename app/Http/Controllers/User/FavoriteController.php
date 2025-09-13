<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Favorite;
use App\Services\User\Favorite\FavoriteService;
use App\Repositories\User\Favorite\FavoriteRepository;
use App\Http\Requests\User\FavoriteRequest;
use App\Resources\FavoriteResource;

use App\Traits\Controllers\WebApiResponseTrait;
use Inertia\Inertia;

/**
 * Class FavoriteController
 *
 * This controller handles all operations related to favorites for the user,
 * including listing and storing favorites. It supports both API and Web responses.
 */
class FavoriteController extends BaseController
{

    #region Constructor

    /**
     *
     * @var FavoriteService Handles business logic
     */
    protected $favoriteService;

    /**
     * *
     * @var FavoriteRepository Handles data access layer
     */
    protected $favoriteRepository;
    
    /**
     * Favorite model instance.
     *
     * @var Favorite
     */
    protected $favorite;

    /**
     * FavoriteController constructor.
     *
     * @param Favorite         $favorite
     * @param FavoriteService  $favoriteService
     * @param FavoriteRepository  $favoriteRepository
     */
    public function __construct(Favorite $favorite, FavoriteService $favoriteService, FavoriteRepository $favoriteRepository)
    {
        $this->favorite         = $favorite;
        $this->favoriteService  = $favoriteService;
        $this->favoriteRepository  = $favoriteRepository;
    }
    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of favorites.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()
    {
        $result = $this->favoriteRepository->getData($this->favorite, $forUser = true);  // Fetch favorite data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in FavoriteResource
        return $this->respond($result, FavoriteResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================
    
    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a newly created favorite or update an existing one if $id is provided.
     *
     * @param FavoriteRequest $request
     * @param int|null $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(FavoriteRequest $request, $id = null)
    {
        // Store or update the favorite using the service
        $result = $this->favoriteService->store($request, $this->favorite, $id);

        // Return the response as a resource with status 201 and redirect if web
        return $this->respond($result, FavoriteResource::class, 201, 'dashboard.favorites.index');
    }
        
    #endregion ===================== End CRUD Methods(Storing) =====================


}
