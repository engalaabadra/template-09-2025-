<?php

namespace App\Services\Dashboard\Review;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;
use App\Repositories\Base\BaseRepository;

/**
 * ReviewService
 *
 * This service class handles the business logic for managing reviews in the dashboard.
 * It supports operations such as storing, updating, and deleting review records.
 */

class ReviewService extends EloquentService implements ReviewServiceInterface
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
