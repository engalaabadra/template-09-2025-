<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure the input matches either a Hijri or Gregorian date format.
 * Accepted formats: Y-m-d (e.g. 2025-07-31) and Y-m-j (e.g. 2025-07-1)
 */
class DateHijriGregorianRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  Closure  $fail       The callback function to call on validation failure.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Use Laravel's built-in Validator to check date formats
        $validate = Validator::make([$attribute => $value], [
            $attribute => "date_format:Y-m-d,Y-m-j", // Accepts both formats
        ]);

        // If validation fails, retrieve and return the first error message
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
