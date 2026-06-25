

## Routing

This section documents the custom resource routing implementation provided in the project.  
It extends Laravel’s default `ResourceRegistrar` to support additional **bulk actions**, **file handling**, and **flexible route registration**.

---

## Classes Overview

### 1. `ResourceRegistrarCustom`
Extends Laravel’s `ResourceRegistrar` to register **custom resource routes** for bulk operations and advanced actions.

- **Default Laravel Actions**: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- **Custom Routes Added**:
  - `PATCH /{resource}/actions/activate` → `changeActivateMany`
  - `PATCH /{resource}/actions/restore` → `restoreMany`
  - `PATCH /{resource}/{id}/actions/restore` → `restore`
  - `PATCH /{resource}/{id}/actions/activate` → `changeActivate`
  - `DELETE /{resource}/actions/destroy` → `destroyMany`
  - `DELETE /{resource}/{id}/actions/force` → `forceDelete`
  - `DELETE /{resource}/actions/force` → `forceDeleteMany`

#### Methods
- `registerCustomResource($name, $controller, $options = [])`: Registers default + custom routes with support for filtering (`custom_only`, `custom_except`).
- `addResourceRestoreMany`, `addResourceRestore`, `addResourceChangeActivate`, `addResourceChangeActivateMany`, `addResourceDestroyMany`, `addResourceForceDelete`, `addResourceForceDeleteMany`: Define individual route bindings.

---

### 2. `ResourceRegistrarFiles`
Specialized `ResourceRegistrar` for **file-related operations**.

- **Default Actions**: `uploadFile`, `uploadFiles`, `deleteFile`, `deleteFiles`
- **Custom Routes Added**:
  - `POST /{resource}/{id}/file` → `uploadFile`
  - `POST /{resource}/{id}/files` → `uploadFiles`
  - `DELETE /{resource}/{id}/file` → `deleteFile`
  - `DELETE /{resource}/{id}/files` → `deleteFiles`

#### Methods
- `registerCustomResource($name, $controller, $options = [])`: Registers all file-related routes.
- `addResourceUploadFile`, `addResourceUploadFiles`, `addResourceDeleteFile`, `addResourceDeleteFiles`: Define individual file route bindings.

---

### 3. `PendingCustomResourceRegistration`
Helper class for **lazy registration** of custom routes.

#### Key Features
- Accepts router + route definitions.
- Supports filtering via:
  - `only([...])`: Keep only specific methods.
  - `except([...])`: Remove specific methods.
- `register()`: Registers all remaining routes with the Laravel router.
- Automatic route registration when object is destroyed (`__destruct`).

---

## ⚡ Example Usage

```php
use App\Routing\ResourceRegistrarCustom;
use App\Routing\ResourceRegistrarFiles;

// Register custom bulk resource routes
Route::resource('users', UserController::class);
(new ResourceRegistrarCustom(app('router')))
    ->registerCustomResource('users', UserController::class);

// Register file-related resource routes
(new ResourceRegistrarFiles(app('router')))
    ->registerCustomResource('documents', DocumentController::class);
```


## Rules (Custom Validation)

This document provides an overview of the custom validation rules implemented in the application.

---

## UniqueActiveAndNotDeleted
- Ensures a value is unique only among active and not-deleted records.
- Ignores soft-deleted or inactive records during uniqueness checks.
- Supports ignoring a specific record ID when updating.

**Use case:** Validate unique fields (like email, slug) only for active records.

---

## UniqueTranslationValue
- Guarantees a value is unique **per language**.
- Prevents duplicate values in the same language, across translation groups.
- Allows duplicates across different languages or within the same record.

**Use case:** Ensure uniqueness of translated fields (e.g., `title`) in multilingual content.

---

## UniqueWithoutSoftDeletes
- Similar to Laravel’s `unique` rule but ignores soft-deleted rows (`deleted_at IS NULL`).
- Optionally skips a specific record ID for updates.

**Use case:** Avoid conflicts with trashed records when validating unique fields.

---

## SmallTextRule
- Validates text length between **2 and 100 characters**.
- Ideal for names, short titles, or small text inputs.

**Use case:** Enforce proper length for user-provided small text fields.

---

## PhoneNumberRule
- Validates phone numbers:
  - Must be numeric.
  - Length between 7 and 14 digits.
  - Matches regex for valid number format.

**Use case:** Standardize phone number inputs (e.g., local/mobile numbers).

---

## ImageRule
- Validates a single uploaded image:
  - Must be an image (`jpg`, `png`, `jpeg`).
  - Max size: **3MB**.

**Use case:** Profile pictures, thumbnails, or single image uploads.

---

## FilesRule
- Validates multiple uploaded files:
  - Must be an array of files.
  - Each file must be one of (`pdf`, `doc`, `docx`, `jpg`, `png`, `xlsx`).
  - Max size: **500MB per file**.

**Use case:** Bulk file uploads (documents, media).

---

## FileRule
- Validates a single uploaded file:
  - Allowed types: `pdf`, `doc`, `docx`, `jpg`, `png`, `xlsx`.
  - Max size: **500MB**.

**Use case:** Single document or media uploads.

---

## IdsRule
- Validates ID inputs in flexible formats:
  - `"all"` (string).
  - `["all"]` (array).
  - Single integer or numeric string.
  - Array of integers.
  - Comma-separated string (`"1,2,3"`).

**Use case:** Bulk actions where IDs may come in different formats.

---

 These rules extend Laravel’s validation capabilities to handle **multilingual data, soft deletes, file uploads, and bulk actions**.

##  Requests

# Form Request Classes Documentation

This document provides a simplified explanation of the main **Form Request** classes used in the application.

---

## BaseRequest
- Base class for all requests.
- Adds shared features:
  - Returns validation errors as JSON (instead of redirect).
  - Automatically decodes JSON strings to arrays.
  - Normalizes `ids`-like fields.
  - Supports dynamic validation for translation fields.
- Goal: centralize common logic and reduce duplication.

---

## UserRequest
- Handles user creation and update in the dashboard.
- Validates:
  - Phone and email (must be valid and unique).
  - Username (required, unique).
  - Roles (must exist, except super admin).
  - Image and files upload.
  - Extra info: full name, nickname, gender, birth date.
- Supports **translations** for multilingual fields.

---

## BannerRequest
- Handles banner creation and update in the dashboard.
- Validates:
  - Title (required, unique).
  - Description (optional, large text).
  - URL (must be a valid link if provided).
  - Image (required, valid image).
- Supports **translations** for multilingual fields.

---

## BaseBulkActionRequest
- Base for **bulk actions** (like delete, restore, activate).
- Validates:
  - `ids` field must be either:
    - `"all"` → all items.
    - Or a non-empty array of valid integers.

---

## BulkActivationActionRequest
- Extends `BaseBulkActionRequest`.
- Adds validation for:
  - `action_activation`: activate, deactivate, or toggle.
  - `strategy`: modify, replace, or prevent.

---

## BulkDeleteActionRequest
- Extends `BaseBulkActionRequest`.
- Used for **bulk delete**.
- Only validates `ids` (no extra rules).

---

## BulkRestoreActionRequest
- Extends `BaseBulkActionRequest`.
- Used for **bulk restore**.
- Adds validation for:
  - `strategy`: modify, replace, or prevent.

---

## Summary
- **BaseRequest** → Core logic for all requests.  
- **UserRequest** → User validation.  
- **BannerRequest** → Banner validation.  
- **BaseBulkActionRequest** → Core bulk action logic.  
- **BulkActivationActionRequest** → Activate/Deactivate in bulk.  
- **BulkDeleteActionRequest** → Bulk delete.  
- **BulkRestoreActionRequest** → Bulk restore.  


## Resources
This document explains the `BaseResource` and `UserResource` classes inside the **App\Resources** namespace.

---

## 1. BaseResource

**File:** `App/Resources/BaseResource.php`  
**Extends:** `Illuminate\Http\Resources\Json\JsonResource`  

### Purpose
- Provides a **generic base resource** to transform Eloquent models into structured API responses.
- Uses `getSmartAttributes()` to filter and include only the required attributes and relations.

### Key Logic
- If the underlying `$resource` is already an array → return it directly.
- Otherwise, it checks if the current resource defines `relationsResources`, which maps Eloquent relations to their corresponding Resource classes.
- Calls `getSmartAttributes($relationsResources)` on the model to retrieve attributes and relation data in a clean format.

---

## 2. UserResource

**File:** `App/Resources/UserResource.php`  
**Extends:** `App\Resources\BaseResource`  

### Purpose
Transforms the `User` model into an API response including its **attributes, translations, and related resources**.

### Relations Mapped
The following model relations are automatically wrapped with their corresponding Resources:
- `roles` → `RoleResource`
- `profile` → `ProfileResource`
- `files` → `FileResource`
- `country` → `CountryResource`

### Example Output
```json
{
    "lang": "ar",
    "email": "employee@example.com",
    "username": "موظف1",
    "translations": [
        {
            "lang": "en",
            "username": "employee Name"
        },
        {
            "lang": "fr",
            "username": "Nom de l'enseignant"
        }
    ],
    "profile": { ... },
    "roles": [ ... ],
    "files": [ ... ],
    "country": { ... }
}
```

### Transformation Flow
1. Calls `parent::toArray($request)` → loads base attributes using `BaseResource`.
2. Prepares extra handling logic (if needed) for `profile`, `roles`, `files`, `country`, etc.
3. Returns a **clean JSON-ready array**.

---

## Summary

- **BaseResource** → provides the foundation for API resource transformation with smart attribute filtering.  
- **UserResource** → extends the base, applying specific relation mappings (roles, profile, files, country).  
- This structure keeps API responses **consistent, extensible, and maintainable** across different models.

## Observers

This document explains the purpose and functionality of the observers used in the project.

---

## 1. FileObserver
**Purpose**  
Handles model events for the `File` model.  
Prevents creating or deleting files that belong to protected users (e.g., Super Admin).

**Key Events**
- **creating** → Blocks file upload if the file belongs to a protected user.  
- **deleting** → Blocks file deletion if the file belongs to a protected user.

---

## 2. RoleObserver
**Purpose**  
Protects critical system roles (e.g., super admin, admin) from unauthorized modifications.  

**Key Events**
- **updating** → Blocks updates unless the user is superadmin, and even then only for specific allowed fields.  
- **deleting** → Prevents deletion of protected roles.  
- **restoring** → Prevents restoring protected roles.  
- **saving** → Prevents activating or deactivating protected roles.

**Why**  
Ensures system integrity and prevents accidental or malicious modifications to essential roles.

---

## 3. UserObserver
**Purpose**  
Protects critical users (e.g., super admins, system accounts) from destructive or unauthorized actions.

**Key Events**
- **updating** → Blocks updates unless done by the same user, and only for specific allowed fields.  
- **deleting** → Prevents deleting protected users.  
- **restoring** → Prevents restoring protected users.  
- **saving** → Prevents activating or deactivating protected users.

**Protected User Definition**
- `is_protected = true`  
- User ID is in the main role users list.

**Why**  
Prevents security risks and ensures high-privilege accounts cannot be modified in unsafe ways.

---

## Provider
# AppServiceProvider

The `AppServiceProvider` is a central service provider in the Laravel application. It is responsible for bootstrapping global configurations, route macros, service bindings, and model observers that enforce system-wide consistency.

---

## Responsibilities

- **Custom Route Binding**
  - Replaces Laravel's default `ResourceRegistrar` with a custom implementation:  
    `\App\Routing\ResourceRegistrarCustom`
  - Adds new router macros:
    - `customResource($name, $controller, $options = [])`: Registers resource routes with additional flexibility.
    - `customResourceFiles($name, $controller, $options = [])`: Special resource registrar for file upload/download controllers.

- **Frontend Integration**
  - Configures [Vite](https://laravel.com/docs/vite) prefetching with limited concurrency (`concurrency: 3`) to optimize asset loading.

- **Model Observers**
  - Attaches observers to critical models to enforce protection rules:
    - `UserObserver` → Blocks destructive actions on protected users.
    - `RoleObserver` → Prevents unsafe modifications to protected roles.
    - `FileObserver` → Restricts file creation/deletion for protected users.

- **Cache Warm-up**
  - Ensures system caches are initialized for key role and user data:
    - `main_roles_ids`
    - `main_roles_names`
    - `main_users_ids`

---

## Purpose

The provider centralizes application-wide configurations such as:
- Route customization
- Model-level protections
- Performance optimizations
- Cache initialization

This ensures consistent behavior across the system without duplicating logic in controllers or services.

---

## Example Usage

```php
// Register a custom resource route
Route::customResource('users', UserController::class);

// Register a file-based resource route
Route::customResourceFiles('documents', DocumentController::class);
```

## Exceptions

## ApiResponseException

A custom exception class to standardize API error responses.

**Responsibilities:** - Ensure all API errors follow the same JSON
format. - Provide control over status codes, messages, errors, and
additional data. - Simplify frontend error handling by avoiding
inconsistent structures.

**Example Usage:**

``` php
throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST, 'Validation failed', [
    'email' => ['The email field is required.'],
]);
```

------------------------------------------------------------------------

## Exception Handlers (exceptions.php)

Centralized configuration for handling all exceptions consistently.

**Why:** - Ensures unified API error structure. - Converts Laravel's
default HTML errors into JSON format. - Improves frontend client
experience.

**Handled Cases:** 1. **ValidationException (422)** → Returns validation
errors. 2. **AuthenticationException (401)** → User not logged in. 3.
**AuthorizationException / AccessDeniedHttpException (403)** → No
permission. 4. **ModelNotFoundException (404)** → Record not found in
DB. 5. **NotFoundHttpException (404)** → Route does not exist. 6.
**MethodNotAllowedHttpException (405)** → Invalid HTTP method. 7.
**QueryException (500 / 403)** → Database errors. Handles SIGNAL custom
messages. 8. **PDOException (500)** → DB connection issues. 9.
**HttpException (4xx/5xx)** → Generic HTTP errors. 10.
**ApiResponseException** → Project-specific standardized exceptions. 11.
**Throwable (500)** → Catch-all fallback.

**Example JSON Response:**

``` json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  },
  "data": []
}
```

## Enums
This document explains the enums defined in the application, their purpose, and example usage.

---

## 1. `ActivationActionEnum`

**Namespace:** `App\Enums`

### Description
Represents actions for changing a model’s activation state.

### Cases
- **ACTIVATE** → Marks as active.
- **DEACTIVATE** → Marks as inactive.
- **TOGGLE** → Flips the current state (active ↔ inactive).

### Benefits
- Avoids magic strings.
- Ensures type safety and readability.

### Example
```php
$action = ActivationActionEnum::TOGGLE;

if ($action === ActivationActionEnum::ACTIVATE) {
    $model->is_active = true;
}
```

---

## 2. `IsActiveEnum`

**Namespace:** `App\Enums`

### Description
Represents the possible active/inactive states of a model.

### Cases
- **ACTIVE (1)** → Active state.
- **NOT_ACTIVE (0)** → Inactive state.

### Traits Used
- **EnumOptionsTrait** → Provides helper methods for enums.

### Example
```php
$status = IsActiveEnum::ACTIVE;
echo $status->text(); // "Active"
```

---

## Summary
- `ActivationActionEnum` → Defines actions to activate, deactivate, or toggle states.
- `IsActiveEnum` → Defines the actual state (active vs not active).

## Helpers


This document provides an overview of the custom helper functions and utility classes included in this project.  
They are grouped by category for clarity.

---

## 🔐 Auth Helpers
Helpers for authentication across multiple guards (`web`, `api`, `admin-api`).

- **createToken($user, $nameToken)** → Generate personal access token.  
- **adminWeb()** → Get authenticated admin from `web` guard.  
- **adminApi()** → Get authenticated admin from `admin-api` guard.  
- **userWeb()** → Get authenticated user from `web` guard.  
- **userApi()** → Get authenticated user from `api` guard.  
- **getAuthUser()** → Return first authenticated user from available guards.

---

## 🌐 General & Request Helpers
Utilities for handling requests, localization, and system info.

- **isWebRequest()** → Check if request is web (non-API).  
- **localeLang() / defaultLang()** → Get current or default locale.  
- **supportedLanguages()** → Supported languages list.  
- **systemCurrency() / countryCurrency()** → Currency codes.  
- **isDefaultLocale($lang)** → Check if language is default.  
- **urlFlag($code)** → Get flag image by ISO code.  
- **page(), query(), clientId(), active(), status(), message(), rate(), fav(), type(), login_type(), randomLink()** → Request input shortcuts.  
- **total()** → Get pagination size (default 10).  
- **currentTime() / currentDate()** → Current timestamp helpers.  
- **getCode()** → Random verification code (0000 in non-production).  
- **filePath($url)** → Convert public URL to internal storage path.  
- **normalizeActivatedField($value)** / **normalizeRestoredField($value)** → Clean field names from `_activated` / `_restored`.  
- **extractIdFromValue($value)** → Extract numeric ID from string with underscores.

---

## 📦 Model Helpers
Utilities for interacting with Eloquent models.

- **isSoftDeletes($model)** → Check if model uses `SoftDeletes`.  
- **modelName($model)** → Get plural lowercase model name.  
- **modelNameSingular($model)** → Get singular model name.  
- **getModelClass($modelName)** → Resolve model class by name.  
- **getTableFromRouteModel($paramName = null)** → Get bound model’s table from route.  
- **refreshIfMissing($data, $model, $key = 'is_active')** → Refresh model if key missing from request.

---

## Payment Helpers
Helpers for handling external payment services.

- **getTokenPayment($paymentMethod)**  
  - For `moyasar` → Returns base64-encoded live key.  
  - For `tap` → Returns base64-encoded live key.  
  - Otherwise → `null`.

---

## Report Helper
Reusable reporting queries for models.

- **ReportHelper::getCommonReports($countAlias = 'records_count')**  
  - `by_active` → Group by `is_active`.  
  - `by_date` → Group by record creation date.

---

## 👤 Role Helper
Helpers for role-based checks.

- **RoleHelper::getMainRoleName()** → Get main role name (from cache/config).  
- **RoleHelper::isMainRole($role)** → Check if given role is the main role.

---

##  Summary
These helpers provide a clean, reusable way to handle **authentication, localization, request handling, model utilities, payment integrations, reporting, and role management** throughout the project.
