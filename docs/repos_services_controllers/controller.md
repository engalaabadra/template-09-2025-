# UserController README

# UserController (Dashboard/Auth)

## Overview

`UserController` is a Laravel controller responsible for managing users in the dashboard. It handles CRUD operations, activation/deactivation, trash management (soft delete & restore), role/permission assignment, and file uploads. The controller integrates with `UserService` for business logic and `UserRepository` for data access.

---

## Dependencies

* **UserService**: Handles business logic.
* **UserRepository**: Handles database queries and retrieval.
* **User**: The main model representing users.
* **UserResource**: Formats user data for API responses.
* **RoleResource / PermissionResource**: Formats related role/permission data.
* **Requests**: Various form request classes for validation.

---

## Constructor

```php
public function __construct(User $user, UserService $userService, UserRepository $userRepository)
```

* Injects dependencies: `User`, `UserService`, `UserRepository`.
* Initializes protected properties `$user`, `$userService`, `$userRepository`.

---

## Methods

### 1. index()

Fetches all users.

* Web: returns data for Inertia view.
* API: returns JSON wrapped in `UserResource`.

### 2. show(\$id)

Fetches a specific user by ID.

* Sets breadcrumbs if web request.
* Returns data via `UserResource`.

### 3. getRolesUser(\$userId)

Returns roles assigned to a user.

* Uses `UserService`.
* Returns `RoleResource`.

### 4. getPermissionsRoleUser(\$roleUserId)

Fetches permissions associated with a user's role.

* Returns `PermissionResource`.

### 5. store(UserRequest \$request, \$id = null)

Creates or updates a user.

* Validates via `UserRequest`.
* Uses `UserService->store()`.
* Returns `UserResource`.

### 6. update(UserRequest \$request, \$id)

Updates a user.

* Returns `UserResource`.

### 7. changeActivate(ActivationActionRequest \$request, \$id)

Activates/deactivates a single user.

* Returns `UserResource`.

### 8. changeActivateMany(BulkActivationActionRequest \$request)

Activates/deactivates multiple users.

* Returns plain response.

### 9. destroy(\$id)

Soft deletes a user.

* Returns `UserResource`.

### 10. destroyMany(BulkDeleteActionRequest \$request)

Soft deletes multiple users.

* Returns plain response.

### 11. forceDelete(\$id)

Permanently deletes a user.

* Returns plain response.

### 12. forceDeleteMany(BulkDeleteActionRequest \$request)

Permanently deletes multiple users.

* Returns plain response.

### 13. trash()

Retrieves all soft-deleted users.

* Returns `UserResource`.

### 14. restore(RestoreActionRequest \$request, \$id)

Restores a soft-deleted user.

* Returns `UserResource`.

### 15. restoreMany(BulkRestoreActionRequest \$request)

Restores multiple soft-deleted users.

* Returns plain response.

---

## File Management Methods

### uploadFile(UploadImageRequest \$request, \$id)

Uploads a single file (image).

* Returns updated `UserResource` with uploaded file.

### uploadFiles(UploadFilesRequest \$request, \$id)

Uploads multiple files.

* Eager loads `files` relation.
* Returns updated `UserResource`.

### deleteFile(\$id)

Deletes a single file.

* Returns plain response.

### deleteFiles(DeleteFilesRequest \$request, \$id)

Deletes multiple files.

* Returns plain response.

---

## Notes

* All responses for API are consistently formatted using `UserResource`.
* Web requests leverage InertiaJS for page rendering.
* Validation is centralized in custom Request classes.
* Bulk actions use specific Request classes to handle arrays of IDs and enforce rules.
* `_text` dynamic attributes are handled via resources to provide human-readable values for enums and dates.

---

**End of README**
