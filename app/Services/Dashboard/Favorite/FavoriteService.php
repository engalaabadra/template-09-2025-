<?php
namespace App\Services\Dashboard\Favorite;

use App\Services\Eloquent\EloquentService;
use App\Scopes\LanguageScope;
use App\Repositories\Eloquent\EloquentRepository;

/**
 * FavoriteService
 *
 * This is a base Service class implementing the FavoriteServiceInterface.
 * It provides methodssuch as : store, update, update, destroy, foreDelete, changeActivate
 */
class FavoriteService extends EloquentService implements FavoriteServiceInterface
{
    
     // Add specific business logic methods here

    #region Constructor

    #endregion Constructor

    #region ===================== Start CRUD Methods : getData, show, report (extends from EloquentRepo), store, update, forceDelete, forceDeleteMany (extends from EloquentService) =====================
    
    #endregion ===================== End CRUD Methods =====================

     #region ===================== Start TRASH Methods: destroy, destroyMany, restore, restoreMany (extends from EloquentService) =====================


    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods : changeActivate, changeActivateMany changeActivate (extends from EloquentService) =====================

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods : uploadFile, uploadFiles, deleteFile, deleteFiles (extends from EloquentService) =====================

    #endregion ===================== End File Handling Methods =====================
   
    #region ===================== Start Protected & Private Methods =====================
    
    #endregion ===================== End Protected & Private Methods =====================
}
