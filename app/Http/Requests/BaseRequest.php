<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Rules\SmallTextRule;
use App\Rules\UniqueTranslationValue;
use App\Enums\IsActiveEnum;
use Illuminate\Validation\Rules\Enum;

/**
 * Class BaseRequest
 *
 * This base form request extends Laravel's FormRequest and adds:
 * - Custom validation failure response (JSON).
 * - JSON decoding for 'translations' input.
 * - Helper methods to get model from route.
 * - Dynamic validation rules for multilingual fields.
 * 
 * A base request class to handle shared validation logic across all form requests.
 * It preserves the original rules() method in child classes and allows merging
 * additional rules (like 'is_active') without requiring any changes in child requests.
 * 
 */
class BaseRequest extends FormRequest
{

    protected string $modelClass;

    /**
     * Get the model class name passed from child request.
     */
    protected function getModelInstance(): \Illuminate\Database\Eloquent\Model
    {
        return new $this->modelClass;
    }
   
    /**
     * Conditionally add shared 'is_active' rule if the request is under a dashboard route.
     *
     * @return array
     */
    protected function getIsActiveRuleIfDashboard(): array
    {
        // Check if the request is targeting a api/dashboard route
        if (request()->is('api/dashboard/*')) {
            return [
                // Add a nullable 'is_active' field with enum validation
                'is_active' => ['nullable', new Enum(IsActiveEnum::class)],
            ];
        }

        // Return empty array if no additional rules are needed
        return [];
    }

   
    /**
     * Override default validation failure response to return JSON instead of redirect.
     *
     * @param  Validator  $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }

     /**
     * Prepare request data before validation.
     *
     * - Automatically decode JSON strings into PHP arrays.
     * - Recursively decode JSON strings inside arrays.
     * - Normalize any "IDs-like" fields to support multiple input formats.
     */
    protected function prepareForValidation(): void
    {
        // ================== Normalization ==================
        foreach ($this->all() as $field => $value) {

            // 1. If the field value is a JSON string → decode it
            if (is_string($value) && $this->isJsonString($value)) {
                $decoded = json_decode($value, true);
                $this->merge([$field => $decoded]);
                $value = $decoded; // update value after decoding
            }

            // 2. If the field is an array of objects → leave it as is
            if (is_array($value) && !empty($value) && is_array($value[0])) {
                continue;
            }

            // 3. If the field is IDs-like → apply normalization
            if ($this->isIdsLike($value)) {
                $this->merge([$field => $this->normalizeIdsValue($value)]);
                continue;
            }

            // 4. If the field is an array containing JSON strings → decode each element
            if (is_array($value)) {
                foreach ($value as &$subValue) {
                    if (is_string($subValue) && $this->isJsonString($subValue)) {
                        $subValue = json_decode($subValue, true);
                    }
                }
                $this->merge([$field => $value]);
            }
        }
    }


    /**
     * Normalize any IDs-like input to a consistent array of integers or "all".
     *
     * Supported cases:
     * 1. "all" → returns "all"
     * 2. ["all"] → returns "all"
     * 3. [1, 2, 3] → returns as integers
     * 4. ["1", "2", "3"] → converts to integers
     * 5. "1,2,3" → splits by comma and converts to integers
     * 6. ["1,2,3"] → splits first element by comma and converts to integers
     * 7. single integer or numeric string → returns as integer
     * 8. any invalid string (not "all") → ignored / returns null if single value
     */
    protected function normalizeIdsValue(mixed $value): mixed
    {
        // Case 1: exact string "all"
        if ($value === 'all') {
            return 'all';
        }

        // Case 2: ["all"]
        if (is_array($value) && count($value) === 1 && $value[0] === 'all') {
            return 'all';
        }

        // Case 6: array with one string containing comma, e.g. ["1,2,3"]
        if (is_array($value)) {
            if (count($value) === 1 && is_string($value[0]) && str_contains($value[0], ',')) {
                $value = explode(',', $value[0]); // split string to array
            }

            // Case 3 & 4: array of numbers or numeric strings → convert to integers
            // Ignore any non-numeric values automatically
            return array_map(
                'intval',
                array_filter($value, fn($id) => is_numeric($id))
            );
        }

        // Case 5: string with commas, e.g. "1,2,3"
        if (is_string($value) && str_contains($value, ',')) {
            $ids = explode(',', $value); // split string to array
            return array_map('intval', array_filter($ids, fn($id) => is_numeric($id)));
        }

        // Case 7: single numeric string or integer
        if (is_numeric($value)) {
            return intval($value);
        }

        // Case 8: any other invalid value (e.g., non-numeric string) → return null
        return null;
    }


    /**
     * Check if the given value is "IDs-like" (needs normalization).
     */
   protected function isIdsLike(mixed $value): bool
    {
        // "all" string or ["all"] array
        if ($value === 'all') return true;
        if (is_array($value) && count($value) === 1 && $value[0] === 'all') return true;

        // Any string containing a comma, e.g., "1,2,3"
        if (is_string($value) && str_contains($value, ',')) return true;

        // Array with a single string containing commas, e.g., ["1,2,3"]
        if (is_array($value) && count($value) === 1 && is_string($value[0]) && str_contains($value[0], ',')) return true;

        // Array consisting entirely of integers (numeric strings allowed)
        if (is_array($value) && $this->allIntegers($value)) return true;

        // Single numeric value
        if (is_numeric($value)) return true;

        // Anything else is not considered "IDs-like"
        return false;
    }


    /**
     * Check if all items in an array are integers (numeric strings allowed)
     */
    protected function allIntegers(array $items): bool
    {
        foreach ($items as $item) {
            if (!is_numeric($item) || intval($item) != $item) return false;
        }
        return true;
    }

    /**
     * Decode JSON values if needed.
     *
     * Handles:
     * - Plain JSON strings → array
     * - Nested JSON strings inside arrays
     */
    protected function decodeJsonValue(mixed $value): mixed
    {

        // Convert plain JSON strings → array
        // Example 1: Convert plain JSON string → array
        // Input: "settings" => "{\"theme\":\"dark\",\"lang\":\"en\"}"
        // Output: "settings" => ["theme" => "dark", "lang" => "en"]
        if (is_string($value) && $this->isJsonString($value)) {
            return json_decode($value, true);
        }

        // Convert nested JSON strings inside arrays → array           
        //  Example 2: Convert nested JSON strings inside arrays → array
        // Input: "translations" => [
        //    "{\"lang\":\"fr\",\"title\":\"Titre\"}"
        // ]
        // Output: "translations" => [
        //    ["lang" => "fr", "title" => "Titre"]
        // ]
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                if (is_string($subValue) && $this->isJsonString($subValue)) {
                    $subValue = json_decode($subValue, true);
                }
            }
        }

        return $value;
    }

     /**
     * Check if a given string is valid JSON.
     *
     * @param  string  $string  Input string to validate
     * @return bool             True if valid JSON, false otherwise
     *
     * Example:
     *   isJsonString('{"name":"Alaa"}') → true
     *   isJsonString('hello') → false
     */
    protected function isJsonString(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Add dynamic validation rules for multilingual/translatable fields.
     *
     * This method ensures:
     * - The main `lang` field is validated against allowed system languages.
     * - Each `translations` array item is validated per field and language.
     *
     * @param  array  $rules             Base validation rules.
     * @param  array  $translationFields List of fields that require translation.
     * @param  array  $requiredFields    Optional list of required fields.
     * @return array Merged validation rules with multilingual translation field validation.
     */

    protected function dynamicTranslationRules(
        array $rules,
        array $translationFields,
        array $uniqueFields,
        array $requiredFields = [],
        $privateRoute = null
    ): array {
        $modelInstance = $this->getModelInstance();

        // 1. Main lang
        $rules['lang'] = [
            'string',
            'in:' . implode(',', supportedLanguages()),
        ];

        // 2. translations must be array
        $rules['translations'] = ['nullable', 'array'];

        // 3. lang inside each translation
        $rules['translations.*.lang'] = [
            'required',
            'string',
            'distinct',
            'in:' . implode(',', supportedLanguages()),
        ];

        // 4. handle unique (ignoreId for update cases)
        $modelName   = rtrim(modelName($this->modelClass), 's');
        $tableName   = (new $modelInstance)?->getTable();
        $ignoreId    = null;
        $translateId = null;

        if ($privateRoute) {
            $ignoreId   = is_numeric($privateRoute) ? $privateRoute : $privateRoute->id;
            $translateId = $ignoreId;
        } else {
            $item = $this->getRouteModel($modelName);
            if ($item) {
                $ignoreId   = is_numeric($item) ? $item : $item->id;
                $translateId = $ignoreId;
            }
        }

        // 5. translation fields rules
        foreach ($translationFields as $field) {
            $isRequired = in_array($field, $requiredFields);

            $fieldRules = [
                $isRequired ? 'required' : 'nullable',
                'string',
                'max:255',
                new SmallTextRule(),
            ];

            if (in_array($field, $uniqueFields)) {
                $fieldRules[] = new UniqueTranslationValue($tableName, $field, $ignoreId, $translateId);
            }

            $rules["translations.*.$field"] = $fieldRules;
        }

        return $rules;
    }

    protected function getRouteModel(): ?int
    {
        $routeParams = $this->route()->parameters(); // all params route

        if (empty($routeParams)) {
            return null;
        }

        $routeItem = reset($routeParams); // the forst param

        if ($routeItem instanceof \Illuminate\Database\Eloquent\Model) {
            return $routeItem->getKey();
        }

        if (is_numeric($routeItem)) {
            return (int) $routeItem;
        }

        return null;
    }

}