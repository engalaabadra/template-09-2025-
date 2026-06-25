<?php
namespace App\Services\Dashboard\Contact;

use App\Services\Eloquent\EloquentService;
use App\Repositories\Eloquent\EloquentRepository;

/**
 * ContactService class handles all business logic related to Contacts in the dashboard.
 * 
 * This class extends the generic EloquentService and implements ContactServiceInterface
 * to ensure contract compliance and reuse of common service logic.
 */
class ContactService extends EloquentService implements ContactServiceInterface
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