<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\User\Author\AuthorService;
use App\Resources\AuthorResource;
use Inertia\Inertia;

/**
 * Class AuthorController
 *
 * This controller handles retrieving and listing author records
 * for both API and web (Inertia) responses.
 */
class AuthorController extends BaseController
{

    /**
     * @var AuthorService
     * Service that contains business logic for authors.
     */
    protected $authorService;

    /**
     * @var User
     * User model instance.
     */
    protected $user;

    /**
     * AuthorController constructor.
     *
     * @param User $user
     * @param AuthorService $authorService
     */
    public function __construct(User $user, AuthorService $authorService)
    {
        $this->user = $user;
        $this->authorService = $authorService;
    }

    /**
     * Display a listing of authors.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()//is_author=1
    {
        $result = $this->authorService->getData($this->user);  // Fetch author data (may be paginated or collection)

        // For API requests, respond with data wrapped in AuthorResource
        return $this->respond($result, AuthorResource::class);
    }

   public function show($id)//is_author=1
    {
        $result = $this->authorService->show($id, $this->author); // Retrieve author details


        // Respond with author data wrapped in AuthorResource
        return $this->respond($result, AuthorResource::class);
    }

    public function popularAuthors()//is_author=1
    {
        $result = $this->authorService->popularAuthors($this->author);

        return $this->respond($result, AuthorResource::class);
    }
}
