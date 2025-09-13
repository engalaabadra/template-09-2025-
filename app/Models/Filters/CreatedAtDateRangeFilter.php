<?php

namespace App\Models\Filters;

use App\Classes\Filter\FilterTypeEnum;
use App\Classes\Filter\Filter;

/**
 * Class CreatedAtDateRangeFilter
 *
 * A filter class used to apply a date range condition on the `created_at` column.
 * Typically used in query builders to filter records created within a specific date range.
 *
 * Example usage:
 * - Filtering results from "2023-01-01" to "2023-01-31"
 *
 * You can optionally pass a custom callback to customize the filtering logic.
 */
final class CreatedAtDateRangeFilter extends Filter
{
    /**
     * The key/column name that this filter applies to.
     *
     * @var string
     */
    public string $key = 'created_at';

    /**
     * The type of filter being applied (in this case, a date range filter).
     *
     * @var FilterTypeEnum
     */
    public FilterTypeEnum $filterTypeEnum = FilterTypeEnum::DATE_RANGE;

    /**
     * Constructor.
     *
     * @param \Closure|null $callback An optional callback to override the default filter behavior.
     */
    public function __construct(public ?\Closure $callback = null) {}
}
