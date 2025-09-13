<?php

/**
 * ===========================================
 *  AUTH HELPERS
 * ===========================================
 * A set of authentication helper functions
 * that simplify retrieving the current user or admin based on the guard.
*/

/** =============Create Token=============== */
if (!function_exists('createToken')) {
    function createToken($user, $nameToken){
        return $user->createToken($nameToken);
    }
}

/** =============Auth Admin=============== */
if (!function_exists('adminWeb')) {
    /**
     * Get authenticated admin user from 'web' guard.
     *
     * @return \App\Models\User|null  Authenticated admin instance or null if not logged in
     */
    function adminWeb()
    {
        return auth('web')->user();
    }
}

if (!function_exists('adminApi')) {
    /**
     * Get authenticated admin user from 'admin-api' guard.
     *
     * @return \App\Models\User|null  Authenticated admin instance or null if not logged in
     */
    function adminApi()
    {
        return auth('admin-api')->user();
    }
}

/** =============Auth User=============== */
if (!function_exists('userWeb')) {
    /**
     * Get authenticated user from 'web' guard.
     *
     * @return \App\Models\User|null  Authenticated user instance or null if not logged in
     */
    function userWeb()
    {
        return auth('web')->user();
    }
}

if (!function_exists('userApi')) {
    /**
     * Get authenticated user from 'api' guard.
     *
     * @return \App\Models\User|null  Authenticated user instance or null if not logged in
     */
    function userApi()
    {
        return auth('api')->user();
    }
}

function getAuthUser()
{
    $guards = ['admin-api', 'api']; // all guards
    foreach ($guards as $guard) {
        $user = auth($guard)->user();

        if ($user) {
            return $user; 
        }
    }
    return null;
}
