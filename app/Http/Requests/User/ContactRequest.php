<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\Rules\LargeTextRule;
use App\Rules\SmallTextRule;
use App\Rules\EmailRule;
use App\Rules\PhoneNumberRule;
use App\Rules\FilesRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class ContactRequest
 *
 * This request handles validation for contact form submissions, including support
 * for optional email or phone number, a required message, and optional files upload.
 */
class ContactRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to submit contact requests
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Define base rules
        $rules = [
            // Name is required
            'name' => [
                'sometimes',
                new SmallTextRule(),
            ],

           // Phone number: optional, must be valid and unique
            'phone_no' => [
                'sometimes',
                new PhoneNumberRule(),
                new UniqueActiveAndNotDeleted('contacts', 'phone_no', $contactId),
            ],

            // Email: optional, must be valid and unique
            'email' => [
                'sometimes',
                new EmailRule(),
            ],

            // Country ID: required if phone number is present, must match phone number
            'country_id' => [
                'required_with:phone_no',
                'numeric',
                Rule::exists('users', 'country_id')->where(function ($query) {
                    $query->where('phone_no', request('phone_no'));
                }),
            ],
            // Message is required with a max length of 5000 characters
            'message'  => ['required', new LargeTextRule()],

            // files: must be a valid files
            'files' => [new FilesRule()],
        ];

        // Apply dynamic translation rules for translatable fields
        return $this->dynamicTranslationRules(
            $rules,
            \App\Models\Contact::getProp('translationFields'),
            ['name', 'message']
        );
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Add custom messages here if needed
        return [];
    }
}
