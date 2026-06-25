<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use App\Models\Content;
use App\Services\User\Category\CategoryService;
use App\Repositories\User\Category\CategoryRepository;
use App\Resources\CategoryResource;
use Inertia\Inertia;

/**
 * Class CategoryController
 *
 * This controller handles retrieving and listing category records
 * for both API and web (Inertia) responses.
 */
class CategoryController extends BaseController
{

    #region Constructor
    
    /**
     * @var CategoryService Handles business logic
     */
    protected $categoryService;

    /**
     * @var CategoryRepository Handles data access layer
     */
    protected $categoryRepository;

    /**
     * @var Category
     * Category model instance.
     */
    protected $category;

     /**
     * @var Content
     * Content model instance.
     */
    protected $content;

    /**
     * CategoryController constructor.
     *
     * @param Category $category
     * @param CategoryService $categoryService
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(Category $category, Content $content, CategoryService $categoryService, CategoryRepository $categoryRepository)
    {
        $this->category = $category;
        $this->content = $content;
        $this->categoryService = $categoryService;
        $this->categoryRepository = $categoryRepository;
    }

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of categories.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()
    {
        $result = $this->categoryRepository->getData($this->category);  // Fetch category data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;
        
        // For API requests, respond with data wrapped in CategoryResource
        return $this->respond($result, CategoryResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start Special Methods =====================

    /**
     * Retrieve all contents that belong to the given category.
     *
     * @param int $id Category ID
     * @return \Illuminate\Support\Collection   A collection of Content models related to the content.
     */
    public function contentsCategory($categoryId)
    {
         $result = $this->categoryRepository->contentsCategory($this->content, $forUser = false);

        // For API requests, respond with data wrapped in CategoryResource
        return $this->respond($result, CategoryResource::class);

    }

    #endregion ===================== End Special Methods =====================


}
