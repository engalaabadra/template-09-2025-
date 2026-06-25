<?php

namespace App\Services\User\Review;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;
use App\Services\ServiceResponse;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

/**
 * Class ReviewService
 *
 * This service handles operations related to the Review module,
 * including storing, updating, and deleting reviews.
 * 
 * @package App\Services\User\Review
 * 
 */

class ReviewService extends EloquentService implements ReviewServiceInterface
{
    // Add specific business logic methods here

    /** @var EloquentRepository */
    protected $eloquentRepo;

    #region Constructor
    /**
     * Constructor
     *
     * @param EloquentRepository    $eloquentRepo
     */
    public function __construct(EloquentRepository $eloquentRepo)
    {
        $this->eloquentRepo    = $eloquentRepo;
    }
    #endregion Constructor

  

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================
    
    /**
     * Store a new review in the database.
     *
     * @param object $request The request object containing validated data.
     * @param object $model The review model instance.
     * @return object The created review with loaded relations.
     */
    public function store($request, $model)
    {
        // Get validated input data from request
        $data = $request->validated();

        // Assign static user_id (for testing; replace with userApi()->id)
        $data['user_id'] = userApi()?->id;

        // Create the review item
        $item = $model->create($data);

        // Load and return any eager loaded relations
        return $item->load($model->getProp('eagerLoading'));
    }

    /**
     * Update an existing review record.
     *
     * @param object $request The request object containing validated data.
     * @param int $id The ID of the review to update.
     * @param object $model The review model instance.
     * @return object JSON response with updated review or error.
     */
    public function update($request, $id, $model, $forUser = false )
    {
        // Get validated input data
        $data = $request->validated();

        // Find the review item by ID
        $item = $this->baseRepo->findOrFailApi($id, $model, $forUser);

        // Remove file/image fields from update payload
        $enteredData = array_diff_key($data, array_flip(['file', 'image']));

        // Assign static user_id (for testing; replace with userApi()->id)
        $enteredData['user_id'] = userApi()?->id;

        // Update the item
        $item->update($enteredData);

        // Load and return any eager loaded relations
        $data = $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;
        
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
