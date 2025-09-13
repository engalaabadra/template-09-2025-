<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ProfileGenderEnum;
use App\Rules\ImageRule;
use App\Rules\EmailRule;
use App\Rules\PhoneNumberRule;
use App\Rules\SmallTextRule;
use App\Rules\DateFormatCreatedAtRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class UpdateProfileRequest
 *
 * Handles validation logic for updating the user's profile information,
 * such as phone number, email, name, gender, date of birth, and avatar image.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // All authenticated users are allowed to update their profile
        return true;
    }

    /**
     * Define the validation rules for the request data.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Get the currently authenticated user's ID (hardcoded here for demo purposes)
        $userId = userApi()?->id;

        return [
            // Phone number: optional, must be valid and unique
            'phone_no' => [
                'sometimes',
                new PhoneNumberRule(),
                new UniqueActiveAndNotDeleted('users', 'phone_no', $userId),
            ],

            // Email: optional, must be valid and unique
            'email' => [
                'sometimes',
                new EmailRule(),
                new UniqueActiveAndNotDeleted('users', 'phone_no', $userId),
            ],

            // Country ID: required if phone number is present, must match phone number
            'country_id' => [
                'required_with:phone_no',
                'numeric',
                Rule::exists('users', 'country_id')->where(function ($query) {
                    $query->where('phone_no', request('phone_no'));
                }),
            ],

            // Full name: required, must follow small text rule
            'username' => [
                'required',
                new SmallTextRule(),
            ],

            // Full name: optional, must follow small text rule
            'full_name' => [
                'sometimes',
                new SmallTextRule(),
            ],

            // Nickname: nullable, must follow small text rule
            'nick_name' => [
                'nullable',
                new SmallTextRule(),
            ],

            // Gender: nullable, must be a valid enum value
            'gender' => [
                'nullable',
                new Enum(ProfileGenderEnum::class),
            ],

            // Birth date: must be valid format and before today
            'birth_date' => [
                new DateFormatCreatedAtRule(),
                'before:today',
            ],

            // Image: must be a valid avatar file
            'image' => [
                new ImageRule(),
            ],
        ];
    }

    /**
     * Define any custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Custom messages can be added here
        ];
    }
}
