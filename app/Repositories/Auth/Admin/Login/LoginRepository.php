<?php
namespace App\Repositories\Auth\Admin\Login;

use App\Models\User;

/**
 * Class LoginRepository
 *
 * Handles login-related data access logic, such as retrieving users by email or phone,
 * including their roles.
 */
class LoginRepository  implements LoginRepositoryInterface
{
    /**
     * Find a user by email or phone number along with their roles.
     *
     * This method retrieves the first user record where either:
     * - The email matches the given input, OR
     * - The phone number and country ID both match the given inputs.
     * 
     * The user’s roles are also loaded, but only the 'name' field of each role is selected.
     *
     * @param string $emailOrPhone  The user's email address or phone number.
     * @param int    $countryId     The user's country ID (required when searching by phone).
     * @return object|null          The user object with roles, or null if not found.
     */
    public function findUserWithRolesByEmailOrPhone($emailOrPhone, $countryId)
    {
        $result = User::with('roles:name') // Eager load the roles relationship, selecting only the 'name' column
            ->where('email', $emailOrPhone) // Match by email
            ->orWhere(function ($query) use ($emailOrPhone, $countryId) {
                $query->where('phone_no', $emailOrPhone) // Match by phone number
                      ->where('country_id', $countryId); // And match by country ID
            })
            ->first(); // Return the first matching user
        
        return $result;

    }
}

