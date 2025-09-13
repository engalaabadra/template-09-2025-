# Item Conflict Handling in Backend

This document explains the backend logic for handling items with **unique constraints** during `restore, activate, deactivate, toggle, destroy, and force delete actions.` It also covers conflict strategies `(modify, replace, prevent)` , `bulk/single` operations.
and `uniqueness rules`
---

### Summary before going into details



## Current Behavior for Store and Update

* During **store** and **update** operations, an item's name can be saved as it exists in the database for a unique field, as long as the item is inactive or in the trash.
* Conflict occurs only when the item is **restored** or **activated**.
* A **dialog** appears on restore/activate operations allowing the user to select a **strategy** if a conflict exists:

```
"If this item conflicts with other items in the table, do you want to modify its name, replace the existing item, or prevent the operation?"
```


* Backend uses the selected strategy:

  * No conflict → restore/activate normally.
  * Conflict → use strategy from request (modify, replace, prevent).
  * Default strategy → modify.

* After action, frontend sees the item:

  * `modify` strategy → name is suffixed with `_activated` or `_restored`.

---

## Activation Request Fields

```php
action_activation: Optional, values: toggle (default), activate, deactivate
strategy: Optional, values: modify (default), replace, prevent
```

* `action_activation`: determines the action.
* `strategy`: used only if a conflict occurs during restore or activation.

### Frontend Considerations

* Toggle button may assume `modify` strategy by default.
* Some implementations show a dialog to select strategy if a conflict occurs.

---
-----------------------------


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

## 6. Enums Used

### ActivationActionEnum

* `ACTIVATE`
* `DEACTIVATE`
* `TOGGLE`

### ActivatedRestoredActionEnum

* `_ACTIVATED`
* `_RESTORED`

***Bulk Operation Enums***
### RestoringDeletingActionEnum

* `RESTORE`
* `DESTROY`
* `FORCE_DELETE`

### StrategyActionEnum

* `MODIFY`
* `REPLACE`
* `PREVENT`

---
---

## 4. Form Requests

### ActivationActionRequest

Validates optional activation requests:

* `action_activation`: toggle, activate, deactivate.
* `strategy`: 
 - modify → Appends `_restored` or `_activated` suffix.
 - replace → Deletes conflicting items.
 - prevent → Skips operation.

### RestoreActionRequest

Validates optional strategy for restoration:

* `strategy`:
 - modify → Appends `_restored` or `_activated` suffix.
 - replace → Deletes conflicting items.
 - prevent → Skips operation.

---

This structure allows safe, standardized, and reusable bulk operations while tracking conflicts, failures, and unchanged records, and ensuring proper validation and activation logic.

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
    'processed_ids' => [...],– successfully changed.
    'unchanged_ids' => [...],– already in desired state.
    'not_found_ids' => [...],– IDs not found.
    'conflict_ids' => [...],– skipped due to conflicts.
    'failed_ids' => [...], – failed due to exceptions.
]
```
---
***HandlesBulkOperationsTrait***
This file serves as a central reference for all **bulk operations, activation/restoration, trash handling, and related validations** in the project.

---------------------------------------------------


## Restore / Activation Conflict Handling in bulk data

**Step 1: Get IDs from request**

```php
$inputIds = $request->input('ids', []);
$isAll = $inputIds === 'all';
```

**Step 2: Determine query based on action**

```php
$query = in_array($action, [RestoringDeletingActionEnum::RESTORE->value, RestoringDeletingActionEnum::FORCE_DELETE->value])
    ? $model::onlyTrashed()
    : $model::withoutTrashed();
```

**Step 3: Fetch data**

```php
if ($isAll) {
    $items = $query->withoutGlobalScopes()->get();
} else {
    $items = $query->withoutGlobalScopes()->whereIn('id', $inputIds)->get();
}
```

**Step 4: Call the proper method depending on action**

* `restoreItem($item)` → handles restore and checks for conflicts.
* `activateItem($item, $strategy)` → handles activation and conflict.

---

### Conflict Detection

* For restore or activation, get **all active items in the original table** except the current item.
* Check **unique fields** for conflicts:

```php
$query->where(function ($q) use ($model, $item) {
    foreach ($model::getProp('uniqueFields') as $field) {
        $q->orWhere($field, $item->$field);
    }
});
```

**If no conflict:**

```php
$item->restore(); // for restore
$item->update(['is_active' => IsActiveEnum::ACTIVE->value]); // for activate
```

**If conflict: three strategies**

1. **Modify**

```php
foreach ($item::getProp('uniqueFields') as $field) {
    $suffix = $operation === 'restore' ? '_restored' : '_activated';
    if (!preg_match('/(_\d+)?'.$suffix.'$/', $item->$field)) {
        $item->$field .= '_' . $item->id . $suffix;
    }
}
$item->save();
```

2. **Replace** → Delete conflicting items and insert the current one.

3. **Prevent** → Do not perform restore or activate.

**Resulting lists:**

* `processedIds` → items processed without conflict
* `conflictIds` → items with conflict
* `unchangedIds` → items already restored/activated or no changes needed

---

### Toggle Action

* If item is `inactive` → call `activateItem` (conflict checked)
* If item is `active` → call `deactivateItem` (no conflict check)

---

### Delete Actions

1. **Force Delete** → remove item permanently; check if in trash first.
2. **Destroy** → soft delete non-trashed items; if already trashed → mark `unchangedIds`.

---

### Single Item Actions

* Same rules apply for single actions like `changeActivate`.
* Backend calls appropriate methods:

```php
switch ($action) {
    case ActivationActionEnum::ACTIVATE->value:
        $this->activateItem($item, $model, $strategy);
        break;
    case ActivationActionEnum::DEACTIVATE->value:
        $this->deactivateItem($item);
        break;
    case ActivationActionEnum::TOGGLE->value:
        if ($item->is_active->value == IsActiveEnum::ACTIVE->value) {
            $this->deactivateItem($item);
        } else {
            $this->activateItem($item, $model, $strategy);
        }
        break;
}
```

* Restore and changeActivate logic uses the same principle.

---

## Notes on Queries

* `restore` / `forceDelete` → query items **only in trash**
* `destroy` → query items **only in the main table** (without trash)

---
# Dashboard Actions Flow

This explains the backend flow for **dashboard user actions** such as `activate, deactivate, toggle, restore, and delete`, including conflict handling strategies.

---

## Diagram

```
                 ┌─────────────────────────┐
                 │   Dashboard Actions     │
                 │  (activate/deactivate/  │
                 │   toggle/restore/delete)│
                 └──────────┬─────────────┘
                            │
                   Check Action Type
───────────────────────────────────────────────
        Activate / Restore / Toggle / Delete
───────────────────────────────────────────────
           ┌───────────────┐
           │ Item in Trash?│
           └───────┬───────┘
                   │
          Yes ───> Check for Conflicts
          No ────> Soft Delete / Deactivate / Skip
───────────────────────────────────────────────
    Conflict Exists?
───────────────────────────────────────────────
           ┌───────────────┐
           │  Apply Strategy│
           └───────┬───────┘
                   │
      ┌────────────┼────────────┐
      │            │            │
   Modify        Replace      Prevent
      │            │            │
 Append Suffix  Delete Conf.  Do Nothing
 & Perform     Items & Perf
 Operation     Operation
```

---

## Explanation of Dashboard Actions Flow

1. **Dashboard Actions:**

   * Represents all user-related operations in the dashboard.
   * Includes **Activate, Deactivate, Toggle, Restore, Delete**.

2. **Check Action Type:**

   * Determines the type of action being performed.

3. **Item in Trash?:**

   * **Yes:** Applicable for Restore or Force Delete.
   * **No:** Applicable for Activate, Deactivate, Destroy.

4. **Check for Conflicts:**

   * For actions that may violate **unique constraints**, check if conflicts exist with other active items.

5. **Conflict Exists?:**

   * If a conflict is detected, apply a **strategy**.

6. **Apply Strategy:**

   * **Modify:** Append a suffix to the item's unique field (e.g., `_restored` or `_activated`) and perform the operation.
   * **Replace:** Delete the conflicting items and perform the operation on the current item.
   * **Prevent:** Do not perform the operation; leave the item unchanged.

7. **Soft Delete / Deactivate / Skip:**

   * If no conflict exists and the item is not in the trash:

     * Soft delete for destroy actions.
     * Deactivate for toggle/deactivate actions.
     * Skip if no operation is needed.

---
