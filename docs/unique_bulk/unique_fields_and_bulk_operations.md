# Laravel Bulk Operations & Validation Documentation

This documentation summarizes the bulk actions, validation requests, enums, and helper traits used in the Laravel project for **activation, restoration, deletion, and uniqueness rules**.

---

## 1. Custom Validation Rules

### UniqueActiveAndNotDeleted Rule

Validates that a field is **unique among active and non-deleted records**.

```php
use App\Rules\UniqueActiveAndNotDeleted;

$rules = [
    'title' => [
        'required',
        new UniqueActiveAndNotDeleted('banners', 'title', $bannerId),
        new SmallTextRule()
    ],
];
```

* Ignores soft-deleted records (`deleted_at`).
* Checks `is_active` column by default.
* Supports ignoring a record (for updates).

---

## 2. Trash Handling Methods

| Method                              | Description                                                                     |
| ----------------------------------- | ------------------------------------------------------------------------------- |
| `destroy($id, $model)`              | Soft delete a single record or permanently if model has no `SoftDeletes`.       |
| `destroyMany($request, $model)`     | Bulk soft delete items using `handleBulkAction`.                                |
| `restore($request, $id, $model)`    | Restore a soft-deleted item, using a strategy (`modify`, `replace`, `prevent`). |
| `restoreMany($request, $model)`     | Restore multiple items in bulk.                                                 |
| `forceDelete($id, $model)`          | Permanently delete a soft-deleted item.                                         |
| `forceDeleteMany($request, $model)` | Permanently delete multiple trashed items.                                      |

---

## 3. Activation Methods

| Method                                  | Description                                                           |
| --------------------------------------- | --------------------------------------------------------------------- |
| `changeActivate($request, $id, $model)` | Activate, deactivate, or toggle a single record's `is_active` status. |
| `changeActivateMany($request, $model)`  | Apply activation changes to multiple records in bulk.                 |

### ActivationActionEnum

```php
enum ActivationActionEnum: string {
    case ACTIVATE = 'activate';
    case DEACTIVATE = 'deactivate';
    case TOGGLE = 'toggle';
}
```

### ActivatedRestoredActionEnum

```php
enum ActivatedRestoredActionEnum: string {
    case _ACTIVATED = '_activated';
    case _RESTORED = '_restored';
}
```

---

## 4. Bulk Operation Enums

| Enum                          | Values                          |
| ----------------------------- | ------------------------------- |
| `RestoringDeletingActionEnum` | RESTORE, DESTROY, FORCE\_DELETE |
| `StrategyActionEnum`          | MODIFY, REPLACE, PREVENT        |

---

## 5. Form Requests

### ActivationActionRequest

Validates optional activation requests:

* `action_activation`: toggle, activate, deactivate.
* `strategy`: modify, replace, prevent.

### RestoreActionRequest

Validates optional strategy for restoration:

* `strategy`: modify, replace, prevent.

### Bulk Restore / Activation Requests

* Validate `ids` field as either "all" or array of integers.
* Optionally validate `action_activation` and `strategy` fields.
* Extends `BaseBulkActionRequest`.

### BaseBulkActionRequest

Validates only the `ids` field:

* Must be present.
* Must pass `IdsRule`.

---

## 6. HandlesBulkOperationsTrait

Provides reusable methods for handling bulk actions:

* `deactivateItem($item)` - deactivate safely.
* `activateItem($item, $model, $strategy)` - activate safely with conflict handling.
* `restoreItem($item, $model, $strategy)` - restore safely with conflict handling.
* `destroyItem($item)` - soft delete item.
* `handleBulkAction($request, $model, $action, $failOnNotFound = true)` - generic bulk handler.
* `fetchItemsByIdsOrAll($query, $isAll, $inputIds)` - fetches items for bulk operations.
* `safeHandleConflictById($operation, $model, $item, $strategy)` - resolves conflicts for unique fields.

### Bulk Action Tracking

Returns:

```php
[
    'processed_ids' => [...],
    'unchanged_ids' => [...],
    'not_found_ids' => [...],
    'conflict_ids' => [...],
    'failed_ids' => [...],
]
```

* `processed_ids` – successfully changed.
* `unchanged_ids` – already in desired state.
* `not_found_ids` – IDs not found.
* `conflict_ids` – skipped due to conflicts.
* `failed_ids` – failed due to exceptions.

---

This file serves as a central reference for all **bulk operations, activation/restoration, trash handling, and related validations** in the project.

---------------------------------------------------


# Laravel Bulk Operations & Activation/Trash Management

This README provides an overview of handling bulk actions, activation, deactivation, soft deletion, force deletion, and restoration in a Laravel project. The codebase uses traits, enums, and custom validation rules to standardize and safely perform these operations.

---

## Table of Contents

1. [Validation Rules](#validation-rules)
2. [Trash Methods](#trash-methods)
3. [Activation Methods](#activation-methods)
4. [Enums](#enums)
5. [Bulk Operations Trait](#bulk-operations-trait)

---

## Validation Rules

### UniqueActiveAndNotDeleted Rule

Ensures a field is unique among **active and not-deleted** records.

```php
new UniqueActiveAndNotDeleted('banners', 'title', $bannerId);
```

* Uses `passes()` to validate.
* Supports ignoring a specific record ID (useful for updates).
* Handles only active and non-deleted rows.
* Provides a default error message when uniqueness fails.

---

### Form Requests for Actions

#### ActivationActionRequest

Validates optional fields for single activation/deactivation/toggle actions.

```php
'action_activation' => ['nullable', Rule::in(array_column(ActivationActionEnum::cases(), 'value'))],
'sategy' => ['nullable', Rule::in(array_column(StrategyActionEnum::cases(), 'value'))],
```

#### RestoreActionRequest

Validates optional `strategy` field for restoring actions.

#### BulkRestoreActionRequest & BulkActivationActionRequest

* Validate `ids` field (array of integers or string `all`).
* Validate optional `strategy`.
* Validate optional `action_activation` for bulk activation.

#### BaseBulkActionRequest

* Ensures `ids` field is present.
* Accepts array of integers or string `all`.

---

## Trash Methods

Used for managing soft deletion and permanent deletion of records.

### destroy(\$id, \$model)

* Soft deletes or permanently deletes a single item.
* Eager loads relations if defined.

### destroyMany(\$request, \$model)

* Handles bulk soft deletions.

### restore(\$request, \$id, \$model)

* Restores a soft-deleted record.
* Accepts a `strategy`: `modify`, `replace`, `prevent`.

### restoreMany(\$request, \$model)

* Restores multiple trashed records in bulk.

### forceDelete(\$id, \$model)

* Permanently deletes a single trashed record.

### forceDeleteMany(\$request, \$model)

* Permanently deletes multiple trashed records.

---

## Activation Methods

### changeActivate(\$request, \$id, \$model)

* Toggles, activates, or deactivates a record.
* Supports `strategy` for conflict handling.
* Returns updated model with eager-loaded relations if defined.

### changeActivateMany(\$request, \$model)

* Bulk activates, deactivates, or toggles multiple records.

---

## Enums

### ActivationActionEnum

```php
ACTIVATE, DEACTIVATE, TOGGLE
```

* Avoids magic strings.
* Ensures type safety.

### ActivatedRestoredActionEnum

```php
_ACTIVATED, _RESTORED
```

* Appended to unique fields during conflict handling.

### RestoringDeletingActionEnum

```php
RESTORE, DESTROY, FORCE_DELETE
```

* Represents bulk or single record actions.

### StrategyActionEnum

```php
MODIFY, REPLACE, PREVENT
```

* Defines conflict handling strategies.

---

## HandlesBulkOperationsTrait

Provides reusable methods for handling bulk operations:

### Methods

* `deactivateItem($item)` → Sets `is_active` to `NOT_ACTIVE`.
* `activateItem($item, $model, $strategy)` → Activates safely with conflict handling.
* `restoreItem($item, $model, $strategy)` → Restores safely with conflict handling.
* `destroyItem($item)` → Soft deletes an item.
* `handleBulkAction($request, $model, $action, $failOnNotFound = true)` → Handles bulk operations and tracks:

  * `processed_ids`
  * `unchanged_ids`
  * `conflict_ids`
  * `failed_ids`
  * `not_found_ids`
* `fetchItemsByIdsOrAll($query, $isAll, $inputIds)` → Helper to fetch all or specific IDs.
* `safeHandleConflictById($operation, $model, $item, $strategy)` → Resolves conflicts based on strategy.

### Conflict Strategies

* `modify` → Appends `_restored` or `_activated` suffix.
* `replace` → Deletes conflicting items.
* `prevent` → Skips operation.

---

This structure allows safe, standardized, and reusable bulk operations while tracking conflicts, failures, and unchanged records, and ensuring proper validation and activation logic.


---------------------------------------------

# Laravel Bulk Operations, Activation, and Trash Handling

This document explains the structure, usage, and purpose of a set of Laravel traits, enums, and request validations used for handling bulk operations, soft deletes, restoration, and activation/deactivation of models.

---

## 1. Custom Validation Rules

### `UniqueActiveAndNotDeleted`

* Ensures a column value is unique **only among active and not-deleted records**.
* Can ignore a record (useful for updates).
* Usage in FormRequest:

```php
'title' => [
    'required',
    new UniqueActiveAndNotDeleted('banners', 'title', $bannerId),
    new SmallTextRule()
]
```

---

## 2. Trash Handling Methods

### Single Item Operations

* `destroy($id, $model)` : Soft deletes or permanently deletes a single record.
* `restore($request, $id, $model)` : Restores a trashed record with a strategy (`modify`, `replace`, `prevent`).
* `forceDelete($id, $model)` : Permanently deletes a trashed item.

### Bulk Operations

* `destroyMany($request, $model)` : Bulk soft deletion.
* `restoreMany($request, $model)` : Bulk restoration.
* `forceDeleteMany($request, $model)` : Bulk permanent deletion.

> All bulk operations are handled through the `handleBulkAction` method in the `HandlesBulkOperationsTrait`.

---

## 3. Activation Methods

* `changeActivate($request, $id, $model)` : Toggle, activate, or deactivate a single record.
* `changeActivateMany($request, $model)` : Bulk activation actions.

### Enum: `ActivationActionEnum`

```php
enum ActivationActionEnum: string {
    case ACTIVATE = 'activate';
    case DEACTIVATE = 'deactivate';
    case TOGGLE = 'toggle';
}
```

### Enum: `ActivatedRestoredActionEnum`

* Used as suffixes for unique fields when restoring or activating a record.

```php
enum ActivatedRestoredActionEnum: string {
    case _ACTIVATED = '_activated';
    case _RESTORED = '_restored';
}
```

---

## 4. Bulk Operations Trait

`HandlesBulkOperationsTrait` provides reusable methods to handle:

* `restore`, `destroy`, `forceDelete`
* `activate`, `deactivate`, `toggle`

### Tracking arrays:

* `processed_ids` : Successfully changed
* `unchanged_ids` : Already in desired state
* `conflict_ids` : Skipped due to conflicts
* `failed_ids` : Failed due to exceptions
* `not_found_ids` : Requested IDs not found in DB

### Example Usage:

```php
$result = $this->handleBulkAction($request, User::class, ActivationActionEnum::TOGGLE->value);
```

### Conflict Handling Strategies

* `modify` : Append suffix to unique fields.
* `replace`: Delete conflicting items.
* `prevent`: Skip operation.

---

## 5. Form Requests

### `ActivationActionRequest`

Validates single activation request:

* `action_activation`: `activate`, `deactivate`, `toggle`
* `strategy`: `modify`, `replace`, `prevent`

### `RestoreActionRequest`

Validates single restore request:

* `strategy`: `modify`, `replace`, `prevent`

### `BaseBulkActionRequest`

Validates bulk operations:

* `ids`: Either "all" or a non-empty array of integers.

### `BulkRestoreActionRequest`

Extends `BaseBulkActionRequest`:

* Adds `strategy` field.

### `BulkActivationActionRequest`

Extends `BaseBulkActionRequest`:

* Adds `action_activation` and `strategy` fields.

---

## 6. Enums for Bulk Actions

### `RestoringDeletingActionEnum`

```php
enum RestoringDeletingActionEnum: string {
    case RESTORE = 'restore';
    case DESTROY = 'destroy';
    case FORCE_DELETE = 'force_delete';
}
```

### `StrategyActionEnum`

```php
enum StrategyActionEnum: string {
    case MODIFY = 'modify';
    case REPLACE = 'replace';
    case PREVENT = 'prevent';
}
```

---

## 7. Summary

This setup provides a consistent and safe way to:

* Handle soft deletes and permanent deletions
* Restore trashed records safely
* Activate, deactivate, or toggle activation
* Perform bulk operations with conflict resolution
* Validate both single and bulk actions through dedicated FormRequests

The `HandlesBulkOperationsTrait` centralizes logic to reduce duplication and ensures a standardized approach for all models.

----------------------------------


# Laravel Bulk Operations & Validation

This document serves as a reference for handling bulk actions, activation, deletion, restoration, and validation in a Laravel project. It covers traits, enums, form requests, and custom validation rules.

---

## 1. UniqueActiveAndNotDeleted Rule

Custom validation rule to ensure a value is unique only among active and not-deleted records.

**Usage:**

```php
use App\Rules\UniqueActiveAndNotDeleted;

$request->validate([
    'title' => ['required', new UniqueActiveAndNotDeleted('banners', 'title', $bannerId)],
]);
```

**Key Points:**

* Can ignore a specific ID (useful for updates).
* Checks only active and non-deleted records.
* Returns a custom error message when validation fails.

---

## 2. Trash Management Methods

### `destroy($id, $model)`

Soft deletes an item or permanently deletes it if the model doesn't use SoftDeletes.

### `destroyMany($request, $model)`

Handles bulk deletion via `handleBulkAction`.

### `restore($request, $id, $model)`

Restores a soft-deleted record based on a specified strategy (`modify`, `replace`, `prevent`).

### `restoreMany($request, $model)`

Restores multiple trashed records using bulk operations.

### `forceDelete($id, $model)`

Permanently deletes a single trashed item.

### `forceDeleteMany($request, $model)`

Permanently deletes multiple trashed items using bulk operations.

---

## 3. Activation Methods

### `changeActivate($request, $id, $model)`

Toggles the activation status of a record (`activate`, `deactivate`, `toggle`) with optional strategy handling.

### `changeActivateMany($request, $model)`

Applies activation changes to multiple records using bulk operations.

**Enums Used:**

* `ActivationActionEnum`: `ACTIVATE`, `DEACTIVATE`, `TOGGLE`
* `StrategyActionEnum`: `MODIFY`, `REPLACE`, `PREVENT`

---

## 4. Bulk Action Handling Trait: `HandlesBulkOperationsTrait`

Provides reusable methods for bulk operations:

* Activate / Deactivate / Toggle
* Restore / Destroy / Force Delete

### Key Methods

* `handleBulkAction($request, $model, string $action, bool $failOnNotFound = true)`
  Handles all bulk actions, returns arrays:

  * `processed_ids`
  * `unchanged_ids`
  * `conflict_ids`
  * `failed_ids`
  * `not_found_ids`

* `fetchItemsByIdsOrAll($query, bool $isAll, $inputIds)`
  Fetches items by IDs or all items.

* `safeHandleConflictById($operation, $model, $item, $strategy)`
  Handles conflicts for restore/activate operations using specified strategies.

* `deactivateItem($item)`
  Deactivates a record safely.

* `activateItem($item, $model, $strategy)`
  Activates a record with conflict handling.

* `restoreItem($item, $model, $strategy)`
  Restores a record with conflict handling.

* `destroyItem($item)`
  Soft deletes a record and normalizes unique fields.

---

## 5. Form Requests

### 5.1 ActivationActionRequest

Validates activation actions:

```php
'action_activation' => ['nullable', Rule::in(array_column(ActivationActionEnum::cases(), 'value'))],
'strategy' => ['nullable', Rule::in(array_column(StrategyActionEnum::cases(), 'value'))],
```

### 5.2 RestoreActionRequest

Validates restore strategies:

```php
'strategy' => ['nullable', Rule::in(array_column(StrategyActionEnum::cases(), 'value'))],
```

### 5.3 BulkRestoreActionRequest

Validates bulk restore requests, extends `BaseBulkActionRequest`.

### 5.4 BulkActivationActionRequest

Validates bulk activation requests, extends `BaseBulkActionRequest`.

### 5.5 BaseBulkActionRequest

Validates bulk action IDs (either `'all'` or an array of integers) using `IdsRule`.

---

## 6. Enums Used

### ActivationActionEnum

* `ACTIVATE`
* `DEACTIVATE`
* `TOGGLE`

### ActivatedRestoredActionEnum

* `_ACTIVATED`
* `_RESTORED`

### RestoringDeletingActionEnum

* `RESTORE`
* `DESTROY`
* `FORCE_DELETE`

### StrategyActionEnum

* `MODIFY`
* `REPLACE`
* `PREVENT`

---

This setup allows for clean, reusable, and type-safe handling of bulk operations, activation, deletion, and restoration within Laravel projects.

----------------------------------


# Laravel Bulk Operations & Validation

This document serves as a reference for handling bulk actions, activation, deletion, restoration, and validation in a Laravel project. It covers traits, enums, form requests, and custom validation rules.

---

## 1. UniqueActiveAndNotDeleted Rule

Custom validation to ensure a value is unique among active and not deleted records.

```php
use App\Rules\UniqueActiveAndNotDeleted;

'banner_title' => [
    'required',
    new UniqueActiveAndNotDeleted('banners', 'title', $bannerId),
    new SmallTextRule()
];
```

Key points:

* Ignores soft-deleted records.
* Checks only active records.
* Allows ignoring a specific ID for updates.

---

## 2. Trash & Restore Methods

### Single Item

```php
protected function destroy($id, $model) // soft delete or permanent
protected function restore($request, $id, $model) // restore trashed record
```

### Bulk Items

```php
protected function destroyMany($request, $model)
protected function restoreMany($request, $model)
protected function forceDeleteMany($request, $model)
```

* Uses `handleBulkAction` internally.
* Tracks arrays: `processed_ids`, `unchanged_ids`, `not_found_ids`, `conflict_ids`, `failed_ids`.

---

## 3. Activation Methods

### Single Item

```php
public function changeActivate($request, $id, $model)
```

* Actions: `activate`, `deactivate`, `toggle`
* Strategy: `modify`, `replace`, `prevent`

### Bulk Items

```php
public function changeActivateMany($request, $model)
```

* Uses `handleBulkAction`
* Tracks the same result arrays as bulk trash operations.

---

## 4. Enums

```php
enum ActivationActionEnum: string { ACTIVATE, DEACTIVATE, TOGGLE }
enum ActivatedRestoredActionEnum: string { _ACTIVATED, _RESTORED }
enum RestoringDeletingActionEnum: string { RESTORE, DESTROY, FORCE_DELETE }
enum StrategyActionEnum: string { MODIFY, REPLACE, PREVENT }
enum IsActiveEnum: string { ACTIVE, NOT_ACTIVE }
```

Purpose:

* Avoid magic strings.
* Ensure type safety.
* Improve readability.

---

## 5. Form Requests

### ActivationActionRequest

* Validates optional fields:

  * `action_activation`: 'activate', 'deactivate', 'toggle'
  * `strategy`: 'modify', 'replace', 'prevent'

### RestoreActionRequest

* Validates `strategy` only.

### Bulk Requests

* Extend `BaseBulkActionRequest`
* `ids` must be array of integers or string "all".
* Optional `action_activation` and `strategy` depending on request type.

---

## 6. HandlesBulkOperationsTrait

Provides reusable methods:

* `deactivateItem($item)`
* `activateItem($item, $model, $strategy)`
* `restoreItem($item, $model, $strategy)`
* `destroyItem($item)`
* `handleBulkAction($request, $model, $action)`

Features:

* Handles conflicts according to strategy (`modify`, `replace`, `prevent`).
* Uses transactions for safe updates.
* Returns detailed arrays of processed, unchanged, conflicts, failures, and not found IDs.

Example:

```php
$result = $this->handleBulkAction($request, User::class, ActivationActionEnum::TOGGLE->value);
```

---

This setup ensures:

* Safe activation/deactivation.
* Safe restore and permanent delete operations.
* Bulk operations with detailed reporting.
* Consistent validation using enums and form requests.

--------------------------------------

# Laravel Bulk Operations & Validation

This document serves as a reference for handling bulk actions, activation, deletion, restoration, and validation in a Laravel project. It covers traits, enums, form requests, and custom validation rules.

---

## 1. UniqueActiveAndNotDeleted Rule

Custom validation to ensure a value is unique among active and not deleted records.

```php
use App\Rules\UniqueActiveAndNotDeleted;

'banner_title' => [
    'required',
    new UniqueActiveAndNotDeleted('banners', 'title', $bannerId),
    new SmallTextRule()
];
```

Key points:

* Ignores soft-deleted records.
* Checks only active records.
* Allows ignoring a specific ID for updates.

---

## 2. Trash & Restore Methods

### Single Item

```php
protected function destroy($id, $model) // soft delete or permanent
protected function restore($request, $id, $model) // restore trashed record
```

### Bulk Items

```php
protected function destroyMany($request, $model)
protected function restoreMany($request, $model)
protected function forceDeleteMany($request, $model)
```

* Uses `handleBulkAction` internally.
* Tracks arrays: `processed_ids`, `unchanged_ids`, `not_found_ids`, `conflict_ids`, `failed_ids`.

---

## 3. Activation Methods

### Single Item

```php
public function changeActivate($request, $id, $model)
```

* Actions: `activate`, `deactivate`, `toggle`
* Strategy: `modify`, `replace`, `prevent`

### Bulk Items

```php
public function changeActivateMany($request, $model)
```

* Uses `handleBulkAction`
* Tracks the same result arrays as bulk trash operations.

---

## 4. Enums

```php
enum ActivationActionEnum: string { ACTIVATE, DEACTIVATE, TOGGLE }
enum ActivatedRestoredActionEnum: string { _ACTIVATED, _RESTORED }
enum RestoringDeletingActionEnum: string { RESTORE, DESTROY, FORCE_DELETE }
enum StrategyActionEnum: string { MODIFY, REPLACE, PREVENT }
enum IsActiveEnum: string { ACTIVE, NOT_ACTIVE }
```

Purpose:

* Avoid magic strings.
* Ensure type safety.
* Improve readability.

---

## 5. Form Requests

### ActivationActionRequest

* Validates optional fields:

  * `action_activation`: 'activate', 'deactivate', 'toggle'
  * `strategy`: 'modify', 'replace', 'prevent'

### RestoreActionRequest

* Validates `strategy` only.

### Bulk Requests

* Extend `BaseBulkActionRequest`
* `ids` must be array of integers or string "all".
* Optional `action_activation` and `strategy` depending on request type.

---

## 6. HandlesBulkOperationsTrait

Provides reusable methods:

* `deactivateItem($item)`
* `activateItem($item, $model, $strategy)`
* `restoreItem($item, $model, $strategy)`
* `destroyItem($item)`
* `handleBulkAction($request, $model, $action)`

Features:

* Handles conflicts according to strategy (`modify`, `replace`, `prevent`).
* Uses transactions for safe updates.
* Returns detailed arrays of processed, unchanged, conflicts, failures, and not found IDs.

Example:

```php
$result = $this->handleBulkAction($request, User::class, ActivationActionEnum::TOGGLE->value);
```

---

This setup ensures:

* Safe activation/deactivation.
* Safe restore and permanent delete operations.
* Bulk operations with detailed reporting.
* Consistent validation using enums and form requests.

--------------------------------

