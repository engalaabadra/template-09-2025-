<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\Rules\LargeTextRule;
use App\Rules\SmallTextRule;
use App\Rules\EmailRule;
use App\Rules\PhoneNumberRule;
use App\Rules\FilesRule;

/**
 * Class ContactRequest
 *
 * This request class handles the validation logic for storing or updating
 * contact form submissions in the dashboard panel.
 */
class ContactRequest extends BaseRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Define basic validation rules
        $rules = [
            // Name is required
            'name' => [
                'sometimes',
                new SmallTextRule(),
            ],

           // Phone number: optional, must be valid and unique
            'phone_no' => [
                'sometimes',
                new PhoneNumberRule()
            ],

            // Email: optional, must be valid and unique
            'email' => [
                'sometimes',
                new EmailRule()
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

        return $rules;

    }

    /**
     * Custom validation messages (if needed).
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [];
    }
}
