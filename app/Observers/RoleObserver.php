<?php

namespace App\Observers;

use App\Models\Role;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Traits\Observers\ProtectedActionsTrait;

/**
 * Class RoleObserver
 *
 * This observer is responsible for handling model events related to the Role model.
 * It enforces application-level restrictions to protect critical system roles from
 * being unintentionally or maliciously modified.
 *
 * Responsibilities:
 * - Intercept model events (creating, updating, deleting, restoring, etc.) on the Role model.
 * - Prevent dangerous actions (update, delete, restore, activate/deactivate) from being
 *   executed on protected roles (e.g., super admin, main admin).
 * - Throw standardized exceptions when a restricted action is attempted, ensuring that
 *   business rules are consistently enforced across the system.
 *
 * Why:
 * - To maintain system integrity by ensuring key roles cannot be altered in ways that
 *   could compromise application security or functionality.
 * - Centralize the protection logic in one place (Observer), keeping controllers and
 *   services clean and focused on business logic.
 *
 * Typical Workflow:
 * - A Role action (delete, restore, update) is triggered.
 * - The observer checks if the role is in the protected roles list.
 * - If protected, the observer stops the action by throwing an exception.
 * - If not protected, the action proceeds as normal.
 *
 * @package App\Observers
 */

class RoleObserver
{
    use ProtectedActionsTrait;

     /**
     * Handle the "updating" event.
     * Prevent updating main roles, allow only specific fields for Superadmin.
     *
     * @param Role $role
     * @throws ApiResponseException
     */
    public function updating(Role $role)
    {
        // Check if role is protected
        if ($this->isProtectedRole($role)) {
            $authUser = $role->getAuthUser();

            // 1) Block updates from everyone except Superadmin
            if (!$authUser || !$authUser->hasRole('superadmin')) {
                $this->throwProtectedException($role, 'updated');
            }

            // 2) (Optional) Even Superadmin can only update specific fields
            //    Remove this block if you want Superadmin to update anything.
            $allowed = ['display_name', 'description'];
            $dirty   = array_keys($role->getDirty());
            foreach ($dirty as $field) {
                if (!in_array($field, $allowed, true)) {
                    $this->throwProtectedException($role, "updated");
                }
            }
        }
    }

     /**
     * Handle the "deleting" event.
     * Prevent deleting protected roles.
     *
     * @param Role $role
     * @throws ApiResponseException
     */
    public function deleting(Role $role)
    {
        // If the role is protected
        if ($this::isProtectedRole($role)) {
            $this->throwProtectedException($role, 'deleted');
        }
    }

    /**
     * Handle the "restoring" event.
     * Prevent restoring protected roles.
     *
     * @param Role $role
     * @throws ApiResponseException
     */
    public function restoring(Role $role)
    {
        // If the role is protected
        if ($this::isProtectedRole($role)) {
            $this->throwProtectedException($role, 'restored');
        }
    }
    
    /**
     * Handle the "saving" event.
     * Prevent activating/deactivating protected roles.
     *
     * @param Role $role
     * @throws ApiResponseException
     */
    public function saving(Role $role)
    {
           
        // Only trigger if the "is_active" field is being changed && If the role is protected
        if ($role->isDirty('is_active') && $this::isProtectedRole($role)) {
            $this->throwProtectedException($role, 'activated/deactivated');
        }
    }

    #region ===================== Start Protected Methods =====================


    /**
     * Check if a role is protected
     */
    protected static function isProtectedRole($role): bool
    {
        return (bool) $role->is_protected
            || in_array($role->id, $role->getMainRolesIds(), true);


    }

       
    #endregion ===================== End Protected Methods =====================

}
