<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PaymentStatusEnum;
/**
 * Class OrderRequest
 *
 * This request handles validation for placing an order,
 * ensuring the payment method is valid and provided.
 */
class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to submit this order request
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
            
            // data contents order
            // contents must be an array (if present)
            'contents'     => ['required', 'array'],
            'contents.*'   => ['exists:contents,id'],
            'contents.*.id'      => ['required', 'exists:contents,id'],
            'contents.*.price'   => ['required', 'numeric', 'min:0'],
            'contents.*.quantity'=> ['required', 'integer', 'min:1'],

            //data address
            'shipping_address'   => ['nullable', 'string'],
            'billing_address'    => ['nullable', 'string'],

            //data payment
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'status' => ['nullable', new Enum(PaymentStatusEnum::class)],
            
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
            // Add custom messages here if needed
        ];
    }
}
