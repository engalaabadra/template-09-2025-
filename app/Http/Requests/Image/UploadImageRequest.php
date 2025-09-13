<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ImageRule;

/**
 * Class UploadImageRequest
 *
 * Handles validation for uploading a single image file.
 * Ensures the image is required, of specified mime types, and within the size limit.
 */
class UploadImageRequest extends FormRequest
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
            // image: must be a valid image
            'image' => [new ImageRule()]
        ];
    }

    /**
     * Custom validation messages (optional).
     *
     * @return array<string, string>
     */
    public function messages()
    {
        // No custom messages defined
        return [];
    }
}
