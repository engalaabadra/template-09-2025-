<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\LargeTextRule;
use App\Rules\ImageRule;

/**
 * Class SendNotificationRequest
 *
 * Handles validation for sending a notification.
 * Ensures that title, body, and type are all provided and within character limits.
 */
class SendNotificationRequest extends FormRequest
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
            // Title is required and must not exceed 225 characters
            'title' => ['required', new SmallTextRule()],

            // Body is required and must not exceed 225 characters
            'body'  => ['required', new LargeTextRule()],

            // Type is required and must not exceed 225 characters
            'type'  => ['required', new SmallTextRule()],
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
