<?php

namespace App\Http\Requests\Auth\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Models\User;

/**
 * Class CheckCodeRequest
 *
 * This request class handles the validation for checking a verification code (e.g., for password reset).
 */
class CheckCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true to allow any user to make the request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for the request.
     *
     * Requires the presence of the 'code' field.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'code' is required in the request
            'code' => ['required'],
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * Currently empty but can be customized later if needed.
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }
}
