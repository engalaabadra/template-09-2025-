<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\LargeTextRule;
/**
 * Class ReviewRequest
 *
 * This request handles validation for submitting a review by a user.
 * It ensures the presence and format of a numeric rating and allows an optional description.
 */
class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to submit a review
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Description is optional with a max length of 5000 characters
            'description' => ['required', new LargeTextRule()],

            // Rating is required, must be a digit with an optional single decimal (e.g., 4 or 4.5),
            // and must be between 1 and 5
            'rating'      => ['required', 'regex:/^\d(\.\d)?$/', 'between:1,5'],
        ];
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // You can add custom error messages here if needed
        ];
    }
}
