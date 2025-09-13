<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule for validating area values.
 * Ensures the input is a numeric value greater than or equal to 0.
 */
class AreaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute to validate.
     * @param  Closure  $fail       The callback used to report a validation failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Use Laravel's Validator to apply numeric and minimum value constraints
        $validate = Validator::make([$attribute => $value], [
            $attribute => "numeric|min:0"
        ]);

        // If validation fails, trigger the failure with the first error message
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
