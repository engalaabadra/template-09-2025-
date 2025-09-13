
# Traits Models

This section provides a detailed explanation of the core reusable **Traits** implemented in the project.  
Each trait encapsulates reusable logic for Eloquent models, enhancing maintainability and consistency across the application.

---

## SmartAttributesTrait
- **Purpose**: Enhances Eloquent models with automatic casting and dynamic `*_text` attributes.  
- **Key Features**:
  - Merges `$autoCasts` with `$casts` automatically.
  - Dynamically generates text-based attributes for enums and dates.
  - Provides `getSmartAttributes()` for recursive relation handling.

---

## BaseModelTrait
- **Purpose**: Combines multiple traits to extend Eloquent models with reusable features.  
- **Included Traits**: `AuthUserTrait`, `SmartAttributesTrait`, `FillableTrait`, `EnumOptionsTrait`, `MorphModelTriggerTrait`, `SafePropsTrait`, `ReportableTrait`, `ForceCascadeDeleteTrait`.  
- **Key Features**:
  - Handles smart attributes, safe property access, reporting, cascading deletes, and authenticated user helpers.

---

## EnumOptionsTrait
- **Purpose**: Utilities for handling PHP Enums in forms, filters, and translations.  
- **Key Methods**:
  - `getLangOptions($langs)` → Build dropdown options for languages.
  - `getOptionsData()` / `getOptionsIdNameData()` / `getOptionsPluckData()` → Generate options for Enums.
  - `text()`, `translate()`, `getTrans()` → Translate enum values to labels.
  - `names()`, `values()`, `array()` → Retrieve enum names/values.

---

## FillableTrait
- **Purpose**: Restrict input data to `$fillable` fields.  
- **Key Method**:
  - `onlyFillable(array $data)` → Returns only the allowed fields for safe mass assignment.

---

## ForceCascadeDeleteTrait

**Purpose**:  
Automatically removes or detaches related records when a model is deleted.  
Useful for enforcing database integrity without relying on database-level cascading (Ensures relational data integrity during deletions)

**Key Method:**

### `handleForceCascadeDelete(Model $model): void`
- **Functionality**: Iterates through the `$forceCascadeDelete` property defined in the model and deletes/detaches related records.  
- **Behavior per relation type**:  
  - **HasMany / MorphMany** → Deletes all related records.  
  - **HasOne / MorphOne** → Deletes the related record and, if relation is media (`file`, `files`, `image`, `images`), triggers specialized deletion helpers.  
  - **BelongsToMany** → Detaches the pivot table records.  
- **Logging**: Writes info/warning logs when relations are deleted or skipped.  

**Example usage inside a model:**
```php
class Post extends Model {
    use ForceCascadeDeleteTrait;
    protected array $forceCascadeDelete = ['comments', 'tags'];
}
```

---

## HasMediaTrait 

**Purpose**:  
Centralizes file and media handling logic (upload, update, delete, attach) for models.  (Provides centralized media/file management utilities. )
Assumes media relations are defined via `MediaRelationsTrait` (e.g., `image()`, `images()`, `file()`, `files()`).

**Key Methods:**

### `handleFiles($request, $model, $item)`
- Processes validated request data.  
- Handles both single (`file`, `image`) and multiple (`files`, `images`) media inputs.  

### `storeMediaFileInFolder(UploadedFile $file, string $type, string $folder): string`
- Generates a unique filename with timestamp.  
- Stores file in `uploads/{folder}` within the `public` disk.  
- Returns the stored path.  

### `uploadSingleMedia(UploadedFile $file, string $type, string $folder): string`
- Stores the file, generates its URL, and updates/creates a single related record.  
- Supports `file` and `image`.  

### `uploadMultipleMedia(array $files, string $type, string $folder): array`
- Stores multiple files and creates related records in bulk.  
- Clears existing records before inserting new ones if the relation already exists.  

### `deleteMediaByIds($ids, string $relation)`
- Deletes media records by IDs or all (`'all'`).  
- Removes physical files as well.  

### `deleteSingleMedia(string $relation): void`
- Deletes one related media item and its physical file.  

---

## HelpersModelTrait -> Supplies reusable query builder helpers for filtering, status, and relations.

**Purpose**:  
Provides reusable query builder helpers for Eloquent models.  
Simplifies applying filters, ranges, statuses, and dynamic conditional queries.

**Key Methods:**

### `createdAtRange(string|array|null $dateRange, $column = 'created_at'): static`
- Filters results by a `created_at` range.  

### `updatedAtRange(?string $dateRange): static`
- Filters results by an `updated_at` range.  

### `isActive(?bool $active = null): static`
- Applies `where is_active = ?` condition only if `$active` is not null.  

### `lang(?string $lang): static`
- Adds a `where lang = ?` condition if the model’s table has a `lang` column.  

### `rangeDateFilter($date_range, $column = 'created_at'): static`
- Generic date range filter.  
- Uses `DateHelper::getRangeFromRequestPeriod`.  

### `whereOrWhereIn(string $column, $values = null, bool $search_for_null)`
- Smart conditional filter.  
- Chooses between `where`, `whereIn`, or skipping.  

### `filterStatus($values = [], $column_name = 'status')`
- Shortcut for filtering by `status`.  

### `columnWhereOrWhereIn($column_name, $values = [])`
- Applies `whereOrWhereIn` on a given column.  

### `relationColumnWhereOrWhereIn($relation, $column_name, $values = [])`
- Applies `whereOrWhereIn` inside a related model query.  

---

## MorphModelTriggerTrait -> Automates auditing fields, soft deletes, and pruning.

**Purpose**:  
Adds auditing fields (`created_by`, `updated_by`, `deleted_by`) automatically using polymorphic relationships.  
Also integrates **SoftDeletes** and **Prunable** features for lifecycle management.

**Key Features:**

- **Relationships**:  
  - `createdBy()`, `updatedBy()`, `deletedBy()` → Return `morphTo` polymorphic relations.  

- **Lifecycle Events (boot hooks):**  
  - On **creating**: Fills `created_by_id` and `created_by_type`.  
  - On **updating**: Fills `updated_by_id` and `updated_by_type`.  
  - On **deleting**: Fills `deleted_by_id` and `deleted_by_type` before soft deleting.  

- **Pruning (auto-cleanup):**  
  - Implements `prunable()` method.  
  - Permanently deletes soft-deleted records older than 30 days.  
  - Can be scheduled in `Kernel.php` for daily cleanup.  

### `getUserData(): array`
- Retrieves authenticated user (`auth('api')->user()`).  
- Returns `{id, type}` pair for auditing.  

---


## HasGeneralScopes
- **Purpose**: Automatically applies global scopes depending on the model type.  
- **Behavior**:
  - For `User` and `Role` models → Adds `ActiveScope` only if `is_active` exists (skipped inside dashboard/admin requests).  
  - For all other models → Adds `GeneralScopes`.  
- **Use Case**: Ensures consistent filtering (active, language, etc.) without manually adding conditions.

---

## OwnedByUserLocalScopeTrait
- **Purpose**: Adds a local scope `scopeOwnedByUser` to filter records by the authenticated user or a specific user.  
- **Parameters**:
  - `$user` → can be `null`, a `User` model, or an integer ID.  
  - `$ownerKey` → the ownership column (default: `user_id`).  
- **Use Case**: Quickly restrict queries to user-owned records.

---

## ReportableTrait
- **Purpose**: Provides a dynamic way to generate aggregated reports with filters, joins, and grouping.  
- **Key Method**:
  - `generateReport($model, $filters, $type)` → Builds report queries based on `getReportConfig`.  
- **Common Reports**:
  - `by_active` → Group results by `is_active`.  
  - `by_date` → Group results by creation date.  
- **Use Case**: Simplifies reporting logic across multiple models.

---

## SafePropsTrait
- **Purpose**: Safely get/set static configuration properties at the class level.  
- **Methods**:
  - `getProp($name, $default)` → Retrieve static property safely.  
  - `setProp($name, $value)` → Assign a value if property exists.  
  - `staticProp($name)` → Alias for backward compatibility.  
- **Use Case**: Centralized config handling per model (e.g., export columns).

---

## MainRolesHandling
- **Purpose**: Handles protected main roles (e.g., SuperAdmin).  
- **Capabilities**:
  - Retrieve role names/IDs (`getMainRolesNames`, `getMainRolesIds`).  
  - Query scopes: `exceptMain`, `onlyTrashedExceptMain`, `exceptMainWithoutTrashed`.  
  - Secure finders: `findRoleExceptMain`, `findRoleExceptMainTrash`, `findRoleExceptMainWithoutTrash`.  
- **Use Case**: Prevents accidental modification or deletion of critical roles.

---

## MainUsersHandling
- **Purpose**: Manages protected main users linked to main roles.  
- **Capabilities**:
  - Retrieve protected user IDs (`getMainUsersIds`).  
  - Query scopes: `exceptMain`, `onlyTrashedExceptMain`, `exceptMainWithoutTrashed`.  
  - Secure finders: `findUserExceptMain`, `findUserExceptMainTrash`, `findUserExceptMainWithoutTrash`.  
- **Use Case**: Protects key system users from deletion or modification.

---

 These traits ensure **security, consistency, and reusability** across the entire project.

# Traits General
## Traits Controllers
This section provides a concise explanation of the controller-related traits in the project.

---

## 1. FilterFrontTrait
- **Purpose:** Extracts and prepares filters defined in models for frontend use (e.g., dropdowns, ranges).
- **Key Methods:**
  - `getModelFilters($model)`: Collects model filters, converts them via `toArray()`, and returns frontend-ready arrays (e.g., enums, min/max, labels).
  - `useFilter($filters)`: Shares filters with Inertia for rendering on frontend pages.

---

## 2. InertiaShareTrait
- **Purpose:** Bridges backend and frontend by sharing UI helpers, filters, and breadcrumbs through Inertia.
- **Key Methods:**
  - `prepareUiData($model, $data)`: Prepares filters, search, and breadcrumbs, then returns unified frontend or API data.
  - `getCreateUpdateData()`: Supplies default form data (e.g., enums for active status).
  - `renderWebIndexPage($view, $rows, $extra)`: Renders index/detail pages with form data, rows, and dynamic page titles.

---

## 3. SetsBreadcrumbsTrait
- **Purpose:** Dynamically generates and shares breadcrumb navigation for dashboard resources.
- **Key Methods:**
  - `setBreadcrumb($resource, $action, $modelOrId, $label)`: Builds breadcrumb trail depending on resource action (index, show, edit, create).
  - `breadcrumb($items)`: Shares breadcrumb with Inertia, including a fallback Home link.
  - `pageTitle($title)`: Shares a dynamic page title with Inertia.

---

## 4. UIHelpersTrait
- **Purpose:** Aggregates UI-related traits for convenience.
- **Includes:**
  - `FilterFrontTrait` → Handles filters.
  - `InertiaShareTrait` → Manages data sharing with Inertia.
  - `SetsBreadcrumbsTrait` → Handles breadcrumbs and page titles.

---

## 5. WebApiSuccessResponseTrait
- **Purpose:** Provides a unified response layer for both API (JSON) and Web (redirects with flash messages).
- **Key Methods:**
  - `respond($data, $resourceClass, $message, $redirectRoute)`: Auto-detects request type and responds accordingly.
  - `handleApi($data, $message, $resourceClass)`: Returns JSON success responses, with support for pagination, collections, single items, and filters.
  - `handleWeb($data, $message, $redirectRoute)`: Redirects with success status and flash messages.
  - `jsonSuccessResponse($data, $message)`: Standardized JSON response format with status, message, data, and optional meta (filters).
  - `normalizeData($data)`: Converts models/collections/arrays into plain arrays.

---

## 6. HandlesApiErrors
- Provides standardized error responses for APIs.
- Returns JSON in the format:
  ```json
  {
    "status": false,
    "message": "Validation failed.",
    "errors": {
      "email": ["The email field is required."],
      "password": ["The password must be at least 8 characters."]
    }
  }
  ```
- Method:
  - `errorResponse($message, $status = 500, $extra = [])`: Returns JSON with error details and HTTP status code.

---

## Summary
These traits streamline frontend-backend integration by:
- Standardizing filters for UI.
- Sharing common UI helpers (filters, breadcrumbs, titles).
- Centralizing success response logic for APIs and web redirects.


## Traits Services

This document provides a concise overview of the **Service Traits** used across the project.

---

## 1. AuthUserTrait
**Purpose:**  
Fetch the currently authenticated user from multiple guards (`admin-api`, `api`).

**Key Method:**  
- `getAuthUser()` → Returns the first authenticated user or `null`.

---

## 2. FilterTrait
**Purpose:**  
Reusable query filtering based on request input.

**Key Methods:**  
- `filter()` → Iterates over defined model filters, applies callbacks dynamically.  
- `getModelFilters()` (from model) → Returns frontend-ready filters (options, ranges, etc.).

---

## 3. HandlesBulkOperationsTrait
**Purpose:**  
Handles **bulk actions** (activate, deactivate, toggle, restore, soft delete, force delete) on Eloquent models.

**Key Methods:**  
- `handleBulkAction($request, $model, $action, $forUser = false)` → Process records in bulk, tracks processed, unchanged, failed, conflict, and not found IDs.  
- `fetchItemsByIdsOrAll($query, $isAll, $ids, $forUser = false)` → Retrieve items by IDs or fetch all with user/client ownership check.

---

## 4. HandlesServiceTransactions
**Purpose:**  
Automatically wraps specific service methods in **DB transactions**.

**Key Methods:**  
- `__call($method, $arguments)` → Intercepts `store`, `update`, `destroy`, `restore` and runs them inside `DB::transaction`.

---

## 5. OperationsActivationRestoringTrait
**Purpose:**  
Provides low-level helpers for **activation, deactivation, restore, delete** with conflict handling.

**Key Methods:**  
- `deactivateItem($item)` → Marks record as not active.  
- `activateItem($item, $model, $strategy)` → Activates record safely.  
- `restoreItem($item, $model, $strategy)` → Restores soft-deleted item safely.  
- `destroyItem($item)` → Soft deletes with unique field normalization.  
- `safeHandleConflictById($operation, $model, $item, $strategy)` → Handles conflicts using strategies: modify / replace / prevent.

---

## 6. SearchTrait
**Purpose:**  
Adds **dynamic search** to Eloquent queries.

**Features:**  
- Search by direct columns.  
- Nested relation search of any depth (`user.profile.username`).  
- Quick ID lookup using `#123`.  
- Logs searches to `Search` model.

**Key Methods:**  
- `search(array $columns, ?string $search_key = null)` → Applies flexible search logic.  
- `applyNestedRelationSearch($query, $path, $key)` → Recursively handles relation-based search.  
- `afterSearchLogging($search_key)` → Logs search query + result count.

---

## Builders

This section explains the purpose and main methods of the custom **Eloquent Builders** implemented in the project.

---

## BaseBuilder

**Namespace:** `App\Models\Builders\BaseBuilder`  
**Extends:** `Illuminate\Database\Eloquent\Builder`  
**Traits Used:** `HelpersModelTrait`, `SearchTrait`, `FilterTrait`  

###  Purpose
Provides a reusable foundation for all model builders, enhancing query logic with **search** and **filtering** capabilities.  
It centralizes the definition of common filters so they can be applied across multiple models.

###  Available Filters
- **ActiveFilter** → Filters by `is_active` field using the `isActive()` scope.
- **LangFilter** → Filters by `lang` field using the `lang()` scope.
- **CreatedAtDateRangeFilter** → Filters records by a date range on `created_at` using the `createdAtRange()` scope.

###  Key Method
```php
public function filters(): array
```
- Returns an array of filter objects.  
- Each filter contains a callback that applies query modifications.

---

##  OrderBuilder

**Namespace:** `App\Models\Builders\OrderBuilder`  
**Extends:** `BaseBuilder`  
**Mixin:** `Order`  

###  Purpose
A specialized query builder for the **Order** model.  
It inherits the filters from `BaseBuilder` and adds **order-specific filters**.

###  Additional Filter
- **OrderStatusFilter** → Filters by the `status` column.

###  Key Methods
```php
public function filters(): array
```
- Extends parent filters with `OrderStatusFilter`.

```php
public function status($value)
```
- Adds a `where status = value` condition when filtering orders.

---

##  Usage Example
```php
// Fetch active orders created in a date range with status = 'pending'
$orders = Order::query()
    ->filters()
    ->status('pending')
    ->get();
```


## Filters

This section describes the **custom filters** implemented in the project.  
Filters are used to dynamically apply query conditions in builders and repositories.  
They define how data can be filtered in APIs, dashboards, or UIs.

---

##  OrderStatusFilter

**Namespace:** `App\Models\Filters\Order\OrderStatusFilter`  
**Extends:** `Filter`  

- **Key:** `status`  
- **UI Type:** Dropdown (`FilterTypeEnum::DROPDOWN`)  
- **Data Source:** Uses `OrderStatusEnum::getOptionsData()` to populate status options.  
- **Customization:** Accepts an optional callback for custom query logic.  

---

##  ActiveFilter

**Namespace:** `App\Models\Filters\ActiveFilter`  
**Extends:** `Filter`  

- **Key:** `is_active`  
- **UI Type:** Dropdown (`FilterTypeEnum::DROPDOWN`)  
- **Placeholder:** `message.is_active_select`  
- **Data Source:** Uses `IsActiveEnum::getOptionsData()` for Active/Inactive options.  
- **Customization:** Optional callback to override query logic.  
- **Special:** Values are treated as integers (0/1).  

---

##  CreatedAtDateRangeFilter

**Namespace:** `App\Models\Filters\CreatedAtDateRangeFilter`  
**Extends:** `Filter`  

- **Key:** `created_at`  
- **UI Type:** Date Range (`FilterTypeEnum::DATE_RANGE`)  
- **Purpose:** Filters records created within a specific date range.  
- **Customization:** Optional callback to override query logic.  

---

##  LangFilter

**Namespace:** `App\Models\Filters\LangFilter`  
**Extends:** `Filter`  
**Trait Used:** `EnumOptionsTrait`  

- **Key:** `lang`  
- **UI Type:** Dropdown (`FilterTypeEnum::DROPDOWN`)  
- **Placeholder:** `message.lang_select`  
- **Data Source:** Loads supported languages from config and cache.  
- **Customization:** Accepts optional callback for advanced logic.  
- **Special:** Returns structured language options for dropdowns.  

---

##  Example Usage

```php
// Filtering Orders by status, language, and date range
$orders = Order::query()
    ->filters() // Loads defined filters
    ->status('pending')
    ->get();
```



## Scopes

This document explains the purpose and functionality of the **Scopes** in the project.

---

## 1. GeneralScopes
Global scope applied to models with the following responsibilities:
- **Skip for dashboard routes** (`dashboard/*`, `api/dashboard/*` with admin).
- **Guest users**: Automatically apply `ActiveScope` and `LanguageScope` if model has relevant columns.
- **Authenticated users**:
  - If model has `user_id`:
    - Show records owned by the user (no scopes).
    - Apply `ActiveScope` + `LanguageScope` to records not owned by the user.
  - If model has no `user_id`, apply general scopes (`is_active`, `lang`) when present.

**Logic Flow:**
1. Dashboard → No global scopes.
2. Guest → Apply `is_active` and `lang` filters.
3. Authenticated user → Show own records, filter others with scopes.

---

## 2. ActiveScope
Automatically adds:
```sql
WHERE is_active = true
```
Ensures only active records are retrieved across models that include this scope.

---

## 3. LanguageScope
Automatically adds:
```sql
WHERE lang = current_locale
```
Ensures records are filtered based on the application’s current language (via `localeLang()`).

---

## Usage Example
When a model uses these scopes, queries automatically restrict results:
- To active records only (`is_active = true`).
- To the current app language (`lang = locale()`).
- To user-owned records if applicable, otherwise apply global filters.

This mechanism enforces consistent multi-language and active-status filtering across the application.

# Translation System (Short Guide)

## Overview
Custom translation system for Laravel. Each translation is stored in the same table with:
- `lang`: record language
- `translate_id`: link to original record (NULL for default)

## Workflow
1. Default language record is created first.
2. Translations reference it via `translate_id`.
3. Queries auto-filter by current locale (`LanguageScope`).
4. Validation via `dynamicTranslationRules` + `UniqueTranslationValue`.

## Example Requests
**Store**
```json
{
  "lang": "ar",
  "title": "Main Title",
  "translations": [
    {"lang": "en", "title": "English Title"},
    {"lang": "fr", "title": "Titre Français"}
  ]
}
```

**Update**
```json
{
  "lang": "ar",
  "title": "Main Title",
  "translations": [
    {"translate_id": 1, "lang": "en", "title": "English Title"},
    {"translate_id": 1, "lang": "fr", "title": "Titre Français"}
  ]
}
```

## Service Methods
- `createTranslations()` → create translations linked to main record.
- `updateTranslations()` → update or create safely.
- `handleTranslations()` → auto-handle store/update.

## Model Setup
```php
public static $translationFields = ['title', 'description'];
public static $excludedFields = ['url'];
```
Use `TranslationRelationsTrait` for relations.

## Advantages
- Simple same-table design
- Automatic locale filtering
- Unique per-language validation
- Flexible: works with array or JSON
