<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\StrategyActionEnum;
use Illuminate\Validation\Rule;

/**
* Class RestoreActionRequest
* Handle validation for  activation.
 * 
 * Optional `` field accepts: modify (default), replace, or prevent.
 * Authorization always returns true by default.
 */
class RestoreActionRequest extends FormRequest
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
