<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ActivationActionEnum;
use App\Enums\StrategyActionEnum;
use Illuminate\Validation\Rule;

/**
* Class ActivationActionRequest
* Handle validation for  activation.
 * 
 * Optional `` field accepts: 'activate', 'deactivate', or 'toggle'.
 * Authorization always returns true by default.
 */
class ActivationActionRequest extends FormRequest
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
            // Optional: toggle (default), activate, or deactivate
            'action_activation' => [
                'nullable',
                Rule::in(array_column(ActivationActionEnum::cases(), 'value')),
            ],
            // Optional: modify (default), replace, or prevent
            'strategy' => [
                'nullable',
                Rule::in(array_column(StrategyActionEnum::cases(), 'value')),
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
