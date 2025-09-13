<?php
namespace App\Traits\Services;

use App\Enums\IsActiveEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\ActivationActionEnum;
use App\Enums\RestoringDeletingActionEnum;

trait OperationsActivationRestoringTrait{
    /**
     * Deactivate a model (set is_active = NOT_ACTIVE).
     */
    protected function deactivateItem($item): void
    {
        DB::transaction(function () use ($item) {
            foreach ($item::getProp('uniqueFields') as $field) {
                //Active → deactivate (remove suffix -> "_1_activated" if exists)
                $idFromValue = extractIdFromValue($item->$field);
                if ($idFromValue === $item->id) {
                    $item->$field = normalizeActivatedField($item->$field);
                }
            }

            $item->is_active = IsActiveEnum::NOT_ACTIVE->value;
            $item->save();
        });
    }

    /**
     * Activate an item safely (handles conflicts).
     */
    protected function activateItem($item, $model, $strategy): bool
    {
        return $this->safeHandleConflictById(ActivationActionEnum::ACTIVATE->value, $model, $item, $strategy);
    }

    /**
     * Restore a soft-deleted item safely (handles conflicts).
     */
    protected function restoreItem($item, $model, $strategy): bool
    {
        return $this->safeHandleConflictById(RestoringDeletingActionEnum::RESTORE->value, $model, $item, $strategy);
    }

    /**
     * Soft delete an item (mark as deleted).
     */
    protected function destroyItem($item): void
    {
        DB::transaction(function () use ($item) {
            foreach ($item::getProp('uniqueFields') as $field) {
                //remove suffix -> "_1_restored" if exists
                $idFromValue = extractIdFromValue($item->$field);
                if ($idFromValue === $item->id) {
                    $item->$field = normalizeRestoredField($item->$field);
                }
            }
            $item->save();

            $item->delete(); // temporary delete
        });
    }

      /**
     * Safely handle conflicts during restore or activate operations.
     *
     * Strategies:
     * - 'modify' : append suffix to unique fields
     * - 'replace': delete conflicting items
     * - 'prevent': skip operation
     *
     * Example:
     * ```php
     * $success = $this->safeHandleConflictById(RestoringDeletingActionEnum::RESTORE->value, User::class, $user, 'modify');
     * ```
     *
     * @param string $operation
     * @param string|object $model
     * @param object $item
     * @param string $strategy
     * @return bool True if operation applied successfully, false if skipped
     */
    protected function safeHandleConflictById(string $operation, $model, $item, string $strategy = 'modify'): bool
    {
        $query = $model::query();
        if ($operation === RestoringDeletingActionEnum::RESTORE->value) $query->whereNull('deleted_at'); // Only check non-deleted

        // Check for conflicts on unique fields
        $query->where(function ($q) use ($model, $item) {
            foreach ($model::getProp('uniqueFields') as $field) {
                $q->orWhere($field, $item->$field);
            }
        });

        $conflictExists = $query->where('id', '!=', $item->id)
                                ->exists(); // True if conflict exists

        if ($conflictExists) {
            switch ($strategy) {
                case 'modify':
                    return DB::transaction(function () use ($item, $operation) {
                        foreach ($item::getProp('uniqueFields') as $field) {
                            $suffix = $operation === RestoringDeletingActionEnum::RESTORE->value ? ActivatedRestoredActionEnum::_RESTORED->value : ActivatedRestoredActionEnum::_ACTIVATED->value;
                            // Append suffix
                            if (!preg_match('/(_\d+)?'. $suffix .'$/', $item->$field)) {
                                    $item->$field .= '_' . $item->id . $suffix;
                                }
                        }
                        $item->save();
                        if ($operation === RestoringDeletingActionEnum::RESTORE->value) $item->restore();
                        if ($operation === ActivationActionEnum::ACTIVATE->value) $item->update(['is_active' => IsActiveEnum::ACTIVE->value]);
                        return false; // Conflict handled
                    });

                case 'replace':
                    return DB::transaction(function () use ($query, $item, $operation) {
                        $query->delete(); // Delete conflicting items
                        if ($operation === RestoringDeletingActionEnum::RESTORE->value) $item->restore();
                        if ($operation === ActivationActionEnum::ACTIVATE->value) $item->update(['is_active' => IsActiveEnum::ACTIVE->value]);
                        return false; // Conflict replaced
                    });

                case 'prevent':
                default:
                    return false; // Skip operation
            }
        } else {
            // No conflict → perform normally
            if ($operation === RestoringDeletingActionEnum::RESTORE->value) $item->restore();
            if ($operation === ActivationActionEnum::ACTIVATE->value) $item->update(['is_active' => IsActiveEnum::ACTIVE->value]);
            return true;
        }
    }
}