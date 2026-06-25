<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Order\OrderRepository;
use App\Services\Dashboard\Order\OrderService;
use App\Models\Order;
use App\Resources\OrderResource;
use App\Http\Requests\Dashboard\OrderRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class OrderController
 *
 * Handles order management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management.
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
     * The Order model instance.
     */
    protected $order;

    /**
     * OrderController constructor.
     * Dependency Injection for Order model, OrderService.
     *
     * @param Order $order
     * @param OrderService $orderService
     * @param OrderRepository $orderRepository
     */
    public function __construct(Order $order, OrderService $orderService, OrderRepository $orderRepository)
    {
        $this->order = $order;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
    }
    #endregion Constructor
    
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of orders.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->orderRepository->getData($this->order);  // Fetch order data (may be paginated or collection)

         // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;


        // For API requests, respond with data wrapped in OrderResource
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Show details of a specific order.
     *
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->orderRepository->show($id, $this->order); // Retrieve order details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('orders', 'show', $id); 
        }

        // Respond with order data wrapped in OrderResource
        return $this->respond($result, OrderResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Update an existing order.
     *
     * @param OrderRequest $request Validated request data
     * @param int $id Order ID to update
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(OrderRequest $request, $id)
    {
        // Update the order data by calling the OrderService
        $result = $this->orderService->update($request, $id, $this->order);
        // Return the response wrapped with OrderResource,
        // which formats the order data consistently for API or web responses
        return $this->respond($result, OrderResource::class);
    }

    
    /**
     * Permanently delete an order from the database.
     *
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        // Permanently delete an order from the database
        $result = $this->orderService->forceDelete($id, $this->order);
        // Return the result (often a success message or empty data)
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple orders at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        // Permanently delete multiple orders at once
        $result = $this->orderService->forceDeleteMany($request, $this->order);
        // Return the response
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete an order (mark as deleted without removing from DB).
     *
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Soft delete an order (mark as deleted without removing from DB)
        $result = $this->orderService->destroy($id, $this->order);
        // Return deleted order data wrapped in OrderResource
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Soft delete multiple orders at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        // Soft delete multiple orders at once
        $result = $this->orderService->destroyMany($request, $this->order);
        // Return response directly (likely success message)
        return $this->respond($result);
    }

    /**
     * Restore a soft deleted order.
     *
     * @param int $id Order ID to restore
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        // Restore a soft deleted order
        $result = $this->orderService->restore($request, $id, $this->order);
        // Return the restored order wrapped in OrderResource
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Restore multiple soft deleted orders at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        // Restore multiple soft deleted orders at once
        $result = $this->orderService->restoreMany($request, $this->order);
        // Return response (usually success message)
        return $this->respond($result);
    }

    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific order.
     *
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        // Toggle activation status (activate/deactivate) for a specific order
        $result = $this->orderService->changeActivate($request, $id, $this->order);
        // Return the result wrapped with OrderResource
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Activate or deactivate multiple orders at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        // Activate or deactivate multiple orders at once
        $result = $this->orderService->changeActivateMany($request, $this->order);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload a single file (image or other) related to a order.
     *
     * @param UploadImageRequest $request Validated image upload request
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFile(UploadImageRequest $request, $id)
    {
        $result = $this->orderService->uploadFile($request, $id, $this->order, $forUser = true);
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Upload multiple files related to a order.
     *
     * @param UploadFilesRequest $request Validated files upload request
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        $this->order->setProp('eagerLoading', ['files']);
        $result = $this->orderService->uploadFiles($request, $id, $this->order, $forUser = true);
        return $this->respond($result, OrderResource::class);
    }

    /**
     * Delete a single file associated with a order.
     *
     * @param int $id File ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFile($id)
    {
        $result = $this->orderService->deleteFile($id, $this->order, $forUser = true);
        return $this->respond($result);
    }

    /**
     * Delete multiple files associated with a order.
     *
     * @param int $id Order ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        $result = $this->orderService->deleteFiles($request, $id, $this->order, $forUser = true);
        return $this->respond($result);
    }
}
