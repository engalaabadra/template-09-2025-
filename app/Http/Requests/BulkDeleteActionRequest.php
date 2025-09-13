<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;
use App\Rules\IdsRule;
use App\Enums\ActivationActionEnum;
use App\Enums\StrategyActionEnum;
use Illuminate\Validation\Rule;

/**
 * Class BulkDeleteActionRequest
 *
 * This form request handles validation for bulk actions (like delete or restore).
 * It ensures that the `ids` field is either the string `"all"` or a non-empty array of integers.
 *
 * Example valid inputs:
 * - { "ids": "all" }
 * - { "ids": [1, 2, 3] }
 *
 * Example invalid inputs:
 * - { "ids": null }
 * - { "ids": [] }
 * - { "ids": ["a", "b"] }
 */
class BulkDeleteActionRequest extends BaseBulkActionRequest
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
         return array_merge(parent::rules(), [ // parent -> BaseBulkActionRequest to take 'ids' to put here in this req.
            //here add another rules
        ]);
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
