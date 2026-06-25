<?php

namespace App\Models\Builders;

 use Illuminate\Database\Eloquent\Builder;


/**
 * Class BannerBuilder
 *
 * Custom Eloquent builder for the Banner model.
 * Allows dynamic filtering using predefined filter classes and scopes.
 *
 *
 * It is commonly used for index/list endpoints in APIs or admin dashboards
 * to filter records based on specific query parameters.
 *
 * @mixin Banner
 */
class BannerBuilder extends BaseBuilder
{
 
    /**
     * Define the available filters that can be applied on Banner queries.
     *
     * Each filter is an instance of a Filter class, which internally calls a callback function
     * to apply query modifications.
     *
     * Supported filters from BaseBuilder:
     * - ActiveFilter: Filters banners by `is_active` status using `isActive()` scope.
     * - CreatedAtDateRangeFilter: Filters banners by a date range on the `created_at` column using `createdAtRange()` scope.
     *
     * @return array<int, \App\Classes\Filter\BaseFilter>
     */
    public function filters(): array
    {
        return array_merge(
            parent::filters(), // call filters() from BaseBuilder
            [
                //add your filters this model
                
            ]
        );
    }
}
