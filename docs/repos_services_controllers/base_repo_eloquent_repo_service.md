# Laravel Base & Eloquent Repositories / Eloquent Service

This is explains the purpose and functionality of the `BaseRepository`, `EloquentRepository`, and `EloquentService` classes used across the project. These classes implement generic CRUD, soft delete, activation, reporting, file handling, and translation support for all Eloquent models.

---

## Table of Contents

- [BaseRepository](#baserepository)
- [EloquentRepository](#eloquentrepository)
- [EloquentService](#eloquentservice)
- [Key Features](#key-features)
- [Usage](#usage)

---

## BaseRepository

**Namespace:** `App\Repositories\Base`

**Purpose:**  
Provides generic methods for:
- Building queries dynamically with filters, search, eager loading -> `buildBaseQuery`
- Handling trashed data & report data.
- API-safe find methods (`findOrFailApi`, `findWithoutTrashedOrFail`, `findOnlyTrashedOrFail`).
- Soft delete, force delete, restore, and transactional operations.
- Excel export, statistics cards, session/toaster messages, and Inertia sharing.

# Key Methods Overview

## Query & Reports
1. **`buildBaseQuery($model, $forUser = false)`**  
   Builds a dynamic query including:  
   - Eager loading relations.  
   - Ownership restrictions (`user_id`) if `$forUser` is true.  
   - Request-based filters via `FilterTrait`.  
   - Column-based search via `SearchTrait`.  
   - Ordering by latest ID.  

2. **`handleReport($model, $filters)`**  
   Generates reports based on request types.  

3. **`handleTrash($model)`**  
   Handles retrieval of soft-deleted items.  

4. **`exportToExcel($model, $query)`**  
   Exports model data to Excel for web or API requests.  

5. **`useTransparent`, `makeStatisticCard`, `addElFileCard`**  
   UI helper methods for Inertia or frontend cards.  
   - Shares a transparency flag for UI styling.  

---

## Basic Model Retrieval
- **`findOrFailApi($id, $model, $forUser): Model`**  
  Finds a model by ID or throws a custom API exception if not found.  

- **`findWithoutTrashedOrFail($id, $model, $forUser): Model`**  
  Finds a model by ID excluding soft-deleted records, or throws if not found.  

- **`findOnlyTrashedOrFail($id, $model, $forUser): Model`**  
  Finds a soft-deleted model by ID or throws if not found.  

---

## Soft Delete & Force Delete (with Transactions)
- **`tryDelete(object $row, ?Closure $callback = null): bool`**  
  Attempts to soft-delete a model instance within a transaction and executes an optional callback.  

- **`tryForceDelete(object $row): bool`**  
  Attempts to permanently delete a model instance within a transaction.  

- **`tryDeleteForceDelete(string $model, int $id, bool $makeMessageSession): bool`**  
  Attempts to force delete a record by ID.  

- **`tryRestore(string $model, int $id, bool $makeMessageSession): void`**  
  Restores a soft-deleted record by ID within a transaction and clears `deleted_by_id`.  

---

## Request & Session Helpers
- **`requestExpectJson(): bool`**  
  Checks if the current request expects a JSON response.  

- **`makeSuccessSessionMessage(?string $message = null): void`**  
  Sets a success toast flash message for the session (non-JSON requests only).  

- **`makeErrorSessionMessage(?string $message = null): void`**  
  Sets an error toast flash message for the session (non-JSON requests only).  

- **`refreshDom(): void`**  
  Sets a session flash to trigger frontend DOM refresh.  

- **`flashShareData(array $data = []): void`**  
  Stores temporary flash data to share with the frontend.  

---

## 📌 Usage Notes
- Built for **Laravel Eloquent models**.  
- Supports **soft deletes & restores**.  
- Integrates with **Inertia.js** for frontend state sharing.  
- Provides **session-based flash messages** (toastr-style).  
- Ensures **transactional integrity** during destructive operations.  
- Works seamlessly with both **JSON API** and **traditional web requests**.  

---

## Example

```php
$repo = app(\App\Repositories\Base\BaseRepository::class);

// Find user or throw
$user = $repo->findOrFailApi($userId, User::class);

// Soft delete with callback
$repo->tryDelete($user, function($deletedUser) {
    Log::info("User deleted: {$deletedUser->id}");
});

// Export all users to Excel
$urlOrDownload = $repo->exportToExcel(User::class, User::query());

// Share breadcrumb
$repo->breadcrumb([
    ['label' => 'Users', 'url' => route('dashboard.users.index')],
    ['label' => 'Edit User'],
]);

```

---

## EloquentRepository

**Namespace:** `App\Repositories\Eloquent`

**Extends:** `BaseRepository`  
**Purpose:** Provides higher-level repository functionality with direct model handling.

**Key Methods:**

1. `getData($model, $forUser = true)`  
   Retrieves paginated or full data:
   - Prepares UI data using `InertiaShareTrait`.
   - Handles web requests, reports, trashed data, and export requests.

```php
public function getData($model, $forUser = true)
{
   /** Base Query */
   // Eager loading relations - Ownership restrictions (`user_id`) if `$forUser` is true - Request-based filters via `FilterTrait` - Column-based search via `SearchTrait` - Ordering by latest ID.  
   $query = $this->buildBaseQuery($model, $forUser); // from baseRepo

   /** Fetch data (paginated or all) */
   $data = page() ? $query->paginate() : $query->get();

   /** Prepare UI-related data for a model */
   $result = $this->prepareUiData($model, $data); // from InertiaShareTrait (result -> API: filters, WEB: render filter &data in Inertia)

   /** Handle WEB request */
   if (isWebRequest()) {
      return $result;
   }

   /** Handle REPORT request */
   if (request()->boolean('report')) {
      return $this->handleReport($model, $result); // from baseRepo
   }

   /** Handle TRASH request */
   if (request()->boolean('only_trashed')) {
      $query = $this->handleTrash($model); // from baseRepo
   }

   /** Handle EXPORT request */
   if (request()->boolean('export')) {
      return $this->exportToExcel($model, $query); // from baseRepo
   }

   /** Return in API */
   return [
      'data'    => $data,
      'filters' => $result,
   ];
}


```

2. `show($id, $model)`  
   Returns a single record with optional eager-loaded relations.

```php
public function show($id, $model)
{
   $item = $this->findOrFailApi($id, $model);

   $data = $model->getProp('eagerLoading')                 // Eager load relations if defined
      ? $item->load($model->getProp('eagerLoading'))
      : $item;

   return $data;
}
```
**Trait Used:** `InertiaShareTrait`  
- Provides `prepareUiData()` to format filters, data, and translations for Inertia.

```php
public function prepareUiData($model, $data)
{
   // === Filters ===
   $filters = $this->getModelFilters($model); // in FilterFrontTrait, Retrieve filters from the model

   // === Fetch Data ===
   $this->useFilter($filters); // in FilterFrontTrait, Share filters with Inertia or frontend

   // === Front-End UI Helpers ===
   Inertia::share(['allowSearch' => true]);   // Render search input

   if (isWebRequest()) { // Check if request is from web (not API)
      $this->setBreadcrumb(modelName($model), 'index');// Setup breadcrumb navigation
      return $this->renderWebIndexPage(modelNameSingular($model). '/Index', $result, [
            'filters' => $filters,
      ]);

   } else {//API
      return $filters;
   }
}
```
---

## EloquentService

**Namespace:** `App\Services\Eloquent`

**Purpose:**  
Generic service class for Eloquent models, supporting:
- CRUD operations
- Trash handling
- Activation/deactivation
- File and translation handling
- Bulk operations

**Traits Used:**
- `HandlesServiceTransactions` — Wraps operations in DB transactions `DB::transaction` use it im methods need to `DB::commit`, `DB::transaction`
- `HandlesBulkOperationsTrait` — Supports bulk destroy, restore, activate actions.
- `HandlesTranslationsAndFilesTrait` — Handles file uploads and translations.

**Key Methods:**

### CRUD
- `store($request, $model)` — Create a new record with validation and optional eager loading.
- `update($request, $id, $model)` — Update a record by ID with validation.
- `forceDelete($id, $model)` — Permanently deletes a single trashed record.
- `forceDeleteMany($request, $model)` — Permanently deletes multiple trashed records.

### Trash Handling
- `destroy($id, $model)` — Soft delete (or permanently delete if model doesn't use soft deletes).
- `destroyMany($request, $model)` — Bulk soft delete.
- `restore($request, $id, $model)` — Restore a trashed record.
- `restoreMany($request, $model)` — Bulk restore.

### Activation
- `changeActivate($request, $id, $model)` — Toggle or explicitly activate/deactivate a record.
- `changeActivateMany($request, $model)` — Bulk activation/deactivation.

### File Handling
- `uploadFile($request, $id, $model)` — Upload single file/image.
- `uploadFiles($request, $id, $model)` — Upload multiple files/images.
- `deleteFile($id, $model)` — Delete a single file/image.
- `deleteFiles($request, $id, $model)` — Delete multiple files/images.

**Other Notes:**
- Handles strategy patterns for restoration or activation (`StrategyActionEnum`).
- Integrates translation handling automatically via `TranslationService`.

---

## Key Features

- **Dynamic Querying:** Filters, search, eager loading automatically applied.
- **Soft Delete & Restore:** Unified handling for trashed and non-trashed records.
- **Bulk Operations:** Activation, deletion, restoration handled in bulk.
- **File Management:** Single and multiple file uploads/deletions.
- **Reports & Export:** Generates Excel files and API-ready reports.
- **Inertia Integration:** Prepares data and filters for Inertia views.
- **Session Feedback:** Unified success/error messages via flash/toasters.

---

## Usage

```php
// Example: Get paginated active banners for API
$repo = new EloquentRepository();
$data = $repo->getData(Banner::class);

// Example: Store a new banner
$service = new EloquentService(new BaseRepository(), new TranslationService());
$newBanner = $service->store($request, Banner::class);

// Example: Soft delete
$service->destroy($id, Banner::class);

// Example: Restore
$service->restore($request, $id, Banner::class);

// Example: Activate
$service->changeActivate($request, $id, Banner::class);
```


## Exceptions & Errors

- Throws `ApiResponseException` with `ServiceResponseEnum::NOT_FOUND` when records are not found.

---

## Usage Notes

- Designed for use with Laravel Eloquent models.
- Supports soft deletes and restores.
- Integrates with Inertia.js for frontend state sharing.
- Handles session-based flash messages with toastr-style notifications.
- Uses database transactions to ensure data integrity during destructive operations.
- Designed to work with both JSON API and traditional web requests.
---