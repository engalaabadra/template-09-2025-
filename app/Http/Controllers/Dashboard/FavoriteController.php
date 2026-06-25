<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Models\Favorite;
use App\Services\Dashboard\Favorite\FavoriteService;
use App\Repositories\Dashboard\Favorite\FavoriteRepository;
use App\Http\Requests\Dashboard\FavoriteRequest;
use App\Resources\FavoriteResource;

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
        $result = $this->favoriteRepository->getData($this->favorite);  // Fetch favorite data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in FavoriteResource
        return $this->respond($result, FavoriteResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================
    

}
