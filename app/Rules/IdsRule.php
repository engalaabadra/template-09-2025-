<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IdsRule implements ValidationRule
{
    /**
     * Validate the given attribute.
     *
     * Supports:
     * - "all"
     * - ["all"]
     * - single integer
     * - array of integers
     * - "1,2,3" (comma-separated string)
     * - ["1,2,3"] (array with a single comma-separated string)
     *
     * @param  string   $attribute
     * @param  mixed    $value
     * @param  \Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Case: exact string "all"
        if ($value === 'all') {
            return;
        }

        // Case: array with single element "all"
        if (is_array($value) && count($value) === 1 && isset($value[0]) && $value[0] === 'all') {
            return;
        }

        // Case: single integer
        if (is_int($value)) {
            return;
        }

        // Case: comma-separated string "1,2,3"
        if (is_string($value) && str_contains($value, ',')) {
            $ids = array_filter(explode(',', $value), fn($id) => $id !== '');
            if ($this->allIntegers($ids)) {
                return;
            }
        }

        // Case: array with a single string containing commas, e.g., ["1,2,3"]
        if (is_array($value) && count($value) === 1 && isset($value[0]) && is_string($value[0]) && str_contains($value[0], ',')) {
            $ids = array_filter(explode(',', $value[0]), fn($id) => $id !== '');
            if ($this->allIntegers($ids)) {
                return;
            }
        }

        // Case: array of integers
        if (is_array($value) && $this->allIntegers($value)) {
            return;
        }

        // Case: single numeric string
        if (is_string($value) && is_numeric($value)) {
            return;
        }

        // Invalid value → fail validation
        $fail("The {$attribute} field must be 'all' or a list of integer IDs.");
    }

    /**
     * Check if all items in the array are integers (numeric strings allowed).
     */
    protected function allIntegers(array $items): bool
    {
        foreach ($items as $item) {
            if (!is_numeric($item) || intval($item) != $item) {
                return false;
            }
        }
        return true;
    }
}
