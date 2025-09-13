<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Auth\User\UserRepository;
use App\Services\Dashboard\Auth\User\UserService;
use App\Models\User;
use App\Resources\UserResource;
use App\Http\Requests\File\UploadFilesRequest;
use App\Http\Requests\File\DeleteFilesRequest;
use App\Http\Requests\Image\UploadImageRequest;
use App\Http\Requests\Dashboard\Auth\UserRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class UserController
 *
 * Handles user management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management,
 * role/permission assignment, and file uploads.
 */
class UserController extends BaseController
{

    #region Constructor

    /**
     * @var UserService Handles business logic
     */
    protected $userService;

    /**
     * @var UserRepository Handles data access layer
     */
    protected $userRepository;


    /**
     * @var User
     * The User model instance.
     */
    protected $user;

    /**
     * UserController constructor.
     * Dependency Injection for User model, UserService.
     */
    public function __construct(User $user, UserService $userService, UserRepository $userRepository)
    {
        $this->user = $user;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    #endregion Constructor

    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $result = $this->userRepository->getData($this->user);  // Fetch user data (may be paginated or collection)

       // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in UserResource
        return $this->respond($result, UserResource::class);
    }

    /**
     * Show details of a specific user.
     */
    public function show($id)
    {
        $result = $this->userRepository->show($id, $this->user); // Retrieve user details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('users', 'show', $id); 
        }
        // Respond with user data wrapped in UserResource
        return $this->respond($result, UserResource::class);
    }


    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new user or update an existing one.
     */
    public function store(UserRequest $request, $id = null)
    {
        $result = $this->userService->store($request, $this->user, $id); // Create or update user via service
        
        // Respond with user data wrapped in UserResource , which formats the user data consistently for API or web responses
        return $this->respond($result, UserResource::class);
        // Respond with status 201 Created and redirect route 'dashboard.users.index'
        //return $this->respond($result, UserResource::class, $message = null, 'dashboard.users.index');
    }

    /**
     * Update an existing user.
     */
    public function update(UserRequest $request, $id)
    {
        // Update the user data by calling the UserService
        $result = $this->userService->update($request, $id, $this->user);
        // Return the response wrapped with UserResource, which formats the user data consistently for API or web responses
        return $this->respond($result, UserResource::class);
    }
     public function forceDelete($id)
    {
        // Permanently delete a user from the database
        $result = $this->userService->forceDelete($id, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message , with empty data)
        return $this->respond($result);
    }

    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        // Permanently delete multiple users at once
        $result = $this->userService->forceDeleteMany($request, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    public function destroy($id)
    {
        // Soft delete a user (mark as deleted without removing from DB)
        $result = $this->userService->destroy($id, $this->user);
        // Return the response wrapped with UserResource, which formats the user data consistently for API or web responses
        return $this->respond($result, UserResource::class);
    }

    public function destroyMany(BulkDeleteActionRequest $request)
    {
        // Soft delete multiple users at once
        $result = $this->userService->destroyMany($request, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

   


    public function restore(RestoreActionRequest $request, $id)
    {
        // Restore a soft deleted user
        $result = $this->userService->restore($request, $id, $this->user);
        // Return the response wrapped with UserResource, which formats the user data consistently for API or web responses
        return $this->respond($result, UserResource::class);
    }

    public function restoreMany(BulkRestoreActionRequest $request)
    {
        // Restore multiple soft deleted users at once
        $result = $this->userService->restoreMany($request, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
        public function changeActivate(ActivationActionRequest $request, $id)
    {
        // Toggle activation status (activate/deactivate) for a specific user
        $result = $this->userService->changeActivate($request, $id, $this->user);

        // Return the response wrapped with UserResource, which formats the user data consistently for API or web responses
        return $this->respond($result, UserResource::class);
    }
     public function changeActivateMany(BulkActivationActionRequest $request)
    {
        // Activate or deactivate multiple users at once
        $result = $this->userService->changeActivateMany($request, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    #endregion ===================== End ACTIVATION Methods =====================

    #region ===================== Start File Handling Methods uploadFile(Request $request, $id), uploadFiles(Request $request, $id), deleteFile($id), deleteFiles(Request $request, $id, $model, $forUser)=====================
  public function uploadFile(UploadImageRequest $request, $id)
    {
        // Upload a single file (image or other) related to a user
        $result = $this->userService->uploadFile($request, $id, $this->user);
        // Return user data wrapped in UserResource with updated file this user
        return $this->respond($result, UserResource::class);
    }

    public function uploadFiles(UploadFilesRequest $request, $id)
    {
        // Prepare eager loading of 'files' relation to optimize queries
        $this->user->setProp('eagerLoading', ['files']);
        // Upload multiple files related to a user
        $result = $this->userService->uploadFiles($request, $id, $this->user);
        // Return user data wrapped in UserResource with updated files this user
        return $this->respond($result, UserResource::class);
    }

    public function deleteFile($id)
    {
        // Delete a single file associated with a user
        $result = $this->userService->deleteFile($id, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }

    public function deleteFiles(DeleteFilesRequest $request, $id)
    {
        // Delete multiple files associated with a user
        $result = $this->userService->deleteFiles($request, $id, $this->user);
        // Return the response directly (no resource wrapping, likely a simple success message)
        return $this->respond($result);
    }
    #endregion ===================== End File Handling Methods =====================

    
}

