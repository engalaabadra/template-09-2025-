<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Rules\SmallTextRule;
use App\Enums\IsActiveEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\LargeTextRule;
use App\Rules\UniqueActiveAndNotDeleted;
use App\Rules\ImageRule;
use App\Enums\ContentTypeEnum;
use App\Enums\IsFeaturedEnum;
use App\Rules\FilesRule;

/**
 * Class ContentRequest
 *
 * This request class handles the validation logic for creating or updating shelfs
 * in the dashboard. It supports dynamic translation fields and status management.
 */
class ContentRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Content::class;

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
        // Get the content ID from the route (only used in update requests)
        $contentId = $this->route('content');

        // Define the base validation rules
        $rules = [
            // The parent_content_id field is nullable and must exist in the parent_contents table
            'parent_content_id' => ['nullable', 'exists:parent_contents,id'],

            // The category_id field is required and must exist in the categories table
            'category_id' => ['required', 'exists:categories,id'],

            // The shelf_id field is required and must exist in the shelves table
            'shelf_id' => ['required', 'exists:shelves,id'],

            // Add a required 'type' field with enum validation
            // 'type' => ['required', new Enum(ContentTypeEnum::class)],
            'type' => ['nullable'],

            'title' => [
                'required',
                new UniqueActiveAndNotDeleted('contents', 'title', $contentId),
                new SmallTextRule()
            ],

            'slug' => [
                'required',
                new UniqueActiveAndNotDeleted('contents', 'slug', $contentId),
                new SmallTextRule()
            ],

            'description' => ['nullable', new LargeTextRule()],
            'content_text' => ['nullable', new LargeTextRule()],
            'summery' => ['nullable', new LargeTextRule()],
            'chapters' => ['nullable', new LargeTextRule()],

            // Add a nullable 'is_featured' field with enum validation
            'is_featured' => ['nullable', new Enum(IsFeaturedEnum::class)],

             // Optional files upload must be of the specified MIME types
            'files' => [new FilesRule()],
        ];

        
        // Return the rules along with dynamic translation rules for multilingual fields
        return $this->dynamicTranslationRules(
            array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            ),
            $this->modelClass::getProp('translationFields'),
            $this->modelClass::$uniqueFields,
            $this->modelClass::getProp('requiredFields')
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
