<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure the input is a valid email address
 * and does not exceed 100 characters.
 */
class EmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  Closure  $fail       The callback function to call on failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Create a validator instance to validate email format , lowercase and max length
        $validate = Validator::make([$attribute => $value], [
            $attribute => "email|max:100|lowercase",
        ]);

        // If validation fails, extract the first error message and call the fail callback
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
