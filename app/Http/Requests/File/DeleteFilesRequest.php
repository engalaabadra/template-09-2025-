<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\FilesRule;
use App\Http\Requests\BaseBulkActionRequest;
use App\Rules\IdsRule;

/**
 * Class DeleteFilesRequest
 *
 * This request class handles validation for Deleteing multiple files.
 * It ensures each file is required, of a valid type, and within the size limit.
 */
class DeleteFilesRequest extends BaseBulkActionRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():  bool
    {
        // Allow all users to perform this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
         return array_merge(parent::rules(), [ // parent -> BaseBulkActionRequest to take 'ids' to put here in this req.
            //here add another rules

        ]);
    }

    /**
     * Custom error messages (optional).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // No custom messages defined
        return [];
    }
}
