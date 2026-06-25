<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule for validating Saudi phone numbers.
 * It checks if the number matches a specific regex pattern used for local mobile formats.
 */
class PhoneNumberRule implements ValidationRule
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
        // Create a validator instance to validate phone must be numeric, match regex
        $validate = Validator::make([$attribute => $value], [
            $attribute => 'numeric|regex:/^\d+$/|digits_between:7,14',
        ]);

        // If validation fails, extract the first error message and call the fail callback
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
