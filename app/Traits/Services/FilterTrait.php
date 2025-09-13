<?php
namespace App\Traits\Services;

/**
 * Trait FilterTrait
 *
 * Provides reusable methods to handle model filtering logic:
 * - `filter()` → applies request-based filters dynamically using callbacks defined in the model’s query builder.
 * - `getModelFilters()` → transforms model-defined filters into frontend-ready arrays (with options, min/max, labels, etc.).
 *
 * Example usage:
 * ```php
 * // Apply filters to query
 * $users = User::query()->filter()->get();
 *
 * // Get filters for frontend rendering (dropdowns, ranges, etc.)
 * $filters = $this->getModelFilters(User::class);
 * ```
 */
trait FilterTrait{
     /**
     * Apply filters to the current instance based on request values.
     *
     * @return $this
     */
    public function filter(): static
    {
        foreach ($this->filters() as $filter) { // Loop through defined filters
            $value = request($filter->key); // Get value from request
            if ($value !== null) { // Apply only if value exists
                // Execute filter callback($this->isActive($value)),in ->(filter) : new ActiveFilter(fn ($value) => $this->isActive($value)),
               data_get($filter, 'callback')($value); //now i excuted isActive($value) -> inside it : get data only 'is_active' = 1
            }
        }
        return $this; // For method chaining
    }

}
