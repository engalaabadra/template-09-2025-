<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/**
 * Class UpdatePasswordRequest
 *
 * Handles the validation logic for updating a user's password,
 * including the old password, new password, and confirmation.
 */
class UpdatePasswordRequest extends FormRequest
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
            // Old password is required, must be max 255 characters, and follow default password rules
            'old_password' => ['required', 'max:255', Rules\Password::defaults()],
            
            // New password is required, must be max 255 characters, and follow default password rules
            'new_password' => ['required', 'max:255', Rules\Password::defaults()],
            
            // Confirmation of new password is required, must be max 255 characters, and follow default password rules
            'confirmation_new_password' => ['required', 'max:255', Rules\Password::defaults()],
        ];
    }
}
