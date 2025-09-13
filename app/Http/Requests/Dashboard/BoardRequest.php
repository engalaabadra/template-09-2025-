<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\Rules\LargeTextRule;
use App\Rules\ImageRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class BoardRequest
 *
 * This request class handles validation logic for creating or updating
 * a board record in the dashboard. It supports multilingual fields and status control.
 */
class BoardRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Board::class;

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
        // Get the board ID from the route (used during update)
        $boardId = $this->route('board');

        // Define the base validation rules
        $rules = [
            'description' => [
                'required',
                new UniqueActiveAndNotDeleted('boards', 'description', $boardId),
                new LargeTextRule()
            ],

            // Image: must be a valid image 
            'image' => [new ImageRule()],

        ];

        // Return the rules directly (this line is redundant and prevents the next from executing)
        return $rules;

        // This will never be reached due to the return above
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
     * Define custom validation error messages (optional).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
