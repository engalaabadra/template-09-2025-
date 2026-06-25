<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Contact\ContactRepository;
use App\Services\Dashboard\Contact\ContactService;
use App\Models\Contact;
use App\Resources\ContactResource;
use App\Http\Requests\File\UploadFilesRequest;
use App\Http\Requests\Dashboard\ContactRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class ContactController
 *
 * Handles contact management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management and file uploads.
 */
class ContactController extends BaseController
{

    #region Constructor

    /**
     * @var ContactService Handles business logic
     */
    protected $contactService;

     /**
     * @var ContactRepository Handles data access layer
     */
    protected $contactRepository;
    /**
     * @var Contact
     * The Contact model instance.
     */
    protected $contact;


    /**
     * ContactController constructor.
     * Dependency Injection for Contact model, ContactService.
     *
     * @param Contact $contact
     * @param ContactService $contactService
     * @param ContactRepository $contactRepository
     */
    public function __construct(Contact $contact, ContactService $contactService, ContactRepository $contactRepository)
    {
        $this->contact = $contact;
        $this->contactService = $contactService;
        $this->contactRepository = $contactRepository;
    }

    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of contacts.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->contactRepository->getData($this->contact);  // Fetch contact data (may be paginated or collection)
        
        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in ContactResource
        return $this->respond($result, ContactResource::class);
    }

    /**
     * Show details of a specific contact.
     *
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->contactRepository->show($id, $this->contact); // Retrieve contact details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('contacts', 'show', $id); 
        }

        // Respond with contact data wrapped in ContactResource
        return $this->respond($result, ContactResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new contact or update an existing one.
     *
     * @param ContactRequest $request Validated request data for storing/updating contact
     * @param int|null $id Contact ID to update; null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(ContactRequest $request, $id = null)
    {
        $result = $this->contactService->store($request, $this->contact, $id); // Create or update contact via service
        // Respond with status 201 Created and redirect route 'dashboard.contacts.index'
        return $this->respond($result, ContactResource::class);
        //return $this->respond($result, ContactResource::class, $message = null, 'dashboard.contacts.index');
    }

    /**
     * Update an existing contact.
     *
     * @param ContactRequest $request Validated request data
     * @param int $id Contact ID to update
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(ContactRequest $request, $id)
    {
        // Update the contact data by calling the ContactService
        $result = $this->contactService->update($request, $id, $this->contact);
        // Return the response wrapped with ContactResource,
        // which formats the contact data consistently for API or web responses
        return $this->respond($result, ContactResource::class);
    }

    
    /**
     * Permanently delete a contact from the database.
     *
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        // Permanently delete a contact from the database
        $result = $this->contactService->forceDelete($id, $this->contact);
        // Return the result (often a success message or empty data)
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple contacts at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        // Permanently delete multiple contacts at once
        $result = $this->contactService->forceDeleteMany($request, $this->contact);
        // Return the response
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a contact (mark as deleted without removing from DB).
     *
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Soft delete a contact (mark as deleted without removing from DB)
        $result = $this->contactService->destroy($id, $this->contact);
        // Return deleted contact data wrapped in ContactResource
        return $this->respond($result, ContactResource::class);
    }

    /**
     * Soft delete multiple contacts at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        // Soft delete multiple contacts at once
        $result = $this->contactService->destroyMany($request, $this->contact);
        // Return response directly (likely success message)
        return $this->respond($result);
    }

    /**
     * Restore a soft deleted contact.
     *
     * @param int $id Contact ID to restore
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        // Restore a soft deleted contact
        $result = $this->contactService->restore($request, $id, $this->contact);
        // Return the restored contact wrapped in ContactResource
        return $this->respond($result, ContactResource::class);
    }

    /**
     * Restore multiple soft deleted contacts at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        // Restore multiple soft deleted contacts at once
        $result = $this->contactService->restoreMany($request, $this->contact);
        // Return response (usually success message)
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
    
    /**
     * Toggle activation status (activate/deactivate) for a specific contact.
     *
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        // Toggle activation status (activate/deactivate) for a specific contact
        $result = $this->contactService->changeActivate($request, $id, $this->contact);
        // Return the result wrapped with ContactResource
        return $this->respond($result, ContactResource::class);
    }

    /**
     * Activate or deactivate multiple contacts at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        // Activate or deactivate multiple contacts at once
        $result = $this->contactService->changeActivateMany($request, $this->contact);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================

    /**
     * Upload multiple files related to a contact.
     *
     * @param UploadFilesRequest $request Validated files upload request
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->contact->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a contact
        $result = $this->contactService->uploadFiles($request, $id, $this->contact);
        // Return contact data wrapped in ContactResource with updated files this contact
        return $this->respond($result, ContactResource::class);
    }

    /**
     * Delete multiple files associated with a contact.
     *
     * @param int $id Contact ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFiles($id)
    {
        // Delete multiple files associated with a contact
        $result = $this->contactService->deleteFiles($id, $this->contact);
        // Return response, usually a simple success message, no resource wrapping needed
        return $this->respond($result);
    }
    
    #endregion ===================== End File Handling Methods =====================


}
