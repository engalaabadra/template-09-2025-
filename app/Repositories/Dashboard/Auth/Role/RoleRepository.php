<?php
namespace App\Repositories\Dashboard\Auth\Role;

use App\Repositories\Eloquent\EloquentRepository;

/**
 * RoleRepository
 *
 * This is a Role Repository class implementing the RoleRepositoryInterface.
 * It provides methods such as : getData, show
 */
class RoleRepository extends EloquentRepository implements RoleRepositoryInterface
{
    #region ===================== Start CRUD Methods extends from EloquentRepository: getData($model), show($model, $id) =====================

    
    /**
     * Show a specific record.
     *
     * @param int $id The ID of the record to show.
     * @param object $model The model to query.
     * @param  bool    $forUser  if false (show data that only has it this user)
     * 
     * @return object The requested record.
     */
    public function show($id, $model, $forUser = false)
    {
        // Find the role by ID with ignore protected main roles
        $role = $model->findRoleExceptMain($id);

        $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
            ? $role->load($model->getProp('eagerLoading'))
            : $role;

        return $data;
    }

    #region ===================== End CRUD Methods =====================

}