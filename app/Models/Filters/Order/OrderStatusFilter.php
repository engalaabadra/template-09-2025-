<?php

namespace App\Models\Filters\Order;

use App\Classes\Filter\Filter;
use App\Classes\Filter\FilterTypeEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OrderStatusFilter
 *
 * Represents a dropdown filter for filtering records by their order status.
 * Uses the OrderStatusEnum to populate the dropdown options and applies a callback for custom filtering logic.
 */
final class OrderStatusFilter extends Filter
{
    /**
     * The key used in the frontend and query string for this filter (usually a database column name).
     *
     * @var string
     */
    public string $key = 'status';

    /**
     * Defines the type of UI element used to display the filter in the frontend.
     * In this case, it's a dropdown.
     *
     * @var FilterTypeEnum
     */
    public FilterTypeEnum $filterTypeEnum = FilterTypeEnum::DROPDOWN;

    /**
     * Optional closure to provide custom query logic when this filter is applied.
     *
     * @param \Closure|null $callback A callback to customize the query modification
     */
    public function __construct(public ?\Closure $callback = null)
    {
    }

    /**
     * Returns the data used to populate the dropdown options.
     *
     * This is usually an array of options like:
     *   [ ['id' => 1, 'name' => 'Pending'], ['id' => 2, 'name' => 'Completed'], ... ]
     *
     * @return null|Arrayable|array|string
     */
    public static function getData(): null|Arrayable|array|string
    {
        return OrderStatusEnum::getOptionsData();
    }
}