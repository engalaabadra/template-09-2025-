
# BaseRequest

**Namespace:** `App\Http\Requests`

**Description:**
`BaseRequest` is a base form request class extending Laravel's `FormRequest`. It centralizes and standardizes request validation logic across the application.

It provides:

* JSON-based validation failure responses (instead of redirects).
* Automatic decoding of JSON strings and arrays in request input.
* Normalization of "IDs-like" inputs (strings, arrays, or comma-separated values).
* Dynamic validation rules for multilingual/translatable fields.
* Helper methods to retrieve route models.
* Optional shared `is_active` validation for dashboard routes.

---

## Features

### 1. JSON Validation Handling

Overrides `failedValidation()` to return a consistent JSON response:

```json
{
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 2. Input Normalization

* Automatically decodes JSON strings to arrays.
* Recursively decodes nested JSON strings inside arrays.
* Normalizes IDs-like fields (`"all"`, `"1,2,3"`, `["1","2","3"]`) to a consistent array of integers or `"all"`.

### 3. Multilingual Validation

Supports dynamic translation validation using `dynamicTranslationRules()`:

* Validates main `lang` field against supported system languages.
* Validates each translation item for required fields.
* Ensures uniqueness for translation values via `UniqueTranslationValue` rule.

### 4. Route Model Helpers

* `getRouteModel()` returns the ID of the model bound to the current route.
* `getModelInstance()` returns a new instance of the model class defined in `$modelClass`.

### 5. Shared Dashboard Rules

* Automatically adds an optional `is_active` rule for API dashboard routes:

```php
'is_active' => ['nullable', new Enum(IsActiveEnum::class)],
```

---

## Example Usage

```php
class BannerRequest extends BaseRequest
{
    protected string $modelClass = \App\Models\Banner::class;

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'url'   => ['nullable', 'url'],
        ];

        $translationFields = ['title', 'description'];
        $uniqueFields = ['title'];
        $requiredFields = ['title'];

        return $this->dynamicTranslationRules(
            $rules,
            $translationFields,
            $uniqueFields,
            $requiredFields
        );
    }
}
```

* Automatically decodes JSON strings in `translations`.
* Normalizes any IDs-like fields like `"all"` or `"1,2,3"`.
* Ensures multilingual fields follow validation rules.

---

## Traits Used

* `JsonArrayFieldsHandlerTrait` – Handles decoding and normalization of JSON array fields.

---

## Key Methods

| Method                      | Description                                                 |
| --------------------------- | ----------------------------------------------------------- |
| `failedValidation()`        | Returns JSON response on validation failure.                |
| `prepareForValidation()`    | Decodes JSON, normalizes IDs-like fields before validation. |
| `normalizeIdsValue()`       | Converts various IDs formats to integer arrays or `"all"`.  |
| `dynamicTranslationRules()` | Adds dynamic validation for multilingual fields.            |
| `getRouteModel()`           | Returns the ID of the model from the route.                 |
| `getModelInstance()`        | Returns a new instance of the request’s model class.        |
| `isJsonString()`            | Checks if a string is valid JSON.                           |
| `decodeJsonValue()`         | Decodes JSON strings recursively.                           |

---

This setup allows consistent and reusable request validation logic across all APIs and dashboard endpoints.
