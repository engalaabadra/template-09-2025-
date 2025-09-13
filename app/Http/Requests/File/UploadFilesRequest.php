<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\FilesRule;

/**
 * Class UploadFilesRequest
 *
 * This request class handles validation for uploading multiple files.
 * It ensures each file is required, of a valid type, and within the size limit.
 */
class UploadFilesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to perform this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
           // files: must be a valid files
            'files' => [new FilesRule()]
        ];
    }

    /**
     * Custom error messages (optional).
     *
     * @return array<string, string>
     */
    public function messages()
    {
        // No custom messages defined
        return [];
    }
}
