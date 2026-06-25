<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\Rules\LargeTextRule;
use App\Rules\FilesRule;

/**
 * Class ChatRequest
 *
 * This request handles validation for creating or updating a chat message,
 * including message content and optional file attachments.
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
            'client_id' => 'required|numeric',

            // The main message body (required and limited to 1000 characters)
            'body'      => ['required', new LargeTextRule()],

            // files: must be a valid files
            'files' => [new FilesRule()]
        ];
    }

    /**
     * Define custom validation error messages (optional).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
