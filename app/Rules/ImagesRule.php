<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rule to validate image uploads.
 * Ensures each file is an image (jpg, png, jpeg) and does not exceed 3MB in size.
 */
class ImagesRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string   $attribute  The name of the attribute being validated.
     * @param  mixed    $value      The value of the attribute (expected to be an uploaded file or array of files).
     * @param  Closure  $fail       The callback to call with the error message if validation fails.
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the value is null, consider it valid (nullable)
        if (is_null($value)) {
            return;
        }

        // If the value is not an array, fail with an appropriate error
        if (!is_array($value)) {
            $fail(__('The :attribute must be an array of images.'));
            return;
        }

        // Build the data array to validate
        $data = [$attribute => $value];

        // Define validation rules:
        // 1. The attribute must be an array (nullable)
        // 2. Each item must be an image file (jpg, jpeg, png) not exceeding 3MB
        $rules = [
            $attribute => 'nullable|array',
            "{$attribute}.*" => 'nullable|image|mimes:jpg,jpeg,png|max:3000',
        ];

        // Create the validator instance
        $validate = Validator::make($data, $rules);

        // If validation fails...
        if ($validate->fails()) {
            // Extract the first error message from the validation errors
            $firstError = collect($validate->messages()->toArray())->first()[0] ?? __('Invalid image.');

            // Pass the error message to the fail closure
            $fail($firstError);
        }
    }
}
