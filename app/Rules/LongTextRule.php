<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure a given text field has a minimum length.
 * In this case, the text must contain at least 2 characters.
 */
class LongTextRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  \Closure $fail       The callback to invoke if validation fails.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate that the text has at least 2 characters
        $validate = Validator::make([$attribute => $value], [
            $attribute => 'min:2',
        ]);

        // If validation fails, return the first error message for this attribute
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
