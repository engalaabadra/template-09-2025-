<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to validate a single image upload.
 * Ensures the uploaded file is an image (jpg, png, jpeg) and does not exceed 3MB in size.
 */
class ImageRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute (expected to be an uploaded file).
     * @param  Closure  $fail       The callback to call with the error message if validation fails.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Create a validator instance to validate the given attribute using Laravel's validation rules
        $validate = Validator::make(
            [$attribute => $value], // Input data array: key is attribute name, value is uploaded file
            [
                $attribute => "nullable|image|max:3000|mimes:jpg,png,jpeg", // Rule: must be image, of specific types, max 3MB
            ]
        );

        // If the validation fails...
        if ($validate->fails()) {
            // Get the first error message related to the attribute from the validator
            // $errorMessage = data_get($validate->messages()->toArray()[$attribute], 0);
            $errorMessage = collect($validate->messages()->toArray())
                ->get($attribute)[0] ?? __('Invalid file.');

            // Call the fail callback with the error message to mark the validation as failed
            $fail($errorMessage);
        }
    }
}
