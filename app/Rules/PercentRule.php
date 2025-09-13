<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure a given value is a valid percentage.
 * The value must be numeric and fall within the range of 0 to 100.
 */
class PercentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  \Closure $fail       The callback that should be called on validation failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate that the value is numeric and between 0 and 100
        $validate = Validator::make([$attribute => $value], [
            $attribute => 'numeric|between:0,100',
        ]);

        // If validation fails, retrieve the first error message and pass it to the fail callback
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
