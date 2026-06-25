<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Rules\SmallTextRule;
use App\Enums\IsActiveEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\LargeTextRule;
use App\Rules\ImageRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class ShelfRequest
 *
 * This request class handles the validation logic for creating or updating shelfs
 * in the dashboard. It supports dynamic translation fields and status management.
 */
class ShelfRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Shelf::class;

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
        // Get the shelf ID from the route (only used in update requests)
        $shelfId = $this->route('shelf');

        // Define the base validation rules
        $rules = [
            'title' => [
                'required',
                 new UniqueActiveAndNotDeleted('shelves', 'title', $shelfId),
                new SmallTextRule()
            ],

            'slug' => [
                'required',
                 new UniqueActiveAndNotDeleted('shelves', 'slug', $shelfId),
                new SmallTextRule()
            ],

            'description' => ['nullable', new LargeTextRule()],

        ];

        
        // Return the rules along with dynamic translation rules for multilingual fields
        return $this->dynamicTranslationRules(
            array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            ),
            $this->modelClass::getProp('translationFields'),
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
