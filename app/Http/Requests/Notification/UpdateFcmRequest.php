<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateFcmRequest
 *
 * Handles validation for updating a user's FCM (Firebase Cloud Messaging) token.
 */
class UpdateFcmRequest extends FormRequest
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
            // FCM token is required for the request
            'fcm_token' => ['required'],
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
