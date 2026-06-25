<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\BaseController;
use App\Repositories\Dashboard\Auth\Role\RoleRepository;
use App\Services\Dashboard\Auth\Role\RoleService;
use App\Models\Role;
use App\Resources\Auth\RoleResource;
use App\Http\Requests\Dashboard\Auth\RoleRequest;

use Inertia\Inertia;
use App\Http\Requests\BulkActivationActionRequest;
use App\Http\Requests\ActivationActionRequest;
use App\Http\Requests\RestoreActionRequest;
use App\Http\Requests\BulkDeleteActionRequest;
use App\Http\Requests\BulkRestoreActionRequest;

/**
 * Class RoleController
 *
 * Handles role management operations for dashboard including:
 * CRUD actions, activation/deactivation, trash management and file uploads.
 */
class RoleController extends BaseController
{

    #region Constructor

    /**
     * @var RoleService Handles business logic
     */
    protected $roleService;

    /**
     * @var RoleRepository Handles data access layer
     */
    protected $roleRepository;

    /**
     * @var Role
     * The Role model instance.
     */
    protected $role;

    /**
     * RoleController constructor.
     * Dependency Injection for Role model, RoleService.
     *
     * @param Role $role
     * @param RoleService $roleService
     * @param RoleRepository $roleRepository
     */
    public function __construct(Role $role, RoleService $roleService, RoleRepository $roleRepository)
    {
        $this->role = $role;
        $this->roleService = $roleService;
        $this->roleRepository = $roleRepository;
    }
    #endregion Constructor
    
    #region ===================== Start CRUD Methods: index(), show($id) ====================

    /**
     * Display a listing of roles.
     * Handles both Web and API responses.
     * - Web: returns an Inertia view or downloadable file.
     * - API: returns JSON or downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function index()
    {
        $result = $this->roleRepository->getData($this->role);  // Fetch role data (may be paginated or collection)

        // For WEB requests, render filter & data in Inertia
        if (isWebRequest()) return $result;

        // For API requests, respond with data wrapped in RoleResource
        return $this->respond($result, RoleResource::class);
    }

    /**
     * Show details of a specific role.
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function show($id)
    {
        $result = $this->roleRepository->show($id, $this->role); // Retrieve role details

        if (isWebRequest()) { // If web request, setup breadcrumb navigation
            $this->setBreadcrumb('roles', 'show', $id); 
        }

        // Respond with role data wrapped in RoleResource
        return $this->respond($result, RoleResource::class);
    }

    #endregion ===================== End CRUD Methods(Get) =====================

    #region ===================== Start CRUD Methods: store(Request $request), update(Request $request, $id), forceDelete($id), forceDeleteMany(Request $request) =====================

    /**
     * Store a new role or update an existing one.
     *
     * @param RoleRequest $request Validated role creation/update request
     * @param int|null $id Role ID to update, or null to create new
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(RoleRequest $request, $id = null)
    {
        $result = $this->roleService->store($request, $this->role, $id); // Create or update role via service
        // Respond with status 201 Created and redirect route 'dashboard.roles.index'
        return $this->respond($result, RoleResource::class);
        //return $this->respond($result, RoleResource::class, $message = null, 'dashboard.roles.index');
    }

    /**
     * Update an existing role.
     *
     * @param RoleRequest $request Validated role update request
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(RoleRequest $request, $id)
    {
        // Update the role data by calling the RoleService
        $result = $this->roleService->update($request, $id, $this->role);
        // Return the response wrapped with RoleResource,
        // which formats the role data consistently for API or web responses
        return $this->respond($result, RoleResource::class);
    }

      /**
     * Permanently delete a role from the database.
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $result = $this->roleService->forceDelete($id, $this->role);
        return $this->respond($result);
    }

    /**
     * Permanently delete multiple roles at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function forceDeleteMany(BulkDeleteActionRequest $request)
    {
        $result = $this->roleService->forceDeleteMany($request, $this->role);
        return $this->respond($result);
    }

    #endregion ===================== End CRUD Methods(Storing) =====================

    #region ===================== Start TRASH Methods: destroy($id), destroyMany(Request $request), restore(Request $request, $id), restoreMany(Request $request) =====================

    /**
     * Soft delete a role (mark as deleted without removing from DB).
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $result = $this->roleService->destroy($id, $this->role);
        return $this->respond($result, RoleResource::class);
    }

    /**
     * Soft delete multiple roles at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroyMany(BulkDeleteActionRequest $request)
    {
        $result = $this->roleService->destroyMany($request, $this->role);
        return $this->respond($result);
    }



    /**
     * Restore a soft deleted role.
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restore(RestoreActionRequest $request, $id)
    {
        $result = $this->roleService->restore($request, $id, $this->role);
        return $this->respond($result, RoleResource::class);
    }

    /**
     * Restore multiple soft deleted roles at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function restoreMany(BulkRestoreActionRequest $request)
    {
        $result = $this->roleService->restoreMany($request, $this->role);
        return $this->respond($result);
    }
    #endregion ===================== End TRASH Methods =====================

    #region ===================== Start ACTIVATION Methods changeActivate(Request $request, $id), changeActivateMany(Request $request)=====================
     /**
     * Toggle activation status (activate/deactivate) for a specific role.
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivate(ActivationActionRequest $request, $id)
    {
        $result = $this->roleService->changeActivate($request, $id, $this->role);
        
        return $this->respond($result, RoleResource::class);
    }

    /**
     * Activate or deactivate multiple roles at once.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function changeActivateMany(BulkActivationActionRequest $request)
    {
        $result = $this->roleService->changeActivateMany($request, $this->role);
        return $this->respond($result);
    }
    #endregion ===================== End ACTIVATION Methods =====================

}
