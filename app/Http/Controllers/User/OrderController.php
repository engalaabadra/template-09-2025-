<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\Order\OrderRepository;
use App\Services\User\Order\OrderService;
use App\Models\Order;
use App\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\OrderRequest;

use Inertia\Inertia;

/**
 * Class OrderController
 *
 * Handles all operations related to orders for the user interface.
 * Supports both Web (Inertia) and API responses using shared methods.
 */
class OrderController extends BaseController
{

    #region Constructor
    /**
     * @var OrderService Handles business logic
     */
    protected $orderService;

    /**
     * @var OrderRepository Handles data access layer
     */
    protected $orderRepository;

    /**
     * @var Order
     */
    protected $order;

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * OrderController constructor.
     *
     * @param Order         $order
     * @param OrderService  $orderService
     * @param OrderRepository  $orderRepository
     */
    public function __construct(Order $order, OrderService $orderService, OrderRepository $orderRepository)
    {
        $this->order = $order;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of orders.
     *
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index()
    {
        $result = $this->orderRepository->getData($this->order);  // Fetch order data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in BannerResource
        return $this->respond($result, BannerResource::class);
    }

    /**
     * Display a specific order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Get specific order details
        $result = $this->orderRepository->show($id, $this->order);
        
        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('orders', 'show', $id); 
        }

        return $this->respond($result, OrderResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Create or update an order.
     *
     * @param OrderRequest $request
     * @param int|null $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(OrderRequest $request, $id = null)
    {
        // Store or update the order via service
        $result = $this->orderService->store($request, $this->order, $id);
        return $this->respond($result, OrderResource::class, 201, 'dashboard.orders.index');
    }

    /**
     * Update an existing order.
     *
     * @param OrderRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(OrderRequest $request, $id)
    {
        // Update order data
        $result = $this->orderService->update($request, $id, $this->order, $forUser = true);
        return $this->respond($result, OrderResource::class);
    }

    
    /**
     * Permanently delete a specific order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        // Force delete (permanent delete) an order
        $result = $this->orderService->forceDelete($id, $this->order, $forUser = true);
        return $this->respond();
    }

    /**
     * Permanently delete multiple orders.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        // Force delete multiple orders
        $result = $this->orderService->forceDeleteMany($request, $this->order, $forUser = true);
        return $this->respond();
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

}
