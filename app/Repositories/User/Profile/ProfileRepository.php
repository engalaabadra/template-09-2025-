<?php
namespace App\Repositories\User\Profile;

use App\Repositories\User\Profile\ProfileRepositoryInterface;

/**
 * ProfileRepository
 *
 * This is a Profile Repository class implementing the ProfileRepositoryInterface.
 */
class ProfileRepository implements ProfileRepositoryInterface
{

    /**
     * show user profile.
     * @param User $model
     * @return object || @return int
     */
    public function show($model){
        $query = $model->withoutGlobalScopes();
        // Find the record or return 404 if not found
        $item = $query->find(userApi()->id);
        return $model->getProp('eagerLoading') ? $item->load($model->getProp('eagerLoading')) : $item;
    
    }
}
