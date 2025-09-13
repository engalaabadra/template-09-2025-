<?php

namespace App\Classes\Filter;

/**
 * Class FilterData
 *
 * Responsible for converting an array of filter class names
 * into an associative array of filter keys and their array representations.
 *
 * @package App\Classes\Filter
 */
class FilterData
{
    /**
     * Convert an array of filter class names into an associative array
     * containing each filter's key and its data from toArray().
     *
     * @param array $filters List of filter class names (string FQCNs).
     * @return array Associative array of filter key => filter data.
     */
    public static function from(array $filters): array
    {
        // Initialize the result array
        $data = [];

        // Loop through each provided filter class
        foreach ($filters as $filter) {
            /** @var Filter $filter Ensure $filter is treated as a Filter instance */

            // Resolve the filter instance from the service container
            $filter = app($filter);

            // Remove the callback property to avoid serializing closures
            unset($filter->callback);

            // Use the filter's key as the array key and store its array representation
            $data[$filter->key] = $filter->toArray();
        }

        // Return the fully built filters data array
        return $data;
    }
}
