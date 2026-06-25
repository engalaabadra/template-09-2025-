### Dynamic Validation for Translations

Validation is handled in Form Requests by including a helper method from BaseRequest.php:
`dynamicTranslationRules`
```
public function dynamicTranslationRules($rules, $translationFields)
```
This method:

Adds a rule to validate the lang field.

Automatically validates each translated field using a translations array from the request.

The `dynamicTranslationRules` method helps add multilingual validation rules for translatable fields in your form request.

**What it does:**

- Validates the main `lang` field against allowed system languages.
- Ensures `translations` input is an array of translation items.
- Validates each `translations.*.lang` field to be required, distinct, and within allowed languages.
- Retrieves the current model from the route (if any) to properly ignore the current record in uniqueness checks.
- Adds validation rules for each translatable field:
  - Required or nullable based on input.
  - Must be a string with max length 255.
  - Applies a custom `SmallTextRule`.
  - Applies a custom `UniqueTranslationValue` rule to ensure uniqueness per language.

**Parameters:**

- `$rules` — Base validation rules array.
- `$translationFields` — List of fields inside translations that require validation.
- `$requiredFields` — Optional list of fields that should be marked required.

**Returns:**

- The merged validation rules array including multilingual validation.

---

**Example Usage:**

```php
public function rules()
{
    $baseRules = ['email' => ['required', 'email']];

    return $this->dynamicTranslationRules($baseRules, ['title', 'description'], ['title']);
}
```

# UniqueTranslationValue Validation Rule

This custom validation rule ensures that a translated value is **unique per language** across a database table. It is useful in multilingual applications where translations are stored with a `lang` and `translate_id` structure.

---

## Purpose

- Prevent duplicate values in the **same language** across different records.
- Allow the same value in different languages.
- Allow the same value for the current record when updating.
- Support ignoring the current record by `ignoreId` during updates.

---

## How It Works

- Extracts the language code (`lang`) from the attribute name, e.g. `translations.0.title`.
- Checks the database table for existing records where:
  - The `lang` matches the extracted language.
  - The value in the specified column matches the input value.
  - The `translate_id` is not the current record's ID (`ignoreId`).
- Returns `true` if no duplicates found; otherwise `false`.

---

## Constructor Parameters

| Parameter      | Description                                        |
|----------------|--------------------------------------------------|
| `$table`       | Database table to check for uniqueness            |
| `$column`      | Column name to check uniqueness on                 |
| `$ignoreId`    | (Optional) ID to ignore (usually current record)  |
| `$translateId` | (Optional) Translation group ID to allow duplicates within the same group |

---

## Usage Example

```php
use App\Rules\UniqueTranslationValue;

public function rules()
{
    return [
        'translations.*.title' => [
            'required',
            new UniqueTranslationValue('posts', 'title', $this->post?->id),
        ],
    ];
}
