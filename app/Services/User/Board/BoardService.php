<?php
namespace App\Services\User\Board;

use App\Services\Eloquent\EloquentService;

/**
 * BoardService class handles all business logic related to boards in the dashboard.
 * 
 * This class extends the generic EloquentService and implements BoardServiceInterface
 * to ensure contract compliance and reuse of common service logic.
 * 
 * @package App\Services\User\Board
 * 
 */
class BoardService extends EloquentService implements BoardServiceInterface
{
     
    // Add specific business logic methods here

    #region Constructor

    #endregion Constructor

    #region ===================== Start CRUD Methods: store($request, $model), update($request, $id, $model), forceDelete($id, $model), forceDeleteMany($request, $model) =====================
    
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
