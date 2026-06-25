<?php

namespace App\Observers;

use App\Models\User;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Traits\Observers\ProtectedActionsTrait;

/**
 * Class UserObserver
 *
 * The UserObserver centralizes logic that enforces application-level restrictions
 * on User model events. It ensures that protected users (e.g., super admins or
 * system-defined accounts) are safeguarded from destructive or unauthorized actions.
 *
 * Core Responsibilities:
 * - Intercept lifecycle events on the User model (creating, updating, deleting, restoring).
 * - Block dangerous operations (update, delete, restore, activate/deactivate) if
 *   the target user is classified as "protected".
 * - Define consistent business rules to determine what makes a user protected.
 * - Provide safe utility methods for querying or filtering non-protected users.
 *
 * Protected User Definition:
 * A user is considered protected if any of the following conditions apply:
 * - The `is_protected` column on the user record is set to `true`.
 * - The user is among the first accounts created and assigned to critical system roles
 *   (e.g., Super Admin, Admin, or other foundational roles required for system integrity).
 *
 * Why this is important:
 * - Prevents accidental or malicious modification of essential system accounts that are
 *   required to manage and secure the application.
 * - Ensures that high-privilege accounts (like the Super Admin) cannot be deleted,
 *   deactivated, or restored in ways that would compromise security.
 * - Centralizes this protection in one place (the observer) so that controllers,
 *   services, and repositories remain clean, focusing only on business logic.
 *
 * Typical Workflow:
 * 1. A User model event is triggered (e.g., delete, update, restore).
 * 2. The observer checks whether the user falls under the "protected" criteria.
 * 3. If the user is protected:
 *    - The observer halts the action and throws a standardized exception.
 * 4. If the user is not protected:
 *    - The action proceeds normally.
 *
 * Example Scenarios:
 * - Attempting to delete the Super Admin account → Blocked.
 * - Attempting to deactivate a system-defined Admin account → Blocked.
 * - Updating a normal user’s profile → Allowed.
 * - Restoring a soft-deleted non-protected user → Allowed.
 *
 * @package App\Observers
 */

class UserObserver
{
    use ProtectedActionsTrait;

     /**
     * Handle the "updating" event.
     *
     * Prevents updating protected users, except for a few allowed fields.
     *
     * @param User $user
     * @return void
     * @throws ApiResponseException
     */
    public function updating(User $user)
    {
        if ($this->isProtectedUser($user)) {
            
            $authUser = $user->getAuthUser();

            if (!$authUser || $authUser->id !== $user->id) {
                $this->throwProtectedException($user);
            }
            // Allow updating only fcm_token, remember_token
            $allowed = ['fcm_token', 'is_author', 'remember_token', 'created_by_id', 'created_by_type', 'updated_by_id', 'updated_by_type', 'deleted_by_id', 'deleted_by_type'];//these fields updated auto in login
            $basicProtected = [
                'email',
                'password',
                'phone_no',
                'country_id',
            ];
            // Get changed fields
            $dirty = array_keys($user->getDirty());

             // Loop through all changed fields
            foreach ($dirty as $field) {
                if (in_array($field, $basicProtected, true)) {
                    $this->throwProtectedException($user, "updated");
                }
                // If the field is not allowed, throw an exception
                if (!in_array($field, $allowed, true)) {
                    // Block updates from every one , in future if i added another fields like (username) in users table and if not effect on system if change it , can put here condtition only superadmin can update on it
                    $this->throwProtectedException($user, "updated");
                }
            }
            
        }
    }


     /**
     * Handle the "deleting" event.
     *
     * Prevents deleting protected users.
     *
     * @param User $user
     * @return void
     * @throws ApiResponseException
     */
    public function deleting(User $user)
    {
        // If the user is protected
        if ($this::isProtectedUser($user)) {
            $this->throwProtectedException($user, 'deleted');
        }
    }

    /**
     * Handle the "restoring" event.
     *
     * Prevents restoring protected users.
     *
     * @param User $user
     * @return void
     * @throws ApiResponseException
     */
    public function restoring(User $user)
    {
        // If the user is protected
        if ($this::isProtectedUser($user)) {
            $this->throwProtectedException($user, 'restored');
        }
    }
    
    /**
     * Handle the "saving" event.
     *
     * Prevents activating/deactivating protected users.
     *
     * @param User $user
     * @return void
     * @throws ApiResponseException
     */
    public function saving(User $user)
    {            
        // Only trigger if the "is_active" field is being changed
        if ($user->isDirty('is_active') && $this::isProtectedUser($user)) {
            $this->throwProtectedException($user, 'activated/deactivated');
        }
    }

    #region ===================== Start Protected Methods =====================


    /**
     * Determine if a user is protected.
     *
     * Conditions:
     * - User has `is_protected = true`.
     * - User ID is in the cached list of main role users.
     *
     * @param User $user
     * @return bool
     */
    public static function isProtectedUser($user): bool
    {

        return (bool) $user->is_protected
            || in_array($user->id, User::getMainUsersIds(), true);
    }

    
    #endregion ===================== End Protected Methods =====================

}
