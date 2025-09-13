<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class FavoriteRequest
 *
 * This request handles validation rules for adding a content to the user's favorites.
 */
class FavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to perform this request
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
            // content_id is required, must be numeric, and must exist in the contents table
            'content_id' => [
                            'required',
                            'numeric',
                            'exists:contents,id',
                            // Rule::unique('favorites')->where(function ($query) {
                            //     return $query->where('user_id', auth()->guard('api')->user()->id);
                            // }),
                        ],

        ];
    }

    /**
     * Get the custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Add custom messages here if needed
        ];
    }
}
