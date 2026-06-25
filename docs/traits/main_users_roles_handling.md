# MainUsersHandling & MainRolesHandling Traits

## Overview

These two Laravel traits provide helper methods and scopes for handling protected `main` users and `main` roles in the system. They help prevent accidental modification or deletion of critical users and roles, while providing convenient query scopes and find methods.

---

## MainUsersHandling Trait

### Features

* Retrieves protected user IDs based on main roles (`getMainUsersIds`).
* Provides query scopes:

  * `exceptMain()` – excludes main users.
  * `exceptMainWithoutTrashed()` – excludes main users, ignoring soft-deleted records.
  * `onlyTrashedExceptMain()` – retrieves only trashed users, excluding main users.
* Provides find methods that ignore main users:

  * `findUserExceptMain($id)`
  * `findUserExceptMainWithoutTrash($id)`
  * `findUserExceptMainTrash($id)`
* Handles permission checks for `superadmin` and `admin` roles.

### Usage Example

```php
$users = User::exceptMain()->get();
$user = User::findUserExceptMain($id);
```

---

## MainRolesHandling Trait

### Features

* Retrieves protected role names and IDs based on main roles (`getMainRolesNames`, `getMainRolesIds`).
* Provides helper methods:

  * `getMainRoleName()` – get cached main role name.
  * `isMainRole($role)` – check if a role is main.
* Provides query scopes:

  * `exceptMain()` – excludes main roles.
  * `exceptMainWithoutTrashed()` – excludes main roles ignoring soft-deletes.
  * `onlyTrashedExceptMain()` – retrieves only trashed roles, excluding main roles.
* Provides find methods that ignore main roles:

  * `findRoleExceptMain($id)`
  * `findRoleExceptMainWithoutTrash($id)`
  * `findRoleExceptMainTrash($id)`
* Handles permission checks for `superadmin` and `admin` roles.

### Usage Example

```php
$roles = Role::exceptMain()->get();
$role = Role::findRoleExceptMain($id);
```
