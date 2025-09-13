<?php

namespace App\Services\User\Order;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;
use App\Services\Translation\TranslationService;


/**
 * Class OrderService
 *
 * This service class handles all order-related business logic.
 * It implements the OrderServiceInterface and provides core methods such as store and update.
 * 
 * @package App\Services\User\Order
 * 
 */
class OrderService extends EloquentService implements OrderServiceInterface
{

    /** @var EloquentRepository */
    protected $eloquentRepo;

    /** @var TranslationService */
    protected $translationService;


    #region Constructor
    /**
     * Constructor
     *
     * @param EloquentRepository    $eloquentRepo
     * @param TranslationService    $translationService
     */
    #endregion Constructor
    
    public function __construct(EloquentRepository $eloquentRepo, TranslationService $translationService)
    {
        $this->eloquentRepo    = $eloquentRepo;
        $this->translationService = $translationService;

    }

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================
    /**
     * Store a new order record.
     *
     * @param object $request The validated request object.
     * @param object $model The order model instance.
     * @return object JSON service response with the newly created order.
     */
    public function store($request, $model)
    {
        // Get validated data from request
        $data = $request->validated();

        // Manually assign user ID (should be from auth, but hardcoded here)
        $data['user_id'] = userApi()?->id;

        // Optionally: calculate and assign 'total' based on order items
        $orderTotal = collect($data['contents'])->sum(function ($content) {
            return $content['price'] * $content['quantity'];
        });

        $data['total'] = $orderTotal;
        
        // Create a new order record in the database
        $newItem = $model->create($data);

        // Automatically refresh the model if 'is_active' field is missing
        refreshIfMissing($data, $newItem);

        // Load relationships if eager loading is defined in model
        $data = $model->getProp('eagerLoading')
            ? $newItem->load($model->getProp('eagerLoading'))
            : $newItem;

        return $data;
        
    }

   #endregion ===================== End CRUD Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate($request, $id, $model), changeActivateMany($request, $model)=====================

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start TRASH Methods: destroy($id, $model), destroyMany($request, $model), restore($request, $id, $model), restoreMany($request, $model) =====================

    #endregion ===================== End TRASH Methods =====================

    
    #region ===================== Start File Handling Methods uploadFile($request, $id, $model), uploadFiles($request, $id, $model), deleteFile($id, $model), deleteFiles($request, $id, $model)=====================

    #endregion ===================== End File Handling Methods =====================
    
    #region ===================== Start Private and Protected Methods =====================

    #endregion ===================== End Private and Protected Methods =====================

}
