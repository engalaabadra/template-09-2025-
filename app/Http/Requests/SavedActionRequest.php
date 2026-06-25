<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\SavedActionEnum;

/**
* Class SaveContentRequest
* Handle validation for  activation.
 * 
 * Optional `` field accepts: 'save', 'unsave', or 'toggle'.
 * Authorization always returns true by default.
 */
class SavedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true, but can be modified to apply permission logic.
     */
    public function authorize(): bool
    {
        return true; // Allow all requests for now; modify if needed for authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Optional: toggle (default), save, or unsave
            'action' => [
                'nullable',
                Rule::in(array_column(SavedActionEnum::cases(), 'value')),
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            
        ];
    }
}
