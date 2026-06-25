# Dashboard Actions & Bulk Operations


### Item Store / Update ***UniqueActiveAndNotDeleted*** & Restore / Activate Conflict Handling 

* Names can be stored even if already exist, as long as the item is inactive or trashed , not not exist in original data table(items active or not in trash).
* Conflicts occur only on **restore** or **activate**.
* in `activate, restore` appeare a dialog to add in the req strategy , that will use it if exixt conflict when activate, restore
BUT , if i want dont appear this dialog it is normal -> will consider strategy: modify (default) 


## Conflict Strategy Dialog

* On restore or activate actions, a **dialog appears** allowing the user to select a **strategy** if a conflict exists.
* Dialog text example:

```
"If this item conflicts with other items in the table, do you want to modify its name, replace the existing item, or prevent the operation?"
```

  * `modify` → append `_restored` / `_activated`
  * `replace` → delete conflicting items
  * `prevent` → skip operation
* Default strategy: `modify`.

---

## Result on Frontend

* After the action, when the frontend renders the items:

  * If **Modify** was chosen → the item’s name is suffixed with `_activated` or `_restored` depending on the action.
  * If **Replace** → conflicting items are removed, and the current item remains.
  * If **Prevent** → no changes applied to the item.

---

✅ This ensures safe and predictable handling of unique constraints during restore and activate operations.



## Activation Request Fields

```php
action_activation: toggle (default) | activate | deactivate
strategy: modify (default) | replace | prevent
```

* `action_activation` → determines action type
* `strategy` → used only if conflict occurs

---

## Trash & Activation Methods

| Method                                  | Description                                    |
| --------------------------------------- | ---------------------------------------------- |
| `destroy` / `destroyMany`               | Soft delete item(s)                            |
| `restore` / `restoreMany`               | Restore trashed item(s) with conflict strategy |
| `forceDelete` / `forceDeleteMany`       | Permanently delete trashed item(s)             |
| `changeActivate` / `changeActivateMany` | Activate, deactivate, or toggle item(s)        |

---

## Bulk Operations Flow

1. Determine query: `onlyTrashed` -> ***restore, forcedelete*** or `withoutTrashed`-> ***destroy, activate***
```php
$query = in_array($action, [
            RestoringDeletingActionEnum::RESTORE->value, 
            RestoringDeletingActionEnum::FORCE_DELETE->value
        ]) 
            ? $model::onlyTrashed() // for restore/forceDelete
            : $model::withoutTrashed(); // other: activated, destroy

```
2. Fetch items via Fetch IDs from request (`all` or array) , use withoutGlobalScopes , to get items activae and not active

```php
$items = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds);
```
3. via type action `restore, activate, forcedelete, destroy` -> Apply operation per item: `restoreItem`, `activateItem`, `deactivateItem`, `destroyItem`
4. Handle conflicts using strategy

*Backend tracks:*

* `processed_ids`, `conflict_ids`, `unchanged_ids`, `not_found_ids`, `failed_ids`

---
***`restoreItem`***, ***`activateItem`*** => check if exist a conflict by using loop on uniquefields this model in fields this item
```php
// Check for conflicts on unique fields
$query->where(function ($q) use ($model, $item) {
    foreach ($model::getProp('uniqueFields') as $field) {
        $q->orWhere($field, $item->$field);
    }
});

$conflictExists = $query->where('id', '!=', $item->id)
                        ->exists(); // True if conflict exists

```
if exist conflict -> use strategy, after that will restore or activate:
  * `modify` → append `_restored` / `_activated` in all unique fields
  * `replace` → delete conflicting items
  * `prevent` → skip operation

***`deactivateItem`***, ***`destroyItem`*** => remove `_restored` / `_activated` from all unique fields



## Toggle Action

* If item inactive → activate (check conflict)
* If item active → deactivate (no conflict check)

---

## Queries

* `restore` / `forceDelete` → query only trashed items
* `destroy` → query main table only (no trash)

---

## 8. Dashboard Actions Flow

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

### single Item 
***changeActivate, restore, destroy, forceDelete***
use methods also: `restoreItem`, `activateItem`, `deactivateItem`, `destroyItem` for this item
---

## 9. Summary

* Handles bulk operations, activation/restoration, trash handling, and conflicts
* Ensures unique constraints are maintained
* Provides reusable traits and requests for validation
* Tracks success, conflicts, unchanged, not found, and failed items
