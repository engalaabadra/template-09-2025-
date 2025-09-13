<?php

namespace App\Models\Filters;

use App\Classes\Filter\Filter;
use App\Classes\Filter\FilterTypeEnum;
use App\Enums\IsActiveEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ActiveFilter
 *
 * A reusable filter that handles the `is_active` boolean status using a dropdown interface.
 * Designed to be used in data tables or query filters to toggle between active/inactive/all items.
 *
 * - Uses `IsActiveEnum` to provide translated options (e.g., Active / Not Active).
 * - Can accept a custom callback to override the default filtering logic.
 */
final class ActiveFilter extends Filter
{
    /**
     * The key used in the frontend to identify this filter (usually the database column).
     *
     * @var string
     */
    public string $key = 'is_active';

    /**
     * Defines the UI type of the filter in the frontend (dropdown, checkbox, range, etc.).
     *
     * @var FilterTypeEnum
     */
    public FilterTypeEnum $filterTypeEnum = FilterTypeEnum::DROPDOWN;

    /**
     * The translation key for the dropdown placeholder.
     * Used for display text like "Select status" in the UI.
     *
     * @var string|null
     */
    public ?string $placeholder = 'message.is_active_select';

    /**
     * Indicates whether the selected values should be treated as integers.
     * Useful for enum-based filters or when the backend expects integers (e.g., 0/1).
     *
     * @var bool
     */
    public bool $isInt = true;

    /**
     * Optional closure that allows custom filtering logic instead of the default behavior.
     *
     * @param \Closure|null $callback A custom filtering callback
     */
    public function __construct(public ?\Closure $callback = null)
    {
    }

    /**
     * Returns the available dropdown options.
     *
     * It pulls values from the `IsActiveEnum::getOptionsData()`,
     * which typically returns a list like:
     * @example
     * 
     *   // [
     *   //    ['id' => IsActiveEnum::ACTIVE, 'name' => 'Active'],
     *   //    ['id' => IsActiveEnum::NOT_ACTIVE, 'name' => 'Inactive']
     *   // ]     *
     * @return null|Arrayable|array|string
     */
    public static function getData(): null|Arrayable|array|string
    {
        return IsActiveEnum::getOptionsData();
    }
}
