<?php

namespace App\Http\Requests\Dashboard;

use App\Http\Requests\BaseRequest;
use App\Rules\SmallTextRule;
use App\Enums\IsActiveEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\LargeTextRule;
use App\Rules\ImageRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class BannerRequest
 *
 * This request class handles the validation logic for creating or updating banners
 * in the dashboard. It supports dynamic translation fields and status management.
 */
class BannerRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Banner::class;

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
        // Get the banner ID from the route (only used in update requests)
        $bannerId = $this->route('banner');

        // Define the base validation rules
        $rules = [
            'title' => [
                'required',
                new UniqueActiveAndNotDeleted('banners', 'title', $bannerId),
                new SmallTextRule()
            ],

            'description' => ['nullable', new LargeTextRule()],

            'url' => [
                'nullable',
                'url',
            ],

            // Image: must be a valid image 
            'image' => [new ImageRule()],

        ];

        
        // Return the rules along with dynamic translation rules for multilingual fields
        return $this->dynamicTranslationRules(
            array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            ),
            $this->modelClass::$translationFields,
            $this->modelClass::$uniqueFields,
            $this->modelClass::$requiredFields
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
