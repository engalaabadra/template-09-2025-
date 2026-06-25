<?php
namespace App\Repositories\Dashboard\Auth\User;

use App\Repositories\Eloquent\EloquentRepository;
use App\Repositories\Dashboard\Auth\User\UserRepositoryInterface;

/**
 * UserRepository
 *
 * This is a User Repository class implementing the UserRepositoryInterface.
 * It provides methods such as : getData, show, trash, report
 */
class UserRepository extends EloquentRepository implements UserRepositoryInterface
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
        // Find a user by ID, ignoring protected main users
        $user = $model->findUserExceptMain($id);

        $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
            ? $user->load($model->getProp('eagerLoading'))
            : $user;

        return $data;
    }

    #region ===================== End CRUD Methods =====================

}
