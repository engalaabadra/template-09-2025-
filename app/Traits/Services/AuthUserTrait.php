<?php

namespace App\Traits\Services;

/**
 * Trait AuthUserTrait
 *
 * Provides a helper method to fetch the currently authenticated user
 * from the first available guard (`admin-api` or `api`).
 *
 * Example:
 * ```php
 * $user = $this->getAuthUser();
 * if ($user) {
 *     // Authenticated user exists
 * }
 * ```
 */
trait AuthUserTrait
{
    /**
     * Get the currently authenticated user across multiple guards.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getAuthUser()
    {
        // Define guards to check in order of priority
        $guards = ['admin-api', 'api']; 

        // Loop through guards until a user is found
        foreach ($guards as $guard) {
            if ($user = auth($guard)->user()) {
                return $user; // return the first authenticated user
            }
        }

        return null; // No user found in any guard
    }
}
