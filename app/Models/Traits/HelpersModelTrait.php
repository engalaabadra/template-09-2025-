<?php

namespace App\Models\Traits;

use App\Classes\DateHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Search;


/**
 * Trait HelpersModelTrait
 *
 * A reusable set of query builder helper methods commonly used in Eloquent models.
 * Provides shortcuts for filtering, searching, scoping, and dynamic where conditions.
 *
 * @mixin Model
 */
trait HelpersModelTrait
{
    
    /**
     * Filter by a date range on the `created_at` column.
     *
     * Example:
     * `$query->createdAtRange('2024-01-01,2024-01-31');`
     *
     * @param string|array|null $dateRange
     * @param string $column
     * @return static
     */
    final public function createdAtRange(string|array|null $dateRange, $column = 'created_at'): static
    {
        return $this->rangeDateFilter($dateRange, $column);
    }

    /**
     * Apply a `where is_active = ?` filter only if `$active` is not null.
     *
     * Example:
     * `$query->isActive(true); // where is_active = 1`
     *
     * @param bool|null $active
     * @return static
     */
    final public function isActive(?bool $active = null): static
    {
        return $this->when($active !== null, function (Builder $q) use ($active) {
            $q->where('is_active', $active);
        });
    }

    
    /**
     * Apply a `where lang = ?` filter only if `$lang` is not null.
     *
     * Example:
     * `$query->lang(lang()); // where lang = ar`
     *
     * @param string $lang
     * @return static
     */
    final public function lang(?string $lang): static
    {
        if (!Schema::hasColumn($this->getModel()->getTable(), 'lang')) {
            return $this;
        }
        return $this->when($lang, function (Builder $q) use ($lang) {
           // $q->whereRaw("TRIM(lang) = ?", [$lang]);

            $q->where('lang', $lang);
        });
    }

    /**
     * Filter by a date range on the `updated_at` column.
     *
     * Example:
     * `$query->updatedAtRange('2024-01-01,2024-01-31');`
     *
     * @param string|null $dateRange
     * @return static
     */
    final public function updatedAtRange(?string $dateRange): static
    {
        return $this->rangeDateFilter($dateRange, 'updated_at');
    }

    /**
     * Filter a model based on a date range, ignoring time.
     *
     * Example:
     * `$query->rangeDateFilter('2024-01-01,2024-01-10', 'published_at');`
     *
     * @param string|array|null $date_range
     * @param string $column
     * @return static
     */
    public function rangeDateFilter($date_range, $column = 'created_at'): static
    {
        $date = DateHelper::getRangeFromRequestPeriod($date_range);
        return $this->when($date !== null,
            fn(Builder $q) => $q->whereDate($column, '>=', Arr::first($date))
                ->when(Arr::last($date) !== null, fn(Builder $q) => $q->whereDate($column, '<=', Arr::last($date)))
        );
    }

    


    /**
     * Smart helper to apply `where`, `whereIn`, or skip conditionally.
     *
     * Example:
     * `$query->whereOrWhereIn('status', ['pending', 'approved']);`
     *
     * @param string $column
     * @param mixed $values
     * @param bool $search_for_null
     * @return static
     */
    public function whereOrWhereIn($column, $values = null, $search_for_null)
    {
        if (!$search_for_null) {
            if (!$values || (is_array($values) && count($values) === 0)) {
                return $this;
            }
        }

        if (!is_array($values))
            return $this->where($column, $values);

        if (count($values) == 1)
            return $this->where($column, $values[0]);

        return $this->whereIn($column, $values);
    }

    /**
     * Filter by one or more status values (uses `whereOrWhereIn`).
     *
     * Example:
     * `$query->filterStatus(['active', 'suspended']);`
     *
     * @param array|string $values
     * @param string $column_name
     * @return static
     */
    public function filterStatus($values = [], $column_name = 'status')
    {
        return $this->whereOrWhereIn($column_name, $values);
    }

    /**
     * Generic method to apply `whereOrWhereIn` for any column.
     *
     * Example:
     * `$query->columnWhereOrWhereIn('type', ['admin', 'user']);`
     *
     * @param string $column_name
     * @param array|string $values
     * @return static
     */
    public function columnWhereOrWhereIn($column_name, $values = [])
    {
        return $this->whereOrWhereIn($column_name, $values);
    }

    /**
     * Apply `whereOrWhereIn` filter to a column in a related model.
     *
     * Example:
     * `$query->relationColumnWhereOrWhereIn('department', 'type', ['main', 'support']);`
     *
     * @param string $relation
     * @param string $column_name
     * @param array|string $values
     * @return static
     */
    public function relationColumnWhereOrWhereIn($relation, $column_name, $values = [])
    {
        return $this->whereHas($relation, fn($z) => $z->whereOrWhereIn($column_name, $values));
    }
}

