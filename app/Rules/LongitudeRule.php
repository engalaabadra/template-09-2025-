<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Custom validation rule to validate longitude values.
 * Longitude must be a numeric value between -180 and 180.
 */
class LongitudeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  \Closure $fail       The callback to invoke if validation fails.
     * @return void
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Check if the value is not numeric or outside the longitude range
        if (!is_numeric($value) || $value < -180 || $value > 180) {
            // Fail with a translated message indicating the allowed range
            $fail(__('validation.between.numeric', ['min' => -180, 'max' => 180]));
        }
    }
}
