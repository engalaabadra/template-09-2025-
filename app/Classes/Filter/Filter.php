<?php

namespace App\Classes\Filter;

use App\Classes\Filter\FilterTypeEnum;
use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Filter
 *
 * This abstract base class represents a generic filter structure
 * that can be extended by specific filter implementations.
 * 
 * It provides default properties such as key, placeholder, type,
 * integer casting, caching usage, and display options.
 * 
 * Additionally, it defines methods for returning filter metadata
 * and converting the filter into an array for use in front-end components.
 *
 * @package App\Classes\Filter
 */
abstract class Filter
{
    /** 
     * @var string The unique key identifier for the filter. 
     */
    public string $key;

    /** 
     * @var string|null The placeholder text displayed for this filter (optional).
     */
    public ?string $placeholder = null;

    /** 
     * @var string The filter type (can be explicitly set or derived from FilterTypeEnum).
     */
    public string $type;

    /** 
     * @var FilterTypeEnum The enum instance that represents the filter type.
     */
    public FilterTypeEnum $filterTypeEnum;

    /** 
     * @var Closure|null A callback function that can be used for custom filter logic.
     */
    public ?Closure $callback;

    /** 
     * @var bool Whether the filter value should be treated as an integer.
     */
    public bool $isInt = false;

    /** 
     * @var bool Whether to use cached data for this filter.
     */
    public bool $useCash = true;

    /** 
     * @var bool Whether this filter should be visible in the UI.
     */
    public bool $show = true;

    /** 
     * @var string The URL to fetch filter search options from.
     */
    public string $search_url = '';

    /** 
     * @var string The property name used for labeling options (e.g., 'name', 'title').
     */
    public string $optionLabel = 'name';

    /** 
     * @var int The minimum value allowed for the filter (if applicable).
     */
    public int $min = 333;

    /** 
     * @var int The maximum value allowed for the filter (if applicable).
     */
    public int $max = 555;

    /**
     * Retrieves the data for the filter.
     * Can return null, an array, a string, or an Arrayable object.
     * 
     * By default, returns null. 
     * Child classes can override this method to return specific data.
     *
     * @return null|Arrayable|array|string
     */
    public static function getData(): null|Arrayable|array|string
    {
        return null; // No data by default
    }

    /**
     * Returns the filter's key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key; // Simply returns the filter key property
    }

    /**
     * Converts the filter instance into an array for use in front-end components.
     *
     * @return array The filter properties as an array.
     */
    public function toArray(): array
    {
        // If $type is set, use it; otherwise use the value from the FilterTypeEnum
        $type = $this->type ?? $this->filterTypeEnum->value;

        return [
            // The type of this filter
            'type' => $type,

            // Placeholder text for the filter input
            'placeholder' => $this->placeholder,

            // Whether the filter expects an integer value
            'isInt' => $this->isInt,

            // Whether to show this filter in the UI
            'show' => $this->show,

            // Minimum value for numeric filters
            'min' => $this->min,

            // Maximum value for numeric filters
            'max' => $this->max,

            // URL for fetching filter data from the backend
            'search_url' => $this->search_url,

            // The property name used for displaying option labels
            'optionLabel' => $this->optionLabel,

            // The filter's data, either retrieved from cache or freshly fetched
            'data' => $this->useCash
                ? \Cache::remember($type . $this->key, 10, fn() => static::getData()) // Cache for 10 seconds
                : static::getData() // Directly get data without caching
        ];
    }
}
