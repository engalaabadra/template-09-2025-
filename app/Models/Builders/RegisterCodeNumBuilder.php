<?php

namespace App\Models\Builders;

use App\Models\RegisterCodeNum;
 use Illuminate\Database\Eloquent\Builder;
use App\Models\Filters\CreatedAtDateRangeFilter;

/**
 * Class RegisterCodeNumBuilder
 *
 * Custom Eloquent builder for the RegisterCodeNum model.
 * Allows dynamic filtering using predefined filter classes and scopes.
 *
 *
 * It is commonly used for index/list endpoints in APIs or admin dashboards
 * to filter records based on specific query parameters.
 *
 * @mixin RegisterCodeNum
 */
class RegisterCodeNumBuilder extends BaseBuilder
{
    // Define an array of filter classes to be excluded from the final result filters
    protected array $excludedFilterClasses = [
        \App\Models\Filters\ActiveFilter::class,
    ];

    /**
     * Check if the given filter should be excluded.
     *
     * @param mixed $filter  The filter instance to check.
     * @return bool  Returns true if the filter is one of the excluded classes.
     */
    protected function isExcludedFilter($filter): bool
    {
        // Loop through each excluded filter class
        foreach ($this->excludedFilterClasses as $excludedClass) {
            // If the filter is an instance of any excluded class, return true
            if ($filter instanceof $excludedClass) {
                return true;
            }
        }

        // If no match found, do not exclude this filter
        return false;
    }
    /**
     * Define the available filters that can be applied on RegisterCodeNum queries.
     *
     * Each filter is an instance of a Filter class, which internally calls a callback function
     * to apply query modifications.
     *
     * Supported filters from BaseBuilder:
     * - CreatedAtDateRangeFilter: Filters RegisterCodeNums by a date range on the `created_at` column using `createdAtRange()` scope.
     *
     * @return array<int, \App\Classes\Filter\BaseFilter>
     */
    public function filters(): array
    {
        return array_merge(
            // Include only filters that are not excluded
            array_values(array_filter(parent::filters(), fn($filter) => ! $this->isExcludedFilter($filter))),
            [
                // Add any custom filters specific to this model here if needed
            ]
        );
    }
}
