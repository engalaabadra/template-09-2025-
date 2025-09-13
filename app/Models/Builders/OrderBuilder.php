<?php

namespace App\Models\Builders;

use App\Models\Order;
 use Illuminate\Database\Eloquent\Builder;
use App\Models\Filters\Order\OrderStatusFilter;
use App\Models\Filters\CreatedAtDateRangeFilter;
use App\Models\Filters\ActiveFilter;
use App\Models\Filters\LangFilter;

/**
 * Class OrderBuilder
 *
 * Custom Eloquent builder for the Order model.
 * Allows dynamic filtering using predefined filter classes and scopes.
 *
 *
 * It is commonly used for index/list endpoints in APIs or admin dashboards
 * to filter records based on specific query parameters.
 *
 * @mixin Order
 */
class OrderBuilder extends BaseBuilder
{
     /**
     * Define the available filters that can be applied on Order queries.
     *
     * Each filter is an instance of a Filter class, which internally calls a callback function
     * to apply query modifications.
     *
     * Supported filters from BaseBuilder:
     * - ActiveFilter: Filters Orders by `is_active` status using `isActive()` scope.
     * - LangFilter: Filters Orders by `lang` status using `lang()` scope.
     * - CreatedAtDateRangeFilter: Filters Orders by a date range on the `created_at` column using `createdAtRange()` scope.
     *
     * @return array<int, \App\Classes\Filter\BaseFilter>
     */
    public function filters(): array
    {
        return array_merge(
            parent::filters(), // call filters() from BaseBuilder
            [
                //add your filters this model
                new OrderStatusFilter(fn($value) => $this->status($value)),
            ]
        );
    }


    public function status($value)
    {
        return $this->when($value !== NULL, fn(Builder $query) => $query->where('status', $value));
    }
}
