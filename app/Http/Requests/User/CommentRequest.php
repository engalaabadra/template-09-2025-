<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Rules\SmallTextRule;
use App\Enums\IsActiveEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\LargeTextRule;
use App\Rules\UniqueActiveAndNotDeleted;
use App\Rules\FilesRule;

/**
 * Class CommentRequest
 *
 * This request class handles the validation logic for creating or updating shelfs
 * in the dashboard. It supports dynamic translation fields and status management.
 */
class CommentRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Comment::class;

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
        // Get the comment ID from the route (only used in update requests)
        $commentId = $this->route('comment');

        // Define the base validation rules
        $rules = [
            'body' => ['nullable', new LargeTextRule()],
             // Optional files upload must be of the specified MIME types
            'files' => [new FilesRule()],
        ];

        
        // Return the rules along with dynamic translation rules for multilingual fields
        return array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            );
    }

    /**
     * Custom validation error messages (if needed).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
