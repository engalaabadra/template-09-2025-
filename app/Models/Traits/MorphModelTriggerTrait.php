<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait MorphModelTriggerTrait
 *
 * Automatically fills auditing fields (`created_by_id`, `updated_by_id`, `deleted_by_id`)
 * using polymorphic relationships when creating, updating, or soft deleting records.
 *
 * Requirements:
 * - The model must have columns: created_by_id, created_by_type, updated_by_id, updated_by_type, deleted_by_id, deleted_by_type
 * - The authenticated user must be accessible via `auth('api')->user()`
 *
 * Relationships:
 * - `createdBy`, `updatedBy`, `deletedBy` return morphTo relations to track user or model responsible for the change.
 *
 * Soft deletes:
 * - Uses Laravel's built-in SoftDeletes trait.
 *
 * Pruning:
 * - Implements automatic pruning of soft deleted models older than 30 days.
 *
 * @property-read User $createdBy
 * @property-read User $updatedBy
 * @property-read User $deletedBy
 * @property-read int|null $created_by_id
 * @property-read string|null $created_by_type
 * @property-read int|null $updated_by_id
 * @property-read string|null $updated_by_type
 * @property-read int|null $deleted_by_id
 * @property-read string|null $deleted_by_type
 */
trait MorphModelTriggerTrait
{
    use Prunable, SoftDeletes;

    /**
     * Polymorphic relationship to the user who created the record.
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relationship to the user who last updated the record.
     *
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relationship to the user who soft deleted the record.
     *
     * @return BelongsTo
     */
    public function deletedBy(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * Boot the trait and register model events for auditing.
     *
     * Automatically fills created_by, updated_by, and deleted_by fields.
     */
    protected static function booting(): void
    {
        self::MorphModelTriggerTrait();
    }

    /**
     * Register the model events: creating, updating, deleting.
     *
     * Automatically populates polymorphic auditing fields with the authenticated user.
     */
    protected static function MorphModelTriggerTrait(): void
    {

        static::creating(function ($row) {
            $user = self::getUserData();
            $row->created_by_id = $user['id'];
            $row->created_by_type = $user['type'];
        });

        static::updating(function ($row) {
            $user = self::getUserData();
            $row->updated_by_id = $user['id'];
            $row->updated_by_type = $user['type'];
        });

        static::deleting(function ($row) {
            $user = self::getUserData();
            $row->deleted_by_id = $user['id'];
            $row->deleted_by_type = $user['type'];
            $row->save();
        });
    }

    /**
     * Define a query for pruning (permanent deletion).
     *
     * - Works only with models using SoftDeletes.
     * - "onlyTrashed()" => target soft-deleted records.
     * - "where('deleted_at', '<', now()->subDays(30))" =>
     *     select records that were soft-deleted more than 30 days ago.
     * - when make use SoftDeletes, Prunable; after that make "php artisan model:prune" runs (or scheduled),
     *  will search on method prunable in model to excute this method, such as : remove permanently  from the database.
     *
     * Example:
     *   Suppose a user was soft-deleted on "2025-07-01".
     *   If today is "2025-08-15", this user will match the condition
     *   because it has been deleted for more than 30 days,
     *   and will be pruned (permanently deleted).
     *
     * Example with Scheduler:
     *   In "app/Console/Kernel.php", you can add:
     *
     *   protected function schedule(Schedule $schedule)
     *   {
     *       // Run pruning daily at midnight
     *       $schedule->command('model:prune')->daily();
     *   }
     *
     *   → This means Laravel will check every day at midnight,
     *     and automatically prune old soft-deleted records.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30));
    }

    /**
     * Retrieve the current authenticated user data for audit purposes.
     *
     * @return array{id: int|null, type: string|null}
     */
    private static function getUserData(): array
    {
        $user = auth('api')->user();
        return [
            'id' => $user?->id,
            'type' => $user ? get_class($user) : null,
        ];
    }
}
