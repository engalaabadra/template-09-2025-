<?php

namespace App\Http\Requests\Auth\User;

use App\Services\General\ProcessCodeMethods\ProccessCodesService;
use App\Services\General\SendingMessageMethods\SendingMessagesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

/**
 * Class RegisterRequest
 *
 * This request handles the validation logic for user registration.
 */
class RegisterRequest extends FormRequest
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
     * Define the validation rules that apply to the registration request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Email is required if phone number is not provided, must be valid and unique in users table
            'email'      => ['nullable', 'required_without:phone_no', 'email', 'unique:users,email'],

            // Country ID is required when phone number is present, must be numeric
            'country_id' => ['required_with:phone_no', 'numeric'],

            // Phone number is required if email is not provided, must be numeric and unique
            'phone_no'   => ['nullable', 'required_without:email', 'numeric', 'regex:/^\d+$/', 'digits_between:7,14', 'unique:users,phone_no'],

            // Password is required and must meet default password rule requirements
            'password'   => ['required', Rules\Password::defaults()],

            // FCM token is optional but may be included
            'fcm_token'  => ['sometimes'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * Empty for now, but can be filled in if needed.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom messages can be added here if needed
        ];
    }
}
