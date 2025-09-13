<?php

namespace App\Http\Requests\Auth\User;

use App\Services\General\MsegatSmsService;
use App\Services\General\ProccessMethods\ProccessCodesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Models\User;

/**
 * Class ForgotPasswordRequest
 *
 * This request class handles validation logic for the forgot password process.
 * It supports both email and phone number based identification for users.
 */
class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true to allow unauthenticated users to request password reset.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for the forgot password request.
     *
     * Supports validation for email or phone number.
     * Validates country ID if phone number is provided.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Validate email: required if phone_no is not provided, must exist in users table
            'email' => 'required_without:phone_no|email|exists:users,email',

            // Validate country_id: required if phone_no is given and must match user with same phone_no
            'country_id' => [
                'required_with:phone_no',
                'numeric',
                Rule::exists('users', 'country_id')->where(function ($query) {
                    $query->where('phone_no', request('phone_no'));
                }),
            ],

            // Validate phone number: required if email is not provided, must be numeric and exist
            'phone_no' => 'required_without:email|numeric|regex:/^\d+$/|digits_between:7,14|exists:users,phone_no',
        ];
    }

    /**
     * Custom messages for validation errors (currently empty).
     *
     * Can be filled if you want to override default Laravel messages.
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }
}
