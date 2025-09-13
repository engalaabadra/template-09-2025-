<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class FilesRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the value is null, it's considered valid (nullable)
        if (is_null($value)) {
            return;
        }

        // If the value is not an array, fail with an appropriate error message
        if (!is_array($value)) {
            $fail(__('The :attribute must be an array of files.'));
            return;
        }

        // Prepare the data array for validation
        $data = [$attribute => $value];

        // Define validation rules:
        // 1. The attribute must be an array (required)
        // 2. Each item in the array must be a valid file with specific mime types and size limit
        $rules = [
            $attribute => 'required|array',
            "{$attribute}.*" => 'required|file|mimes:pdf,doc,docx,jpg,png,xlsx|max:500000',
        ];

        // Create the validator instance using Laravel's Validator facade
        $validate = Validator::make($data, $rules);

        // If validation fails...
        if ($validate->fails()) {
            // Get the first error message from the validation errors, if available
            $firstError = collect($validate->messages()->toArray())->first()[0] ?? __('Invalid file.');

            // Trigger the fail callback with the first error message
            $fail($firstError);
        }
    }
}
