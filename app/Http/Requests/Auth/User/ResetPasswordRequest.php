<?php

namespace App\Http\Requests\Auth\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/**
 * Class ResetPasswordRequest
 *
 * This request handles validation for resetting the user's password.
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true to allow the request to be processed.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the reset password request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Password is required, must follow Laravel's default password rules, and be confirmed
            'password' => ['required', Rules\Password::defaults(), 'confirmed'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * Currently empty; can be filled with custom messages if needed.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // You can define custom error messages here
        ];
    }
}
