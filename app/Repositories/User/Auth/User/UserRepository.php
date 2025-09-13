<?php
namespace App\Repositories\User\Auth\User;

use App\Repositories\Eloquent\EloquentRepository;
use App\Repositories\User\Auth\User\UserRepositoryInterface;
use App\Models\User;

/**
 * UserRepository
 *
 * This is a User Repository class implementing the UserRepositoryInterface.
 */
class UserRepository implements UserRepositoryInterface
{

    // Add specific  Handling data methods here

    #region Constructor
     
    #endregion Constructor

    /**
    * FindUserByEmailOrPhone .
    * @param $data
    * @return object
    */
    public function findUserByEmailOrPhone($data)
    {
        $user = User::when(!empty($data['phone_no']) && !empty($data['country_id']), function ($query) use ($data) {
                                $query->where('phone_no', $data['phone_no'])
                                    ->where('country_id', $data['country_id']);
                            })
                            ->when(!empty($data['email']), function ($query) use ($data) {
                                $query->orWhere('email', $data['email']);
                            })
                            ->first();
        return $user;
    }
}
