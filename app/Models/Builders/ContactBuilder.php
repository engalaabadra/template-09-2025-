<?php

namespace App\Models\Builders;

 use Illuminate\Database\Eloquent\Builder;

/**
 * Class ContactBuilder
 *
 * Custom Eloquent builder for the Contact model.
 * Allows dynamic filtering using predefined filter classes and scopes.
 *
 *
 * It is commonly used for index/list endpoints in APIs or admin dashboards
 * to filter records based on specific query parameters.
 *
 * @mixin Contact
 */
class ContactBuilder extends BaseBuilder
{
     /**
     * Define the available filters tontact can be applied on Contact queries.
     *
     * Each filter is an instance of a Filter class, which internally calls a callback function
     * to apply query modifications.
     *
     * Supported filters from BaseBuilder:
     * - ActiveFilter: Filters Contacts by `is_active` status using `isActive()` scope.
     * - CreatedAtDateRangeFilter: Filters Contacts by a date range on the `created_at` column using `createdAtRange()` scope.
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
