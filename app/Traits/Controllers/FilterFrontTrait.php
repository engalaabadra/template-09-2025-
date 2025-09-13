<?php

namespace App\Traits\Controllers;

use Inertia\Inertia;

trait FilterFrontTrait
{
   
    /**
     * Get all frontend-ready filters from a given model.
     * render all elements for filters in front like(min , max & dropdown options from Enum:[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ])
     * This method fetches the filters defined in the model's custom query builder,
     * converts each filter into an array using its `toArray()` method, and returns
     * the result as a plain array suitable for frontend consumption.
     *
     * Each filter object may internally call `getData()` to retrieve options
     * this getData() : call getOptionsData() that is exist in any enum class to show like:  dropdown options from Enum:[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ]
     * (for example from an Enum) and include properties like min, max, label, etc.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     *         The Eloquent model class or instance to get filters from.
     * 
     * @return array
     *         An array of filters formatted for frontend display.
     *
     * @example
     * $filters = $this->getModelFilters(User::class);
     * // Returns something like:
     * // [
     * //     ['name' => 'Status', 'type' => 'dropdown', 'options' => [[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active']]],
     * //     ['name' => 'Created At', 'type' => 'range', 'min' => ..., 'max' => ...],
     * // ]
     */
    protected function getModelFilters($model): array
    {
        //render all elements for filters in front like(min , max & dropdown options from Enum:[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ])
        $filters = collect($model::query()->filters()) // 1. Get the filters method from the model's custom BaseBuilder
           // 2. each filter extends from Filer class , this class contain on toArray() : contain all elements for filter for front (min , max , label)
           //  and calls getData() internally that it exist in every filter class like IsActiveFilter 
           //  this getData() : call getOptionsData() that is exist in any enum class to show like:  dropdown options from Enum:[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ]
            ->map(fn($filter) => $filter->toArray())   
            ->toArray();                               // 3. Convert the Collection back to a plain array ready for frontend

        return $filters;
    }
     /**
     * Share filters with Inertia.
     *
     * @param array|Collection|null $filters
     * @return void
     */
    public function useFilter(null|array|Collection $filters = NULL): void
    {
        if ($filters) {
            Inertia::share(['filters' => $filters]);
        }
    }
}
