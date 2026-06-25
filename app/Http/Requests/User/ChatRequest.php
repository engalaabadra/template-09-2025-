<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\Rules\LargeTextRule;
use App\Rules\FilesRule;

/**
 * Class ChatRequest
 *
 * This request handles validation for creating or updating a chat message,
 * including text content and optional file attachments.
 */
class ChatRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to perform this request
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
            'client_id' => 'required|numeric|exists:users,id',

            // The message body is optional, must be a string with a max length of 5000 characters,
            'body' => ['nullable', new LargeTextRule()],

            // Optional files upload must be of the specified MIME types
            'files' => [new FilesRule()],
        ];
    }

    /**
     * Get the custom validation messages for this request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Add custom error messages here if needed
        ];
    }
}
