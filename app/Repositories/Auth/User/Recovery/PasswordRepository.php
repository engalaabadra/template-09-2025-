<?php
namespace App\Repositories\Auth\User\Recovery;

use App\Models\User;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

/**
 * Class PasswordRepository
 *
 * This class handles data retrieval logic related to password actions,
 * such as finding a user by email or phone number.
 */
class PasswordRepository implements PasswordRepositoryInterface
{
    /**
     * Find a user by either email or phone number (with country ID).
     *
     * Applies conditional queries based on the presence of 'email' or 'phone_no' in the input array.
     * Returns a `notFound` service response if the user does not exist.
     *
     * @param array $data  The user identifying data (email or phone number and country ID).
     * @return object      The user object if found, or a `notFound` response if not.
     */
    public function findUserByEmailOrPhone($data)
    {
        $user = User::when(isset($data['email']), function ($query) use ($data) {
                    // Apply email-based filtering if 'email' is provided
                    return $query->where('email', $data['email']);
                })
                ->when(isset($data['phone_no']), function ($query) use ($data) {
                    // Apply phone number and country ID filtering if 'phone_no' is provided
                    return $query->where([
                        'phone_no'   => $data['phone_no'],
                        'country_id' => $data['country_id'],
                    ]);
                })
                ->first(); // Retrieve the first matching user

        // Return notFound response if no user matched
        if (!$user) throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

        return $user;
    }
}

