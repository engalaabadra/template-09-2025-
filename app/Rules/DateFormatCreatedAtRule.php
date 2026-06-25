<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to ensure the "created_at" date field
 * is in the correct format: either Y-m-d (e.g. 2025-07-31) or Y-m-j (e.g. 2025-07-1).
 */
class DateFormatCreatedAtRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute.
     * @param  Closure  $fail       The callback to call if validation fails.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Perform validation using Laravel's built-in validator with multiple accepted formats
        $validate = Validator::make([$attribute => $value], [
            $attribute => "date_format:d-m-Y,Y-m-j", // Accept either format
        ]);

        // If validation fails, retrieve and pass the first error message to the fail callback
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }
}
