<?php
namespace App\Services\User\Shelf;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;


/**
 * ShelfService class handles all business logic related to banners in the dashboard.
 * 
 * This class extends the generic EloquentService and implements ShelfServiceInterface
 * to ensure contract compliance and reuse of common service logic.
 * 
 * @package App\Services\User\Shelf
 * 
 */
class ShelfService extends EloquentService implements ShelfServiceInterface
{
    
    // Add specific business logic methods here

    #region Constructor

    #endregion Constructor

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================

    /**
     * Store a new shelf record.
     *
     * @param object $request Validated request object.
     * @param object $model   Chat model instance.
     * @return object         Newly created shelf record with eager loaded relations.
     */
    public function store($request, $model )
    {
        // Extract validated data and remove 'files' key
        $data = $request->validated();

        // Assign static user_id and client_id (replace with dynamic logic if needed)
        $data['user_id'] = userApi()?->id;

        // Create shelf record
        $shelf = $model->create($data);

        // Refresh the model if 'is_active' was not part of the input
        refreshIfMissing($data, $shelf);

         // Handle translations
        if ($request->filled('translations')) {
            $this->translationService->handleTranslations($model, $shelf, $request->get('translations'));
        }

        // Return created shelf with eager loaded relationships if any
        return $model->getProp('eagerLoading') ? $shelf->load($model->getProp('eagerLoading')) : $shelf;
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

