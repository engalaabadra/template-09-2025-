<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to check if a given text is within a large text range.
 * It ensures that the text is between 2 and 5000 characters.
 */
class LargeTextRule implements ValidationRule
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
        // Create a validator instance with min and max length rules
        $validate = Validator::make([$attribute => $value], [
            $attribute => "min:2|max:5000",
        ]);

        // If validation fails, extract the first error message and call the fail callback
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
