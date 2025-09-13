<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ImagesRule;

/**
 * Class UploadImagesRequest
 *
 * Handles validation for uploading multiple images.
 * Ensures 'images' is an array, and each image is required, 
 * must be a valid image of specified mime types, and within size limits.
 */
class UploadImagesRequest extends FormRequest
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
            // images: must be a valid images
            'images' => [new ImagesRule()]
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
