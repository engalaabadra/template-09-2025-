<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Repositories\User\Contact\ContactRepository;
use App\Services\User\Contact\ContactService;
use App\Models\Contact;
use App\Resources\ContactResource;
use App\Http\Requests\User\ContactRequest;

use Inertia\Inertia;

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
        $this->contactService = $contactService;
        $this->generalRepository = $generalRepository;
    }
    #endregion Constructor
    

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
     
    #endregion ===================== End CRUD Methods(Storing) =====================

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
