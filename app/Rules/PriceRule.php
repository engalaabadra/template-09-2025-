<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure the given value is numeric.
 * Typically used for validating prices or any number-based input.
 */
class PriceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string  $attribute The name of the attribute being validated.
     * @param mixed   $value     The value of the attribute.
     * @param Closure $fail      The callback to be called on failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Create a validator instance to check if the value is numeric
        $validate = Validator::make([$attribute => $value], [
            $attribute => 'numeric',
        ]);

        // If validation fails, trigger the fail callback with the first error message
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
