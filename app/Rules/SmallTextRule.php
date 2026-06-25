<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Class SmallTextRule
 *
 * A custom validation rule to ensure a given string is between 2 and 100 characters.
 * This rule is useful for small text fields like names, titles, etc.
 */
class SmallTextRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * This method uses Laravel's validator internally to apply `min:2|max:100`
     * constraints and triggers a failure if validation fails.
     *
     * @param  string   $attribute  The name of the attribute under validation
     * @param  mixed    $value      The value of the attribute
     * @param  \Closure $fail       A callback to be called if validation fails
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Create a validator instance with min and max rules
        $validate = Validator::make([$attribute => $value], [
            $attribute => 'min:2|max:100',
        ]);

        // If validation fails, retrieve the first error message and call the fail closure
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
