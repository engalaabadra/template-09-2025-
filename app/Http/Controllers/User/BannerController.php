<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Banner;
use App\Services\User\Banner\BannerService;
use App\Repositories\User\Banner\BannerRepository;
use App\Resources\BannerResource;
use Inertia\Inertia;

/**
 * Class BannerController
 *
 * This controller handles retrieving and listing banner records
 * for both API and web (Inertia) responses.
 */
class BannerController extends BaseController
{

    #region Constructor
    /**
     * @var BannerService Handles business logic
     */
    protected $bannerService;

    /**
     * @var BannerRepository Handles data access layer
     */
    protected $bannerRepository;

    /**
     * @var Banner
     * Banner model instance.
     */
    protected $banner;

    /**
     * BannerController constructor.
     *
     * @param Banner $banner
     * @param BannerService $bannerService
     * @param BannerRepository $bannerRepository
     */
    public function __construct(Banner $banner, BannerService $bannerService, BannerRepository $bannerRepository)
    {
        $this->banner = $banner;
        $this->bannerService = $bannerService;
        $this->bannerRepository = $bannerRepository;
    }

    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================


    /**
     * Display a listing of banners.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()
    {
        $result = $this->bannerRepository->getData($this->banner);  // Fetch banner data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in BannerResource
        return $this->respond($result, BannerResource::class);
    }
    
    #endregion ===================== End CRUD Methods(Get) =====================

}
