<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\BaseController;
use App\Models\Like;
use App\Services\Like\LikeService;
use App\Repositories\Like\LikeRepository;
use App\Resources\LikeResource;
use Inertia\Inertia;

/**
 * Class LikeController
 *
 * This controller handles retrieving and listing like records
 * for both API and web (Inertia) responses.
 */
class LikeController extends BaseController
{

    #region Constructor
    /**
     * @var LikeService Handles business logic
     */
    protected $likeService;

    /**
     * @var LikeRepository Handles data access layer
     */
    protected $likeRepository;

    /**
     * @var Like
     * Like model instance.
     */
    protected $like;

    /**
     * LikeController constructor.
     *
     * @param Like $like
     * @param LikeService $likeService
     * @param LikeRepository $likeRepository
     */
    public function __construct(Like $like, LikeService $likeService, LikeRepository $likeRepository)
    {
        $this->like = $like;
        $this->likeService = $likeService;
        $this->likeRepository = $likeRepository;
    }

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of likes.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function likesContent(Content $content)
    {
         $result = $this->likeRepository->likesContent($this->like);

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;
        
        // For API requests, respond with data wrapped in LikeResource
        return $this->respond($result, LikeResource::class);

    }

    public function likesComment(Comment $comment)
    {
         $result = $this->likeRepository->likesComment($this->like);

         if (isWebRequest()) { // Check if request is from web (not API)

            $this->setBreadcrumb('likes', 'index');// Setup breadcrumb navigation

            // Render the web page with Inertia and pass necessary data
            return $this->renderWebIndexPage('Like/Index', [
                 'rows' => $result,                 // like list data
                'form_data' => $this->getCreateUpdateData(), // Form data for create/update
            ]);
        }
        // For API requests, respond with data wrapped in LikeResource
        return $this->respond($result, LikeResource::class);

    }

    public function likesReply(Reply $reply)
    {
         $result = $this->likeRepository->likesReply($this->like);  // Fetch like for a category data (may be paginated or collection)

        if (isWebRequest()) { // Check if request is from web (not API)

            $this->setBreadcrumb('likes', 'index');// Setup breadcrumb navigation

            // Render the web page with Inertia and pass necessary data
            return $this->renderWebIndexPage('Like/Index', [
                 'rows' => $result,                 // like list data
                'form_data' => $this->getCreateUpdateData(), // Form data for create/update
            ]);
        }

        // For API requests, respond with data wrapped in LikeResource
        return $this->respond($result, LikeResource::class);

    }

    #endregion ===================== End CRUD Methods(Get) =====================
}