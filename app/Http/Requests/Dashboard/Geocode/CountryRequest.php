<?php

namespace App\Http\Requests\Dashboard\Geocode;

use App\Rules\SmallTextRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\IsActiveEnum;
use App\Http\Requests\BaseRequest;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class CountryRequest
 *
 * This request class handles validation logic for creating or updating a country.
 */
class CountryRequest extends BaseRequest
{
    /**
     * The model class this request is associated with.
     *
     * @var string
     */
    protected string $modelClass = \App\Models\Country::class;

    /**
     * Get the validation rules that apply to the request.
     *
     * @param Request $request The incoming HTTP request instance
     * @return array<string, mixed> The validation rules
     */
    public function rules(Request $request): array
    {
        // Retrieve the country ID from the route model (used only in update requests)
        $countryId = $this->route('country');

        // Define the validation rules
        $rules = [
            'name'        => [
                'required',
                new UniqueActiveAndNotDeleted('countries', 'name', $countryId),
                new SmallTextRule()
            ],

            'flag'        => ['nullable'],

            'code'        => [
                'required',
                new SmallTextRule()
            ],

            'code2'       => ['nullable'],
            'numcode'     => ['nullable'],
            'phone_code'  => ['nullable']
        ];

        return $rules;

        // Add dynamic translation rules for multilingual fields (this will never be executed because it's after return)
        return $this->dynamicTranslationRules(
            array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            ),
            $this->modelClass::getProp('translationFields'),
            $this->modelClass::getProp('requiredFields')
        );
    }
}
