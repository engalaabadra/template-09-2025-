
# OperationsActivationRestoringTrait

Trait providing helper methods for safe activation, deactivation, restoring, and soft deletion of models, including conflict handling strategies.

**Features:**

* `deactivateItem($item)`: Deactivates a model by setting `is_active` to NOT\_ACTIVE.
* `activateItem($item, $model, $strategy)`: Activates a model safely, handling conflicts.
* `restoreItem($item, $model, $strategy)`: Restores a soft-deleted model safely, handling conflicts.
* `destroyItem($item)`: Soft deletes a model.
* `safeHandleConflictById($operation, $model, $item, $strategy)`: Handles conflicts for restore or activate operations with strategies `modify`, `replace`, or `prevent`.

**Example usage:**


```php
case ActivationActionEnum::ACTIVATE->value: // from not_active to active
    if ($item->is_active->value !== IsActiveEnum::ACTIVE->value) { // if not active -> will be activate , elseif active -> not changed(because it already active , and here activate)
        $this->activateItem($item, $model, $strategy);
    }
    break;

```