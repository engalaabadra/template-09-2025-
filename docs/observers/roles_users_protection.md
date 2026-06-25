# Main Users & Roles Protection (in files : UserObserver, RoleObservder)

## Overview 

This document explains the protection mechanisms applied to main users and main roles in the system to ensure system integrity and prevent accidental or malicious modifications.

---

## 1. Types of Protection

### A. Column-Based Protection

* **Column:** `is_protected` in the database.
* Purpose: Prevent modifications directly in the database.
* Mechanism: Database triggers check if `is_protected` is `true`.

  * If `true`, any attempt to modify the record (role or user) will throw an **error message**.

**Example MySQL Triggers:**

### Roles

```sql
CREATE TRIGGER prevent_update_protected_roles
BEFORE UPDATE ON roles
FOR EACH ROW
BEGIN
    IF OLD.is_protected = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'You cannot update a protected role.';
    END IF;
END;

CREATE TRIGGER prevent_delete_protected_roles
BEFORE DELETE ON roles
FOR EACH ROW
BEGIN
    IF OLD.is_protected = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'You cannot delete a protected role.';
    END IF;
END;
```

### B. Eloquent Model Traits Protection -> Main Roles / Main Users Protection

* **Source:** `roles_structure` from `config('spatie_seeder.roles_structure', [])`.
* Stored in **cache** and also in a property in the Role model ($mainRolesNames).
and stored in cache : to Optimize performance and avoid fetching the config repeatedly.

* Mechanism: Check if the role belongs to the main roles before performing any action.

  * If yes, throw a **403 Forbidden** unless the action is permitted for the super admin.

---

## 2. Checking Protection

### Method: `isProtected`

* Checks two things:

  1. **Column Protection**: Is `is_protected` true for this role or user?
  2. **Main Role / User**: Is this role/user in the main roles/main users list?
* **Note:** If either check returns true → the role/user is considered protected.
* Even if the column is false but the user/role is main → it is still protected.

---

## 3. Permissions on Main Users and Roles

### Main Roles/Users

* Cannot be modified by anyone **except Super Admin**.
* Only certain fields in role (e.g., `display_name`, `description`), in user(e.g.,`username`) can be updated, * Core fields like `role`, `is_active`, `email`, etc., cannot be modified.
* Cannot delete or deactivate main roles.

---

## 4. Data Sources

### Main Roles

```php
// Get main role names from config and cache
$mainRolesNames = array_keys(config('spatie_seeder.roles_structure', []));
$mainRolesIds = self::withTrashed()
    ->whereIn('name', static::getMainRoleNames())
    ->pluck('id')
    ->toArray();
```

### Main Users

```php
$mainUsersIds = [];
foreach ($mainRolesNames as $role) {
    $firstUser = \App\Models\User::role($role)->orderBy('id')->first();
    if ($firstUser) {
        $mainUsersIds[] = $firstUser->id;
    }
}
```

---
# Summary of Protection Layers

This is layered protection mechanisms applied to **Roles** and **Users** in the system.  
Each layer adds an extra safeguard to ensure critical roles and users cannot be deleted or modified unintentionally.

| **Layer**   | **Mechanism**                                | **Applies To**     | **Notes** |
|-------------|-----------------------------------------------|--------------------|-----------|
| Database    | `is_protected` column + MySQL triggers        | Roles, Users       | Primary protection layer |
| Eloquent    | Traits (`ProtectsMainRoles`, `ProtectsMainUsers`) | Role, User models | Hooks into model events |
| Fallback    | Cached main roles/users IDs                   | Role, User models  | Extra safety layer |
| Config      | `roles_structure` in `spatie_seeder.php`      | Role & user creation | Defines main roles/users for seeding & caching |


## 6. Best Practices

Keep is_protected = 1 for main roles/users unless a legitimate administrative change is needed.

Avoid bypassing traits; they provide extra checks beyond database triggers.

Use seeders to consistently create main roles and default users.

Any manual changes to is_protected must be carefully reviewed to avoid accidental deletions or security issues.

## 7. Notes

These protections ensure that main roles and main users remain safe both at the database and application level.

Combining column checks, triggers, traits, and cached fallback IDs provides multiple layers of safety.

This prevents accidental deletion, updates, or deactivation, maintaining system integrity.


## 8. Summary

* **Protected by Column (`is_protected`)**: Database-level prevention.
* **Protected by Main Roles / Users that defined it in events in files observers**: Application-level prevention via cache and model properties.
* **Modification Rules:**

  * **Main Roles** → only Super Admin can modify certain fields.
  * **Main Users** → only Super Admin, Admin, or the user themselves can modify certain fields.
* **Deletion / Deactivation:** Not allowed on main users or roles (except allowed by Super Admin under restricted conditions).

> 🔒 **Critical:** Core fields cannot be modified to maintain system integrity. Only safe fields like `display_name` or `bio` are editable by allowed roles.

***props && Methods***
- $mainRolesNames: this fill from `config('spatie_seeder.roles_structure', [])`
- `$mainRolesIds` : 
```php
$mainRolesIds = self::withTrashed()
    ->whereIn('name', static::getMainRoleNames())
    ->pluck('id')
    ->toArray();
```
- `$mainUsersIds`
```php
$mainUsersIds = [];
foreach ($mainRolesNames as $role) {
    $firstUser = \App\Models\User::role($role)->orderBy('id')->first();
    if ($firstUser) {
        $mainUsersIds[] = $firstUser->id;
    }
}
```
- `isProtectedUser`:
```php
public static function isProtectedUser($user): bool
    {

        return (bool) $user->is_protected
            || in_array($user->id, static::getMainUsersIds(), true);
    }
```
- `isProtectedUser`:
```php
protected static function isProtectedRole($role): bool
    {
        return (bool) $role->is_protected
            || in_array($role->id, static::getMainRolesIds(), true);


    }
```
- one of methods event for check protection `deleting`
```php
public function deleting(User $user)
    {
        // If the user is protected
        if ($this::isProtectedUser($user)) {
            $this->throwProtectedException($user, 'deleted');
        }
    }
```

This ensures that **main roles and main users are protected** both at the database and application level, and only authorized personnel can perform safe modifications.
