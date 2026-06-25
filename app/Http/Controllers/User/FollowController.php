<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\User\Follow\FollowService;
use App\Repositories\User\Follow\FollowRepository;
use App\Resources\FollowResource;
use Inertia\Inertia;

/**
 * Class FollowController
 *
 * This controller handles retrieving and listing author records
 * for both API and web (Inertia) responses.
 */
class FollowController extends BaseController
{

    #region Constructor

    /**
     * @var FollowService Handles business logic
     */
    protected $followService;

     /**
     * @var FollowRepository Handles data access layer
     */
    protected $followRepository;

    /**
     * @var Follow
     * Follow model instance.
     */
    protected $follow;

    /**
     * FollowController constructor.
     *
     * @param Follow $follow
     * @param FollowService $followService
     * @param FollowRepository $followRepository
     */
    public function __construct(Follow $follow, FollowService $followService, FollowRepository $followRepository)
    {
        $this->follow = $follow;
        $this->followService = $followService;
        $this->followRepository = $followRepository;
    }
    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of authors.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()//myfollowers
    {
        //where('follow_id', apiAuther())
        $result = $this->followRepository->getData($this->follow);  // Fetch follow data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in FollowResource
        return $this->respond($result, FollowResource::class);
    }
    public function followersfollow(Follow $follow)
    {
        $result = $this->followRepository->followersfollow($this->follow, $follow);
        return $this->respond($result, FollowResource::class);
    }
    #endregion ===================== End CRUD Methods(Get) =====================
  
    
    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    public function store($follow)//add follow this follow
    {
        $result = $this->followService->store($this->follow, $follow);
        return $this->respond($result, FollowResource::class);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    public function destory()
    {
        $result = $this->followService->destory($this->follow, $forUser = true);
        return $this->respond($result, FollowResource::class);
    }
    /**
     * Soft delete multiple follows at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkRestoreActionRequest $request)
    {
        $result = $this->followService->destroyMany($request, $this->follow, $forUser = true);
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================


    
}
