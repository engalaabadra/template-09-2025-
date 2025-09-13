<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

/**
 * Class SaudiNationalIdRule
 *
 * A custom validation rule to validate Saudi national ID numbers.
 * Ensures the value consists of exactly 10 digits.
 */
class SaudiNationalIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * This method uses Laravel's validator to apply a regex pattern
     * that checks if the value is a 10-digit number.
     *
     * @param  string   $attribute  The name of the attribute under validation
     * @param  mixed    $value      The value of the attribute
     * @param  \Closure $fail       A callback to be called if validation fails
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Use Laravel Validator to apply regex pattern for exactly 10 digits
        $validate = Validator::make([$attribute => $value], [
            $attribute => ['regex:/^\d{10}$/'],
        ]);

        // If validation fails, trigger the fail callback with the first error message
        if ($validate->fails()) {
            $fail(data_get($validate->messages()->toArray()[$attribute], 0));
        }
    }

    /**
     * Get the validation error message.
     *
     * Returns a translated validation message for the national ID.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('validation.national_id');
    }
}
