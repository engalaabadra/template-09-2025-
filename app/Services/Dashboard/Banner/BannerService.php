<?php
namespace App\Services\Dashboard\Banner;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;

/**
 * BannerService class handles all business logic related to banners in the dashboard.
 * 
 * This class extends the generic EloquentService and implements BannerServiceInterface
 * to ensure contract compliance and reuse of common service logic.
 * 
 * @package App\Services\Dashboard\Banner
 * 
 */
class BannerService extends EloquentService implements BannerServiceInterface
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

