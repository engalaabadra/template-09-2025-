<?php
namespace App\Traits\Services;

use Illuminate\Support\Facades\Schema;
use App\Enums\StrategyActionEnum;
use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;
use App\Enums\IsActiveEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\ActivationActionEnum;
use App\Enums\RestoringDeletingActionEnum;
use App\Enums\ActivatedRestoredActionEnum;
use App\Traits\Services\OperationsActivationRestoringTrait;

/**
 * Trait HandlesBulkOperationsTrait
 *
 * Provides reusable methods for handling bulk actions on models:
 * - Activate / Deactivate / Toggle
 * - Restore / Destroy / Force Delete
 *
 * Tracks results with arrays: processed_ids, unchanged_ids, conflict_ids, failed_ids, not_found_ids.
 *
 * Example:
 * ```php
 * $result = $this->handleBulkAction($request, User::class, ActivationActionEnum::TOGGLE->value);
 * ```
 */
trait HandlesBulkOperationsTrait
{
    use OperationsActivationRestoringTrait;
    
    /**
     * Handle bulk operations on records (restore, destroy, forceDelete, activate, deactivate, toggle)
     *
     * Tracks arrays:
     * - processed_ids: IDs successfully changed
     * - unchanged_ids: IDs that did not change
     * - conflict_ids: IDs skipped due to conflicts (activate/restore)
     * - failed_ids: IDs failed due to exceptions
     * - not_found_ids: IDs not found in DB
     *
     * Example:
     * ```php
     * $result = $this->handleBulkAction($request, User::class, RestoringDeletingActionEnum::RESTORE->value);
     * ```
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string|object $model
     * @param  string $action
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function handleBulkAction($request, $model, string $action, $forUser = false)
    {

        $data = $request->validated(); // Validate request and get data
        $inputIds = $request->input('ids', []); // Get IDs or 'all'
        $isAll = $inputIds === 'all'; // Check if operation is on all items

        // Pick trashed or normal query depending on action
        // Determine query: onlyTrashed for restore/forceDelete
        $query = in_array($action, [
            RestoringDeletingActionEnum::RESTORE->value, 
            RestoringDeletingActionEnum::FORCE_DELETE->value
        ]) 
            ? $model::onlyTrashed() // for restore/forceDelete
            : $model::withoutTrashed(); // other: activated, destroy

        // use it in restore or in activated -> modify , replace , prevent , use to avoid any conflict with  data in table when restoring or activated
        $strategy = $data['strategy'] ?? StrategyActionEnum::MODIFY->value;

        // Fetch the relevant items based on IDs or all
        $items = $this->fetchItemsByIdsOrAll($query, $isAll, $inputIds, $forUser);

        // Initialize arrays for tracking results
        $processedIds = [];   // Items successfully changed
        $unchangedIds = [];   // Items unchanged
        $failedIds = [];      // Items failed due to exceptions
        $notFoundIds = [];    // IDs not found
        $conflictIds = [];    // Items skipped due to conflicts

        // Loop through each item and perform the requested action
        foreach ($items as $item) {
            try {
                switch ($action) {
                    case RestoringDeletingActionEnum::RESTORE->value:
                       
                        if ($item->trashed()) { // Only restore if actually trashed
                            $noConflict = $this->restoreItem($item, $model, $strategy);
                            if (!$noConflict) $conflictIds[] = $item->id; // Track conflict
                            else $processedIds[] = $item->id; // Mark as processed
                        } else {
                            $unchangedIds[] = $item->id; // Already restore → unchanged
                        }
                        break;

                    case ActivationActionEnum::ACTIVATE->value: // from not_active to active
                        if ($item->is_active->value !== IsActiveEnum::ACTIVE->value) { // if not active -> will be activate , elseif active -> not changed(because it already active , and here activate)
                            $noConflict = $this->activateItem($item, $model, $strategy);
                            if (!$noConflict) $conflictIds[] = $item->id;
                            else $processedIds[] = $item->id;
                        } else {
                            $unchangedIds[] = $item->id;// Already active → unchanged
                        }
                        break;

                    case ActivationActionEnum::DEACTIVATE->value: // from active to not active
                        if ($item->is_active->value !== IsActiveEnum::NOT_ACTIVE->value) { // if active -> will be deactivate , elseif not_active -> not changed(because it already not_active , and here deactivate)
                           $this->deactivateItem($item);
                            $processedIds[] = $item->id;
                        } else {
                            $unchangedIds[] = $item->id;
                        }
                        break;

                    case ActivationActionEnum::TOGGLE->value: // from active to not active && from not_active to active
                        if ($item->is_active->value == IsActiveEnum::ACTIVE->value) { // from active to  not_active
                             $this->deactivateItem($item);
                            $processedIds[] = $item->id;
                       
                        } else { // from not_active to active
                            $noConflict = $this->activateItem($item, $model, $strategy);
                            if (!$noConflict) $conflictIds[] = $item->id;
                            else $processedIds[] = $item->id;
                        }
                        break;


                    case RestoringDeletingActionEnum::FORCE_DELETE->value:
                        if ($item->trashed()) { // Only delete if exists
                            $processedIds[] = $item->id;
                            $item->forceDelete(); // Permanently delete
                        } else {
                            $unchangedIds[] = $item->id; // Already deleted → unchanged
                        }
                        break;

                    case RestoringDeletingActionEnum::DESTROY->value:
                        if (!$item->trashed()) { // Only soft delete if not trashed
                            $this->destroyItem($item);
                            $processedIds[] = $item->id;
                        } else {
                            $unchangedIds[] = $item->id; // Already trashed → unchanged
                        }
                        break;

                    default:
                        throw new \InvalidArgumentException("Invalid action: {$action}");
                }
            } catch (\Throwable $e) {
                $failedIds[] = $item->id; // Track failures
            }
        }

        // Determine not found IDs (requested IDs minus processed & unchanged)
        $notFoundIds = $isAll ? [] : array_values(array_diff($inputIds, array_merge($processedIds, $unchangedIds)));

        return [
            'processed_ids' => $processedIds,
            'unchanged_ids' => $unchangedIds,
            'not_found_ids' => $notFoundIds,
            'conflict_ids'  => $conflictIds,
            'failed_ids'    => $failedIds,
        ];
    }

    /**
     * Fetch items by IDs or all items, optionally filtering by ownership.
     *
     * Example:
     * ```php
     * $items = $this->fetchItemsByIdsOrAll(User::query(), false, [1,2,3], true);
     * ```
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $isAll
     * @param array|string $inputIds
     * @return \Illuminate\Support\Collection
     *
     * @throws ApiResponseException
     */
    protected function fetchItemsByIdsOrAll($query, bool $isAll, $inputIds, $forUser = false)
    {
        // If no user is passed, use the currently authenticated user
        $user ??= getAuthUser();
        $userId = $user?->id;
    
        $model = $query->getModel(); 
        $hasUser = $forUser && Schema::hasColumn($model->getTable(), 'user_id');
        $hasClient = Schema::hasColumn($model->getTable(), 'client_id');
        if ($isAll) {
            $items = $query
                ->when(
                    $hasUser || $hasClient,
                    fn($q) => $q->where(function($q2) use ($hasUser, $hasClient, $userId) {
                        if ($hasUser) {
                            $q2->orWhere('user_id', $userId);
                        }
                        if ($hasClient) {
                            $q2->orWhere('client_id', $userId);
                        }
                    })
                )
                ->withoutGlobalScopes()->get(); // Fetch all items
        } else {
            $ids = $inputIds;
                $items = $query
                    ->when(
                        $hasUser || $hasClient,
                        fn($q) => $q->where(function($q2) use ($hasUser, $hasClient, $userId) {
                            if ($hasUser) {
                                $q2->orWhere('user_id', $userId);
                            }
                            if ($hasClient) {
                                $q2->orWhere('client_id', $userId);
                            }
                        })
                    )
                  ->withoutGlobalScopes()
                   ->whereIn('id', $ids)
                    ->get();
        }
        return $items;
    }

}
