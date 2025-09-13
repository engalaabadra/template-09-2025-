<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Models\Dashboard;
use App\Services\Dashboard\Follow\FollowService;
use App\Repositories\Dashboard\Follow\FollowRepository;
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
     * @var User
     * User model instance.
     */
    protected $user;

    /**
     * FollowController constructor.
     *
     * @param User $user
     * @param FollowService $followService
     * @param FollowRepository $followRepository
     */
    public function __construct(User $user, FollowService $followService, FollowRepository $followRepository)
    {
        $this->user = $user;
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
        //where('user_id', apiAuther())
        $result = $this->followRepository->getData($this->follow);  // Fetch follow data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in FollowResource
        return $this->respond($result, FollowResource::class);
    }
    public function followersuser(user $user)
    {
        $result = $this->userRepository->followersuser($this->follow, $user);
        return $this->respond($result, FollowResource::class);
    }
    #endregion ===================== End CRUD Methods(Get) =====================
}