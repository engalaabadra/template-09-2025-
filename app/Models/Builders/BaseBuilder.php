<?php

namespace App\Models\Builders;

use App\Classes\Helpmate;
use App\Models\Traits\HelpersModelTrait;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Models\Filters\Order\OrderStatusFilter;
use App\Models\Filters\CreatedAtDateRangeFilter;
use App\Models\Filters\ActiveFilter;
use App\Models\Filters\LangFilter;
use App\Traits\Services\SearchTrait;
use App\Traits\Services\FilterTrait;

/**
 * Class BaseBuilder
 *
 * Extends Laravel's Eloquent Builder to include advanced filtering capabilities.
 * Uses custom Filter classes that define how various fields can be filtered.
 * Each filter applies a closure that modifies the query builder instance.
 */
class BaseBuilder extends Builder
{
    use HelpersModelTrait, SearchTrait, FilterTrait;

    /**
     * Define the available filters that can be applied on model queries.
     *
     * Each filter is an instance of a Filter class, which internally calls a callback function
     * to apply query modifications based on a given value.
     *
     * Supported filters:
     * - ActiveFilter: Filters models by `is_active` status using `isActive()` scope.
     * - LangFilter: Filters models by `lang` value using `lang()` scope.
     * - CreatedAtDateRangeFilter: Filters models by a date range on the `created_at` column
     *   using the `createdAtRange()` scope.
     *
     * @return array<int, \App\Classes\Filter\BaseFilter>  The list of applicable filter objects.
     */
    public function filters(): array
    {
        return [
            // Apply filter by active status using the isActive() scope
            new ActiveFilter(fn ($value) => $this->isActive($value)), // isActive() : in HelpersModelTrait

            // Apply filter by language using the lang() scope
            new LangFilter(fn ($value) => $this->lang($value)), // lang() : in HelpersModelTrait

            // Apply filter by creation date range using the createdAtRange() scope
            new CreatedAtDateRangeFilter(fn ($date) => $this->createdAtRange($date)), // createdAtRange() : in HelpersModelTrait
        ];
    }

   
}

