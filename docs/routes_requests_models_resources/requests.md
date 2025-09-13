# Laravel Requests Documentation

This is explains the purpose and functionality of `BaseRequest` and `UserRequest` classes in the Laravel project. These classes handle request validation, JSON decoding, multilingual support, and dynamic validation rules for models like `Profile`.

---

## 1. BaseRequest

`BaseRequest` extends Laravel's `FormRequest` and adds custom functionality for JSON responses, multilingual validation, and JSON array field handling.

### Features

1. **Custom validation failure response**
   Overrides `failedValidation` to return JSON instead of redirecting:

   ```php
   protected function failedValidation(Validator $validator)
   ```

2. **JSON Array Fields Handling**
   Automatically decodes JSON string inputs (like `translations`, `roles`, `permissions`, `ids`) into PHP arrays for easy usage:

   ```php
   protected function prepareForValidation()
   protected function jsonArrayFields(): array
   ```

3. **Route Model Handling**
   Retrieves a model or numeric ID from the current route:

   ```php
   protected function getRouteModel(string $key = 'id'): ?int
   ```

4. **`is_active` Rule for Dashboard Routes**
   Adds a conditional `is_active` field validation if the request is under `api/dashboard/*`:

   ```php
   protected function getIsActiveRuleIfDashboard(): array
   ```

5. **Dynamic Multilingual/Translation Rules**
   Generates validation rules for translatable fields in any model:

   ```php
   public function dynamicTranslationRules(array $rules, array $translationFields, array $requiredFields = []): array
   ```

6. **Normalize IDs Input**
   Supports multiple formats for `ids` field: `"all"`, comma-separated strings, or arrays:

   ```php
   public function normalizeIds(string $key = 'ids'): void
   ```

---

## 2. UserRequest

`UserRequest` extends `BaseRequest` and validates requests for creating or updating users from the dashboard.

### Features

1. **Model Binding**
   Uses `Profile` model for validation:

   ```php
   protected string $modelClass = \App\Models\Profile::class;
   ```

2. **Authorization**
   Always allows request authorization:

   ```php
   public function authorize(): bool
   ```

3. **Validation Rules**
   Validates user input with the following checks:

   * **Phone Number**

     * Optional
     * Must follow phone format
     * Unique among active and not deleted users
   * **Email**

     * Optional
     * Must follow valid email format
     * Unique among active and not deleted users
   * **Country ID**

     * Required if `phone_no` is provided
   * **Username, Full Name, Nickname**

     * Optional
     * Must comply with `SmallTextRule`
   * **Gender**

     * Optional
     * Must match `ProfileGenderEnum`
   * **Birth Date**

     * Valid date format
     * Must be before today
   * **Roles**

     * Required array
     * Each role must exist and cannot be the super admin role (ID 1)
   * **Images and Files**

     * Validated using `ImageRule` and `FilesRule`
   * **Multilingual Support**

     * Validates `translations` field for all translatable attributes dynamically using `dynamicTranslationRules`

4. **Dynamic Translation Rules**

   * Ensures `lang` is valid against supported system languages.
   * Validates each `translations.*` field with `SmallTextRule` and `UniqueTranslationValue`.
   * Optional or required fields are handled dynamically.

---

### Usage Example

```php
use App\Http\Requests\Dashboard\Auth\UserRequest;

public function store(UserRequest $request)
{
    $validatedData = $request->validated();
    // Proceed with creating the user
}
```

---

### Notes

* Both requests handle JSON-decoded fields automatically(because handling it , via `prepareForValidation` in BaseRequest), allowing controllers or services to work with arrays directly.
* `BaseRequest` centralizes common logic, so child requests like `UserRequest` remain clean and concise.
* `dynamicTranslationRules` makes multilingual validation reusable across different models.
