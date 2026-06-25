# UniqueTranslationValue Validation Rule

`UniqueTranslationValue` is a custom Laravel validation rule designed to ensure that a given value is **unique per language** within a database table. This is especially useful when working with multilingual data where translations are stored in the same table but differentiated by a `lang` column and grouped by a `translate_id`.

---

## Purpose

When saving multilingual content, you often need to enforce uniqueness of a certain column (e.g., a translated title) **within each language** independently. This rule checks that the value does not already exist in the specified language, preventing duplicate translated values in the same language across different records.

---

## How It Works

- Checks if the value exists in the specified database table, within the same language (`lang` column).
- Optionally ignores a record by its ID when updating (to allow the same value on the current record).
- Does **not** allow duplicates within the same language, even across different translation groups (`translate_id`).
- Extracts the language dynamically from the input attribute, supporting nested translation arrays (e.g., `translations.0.title`).
- Handles cases where translation data may come as JSON-encoded string or array.

---

## Constructor Parameters

| Parameter     | Type       | Description                                 |
|---------------|------------|---------------------------------------------|
| `$table`      | `string`   | Database table to check uniqueness against. |
| `$column`     | `string`   | Column name to validate uniqueness for.     |
| `$ignoreId`   | `int|null` | Optional record ID to exclude from check (useful for updates). |
| `$translateId`| `int|null` | Optional translation group ID (not used in uniqueness check here). |

---

## Usage Example

```php
use App\Rules\UniqueTranslationValue;

$request->validate([
    'translations.*.title' => [
        'required',
        new UniqueTranslationValue('your_table_name', 'title', $ignoreId = null),
    ],
]);

```
# UniqueWithoutSoftDeletes Validation Rule

`UniqueWithoutSoftDeletes` is a custom Laravel validation rule that ensures a database column's value is unique **only among non-soft-deleted records**. This means it ignores records that have a non-null `deleted_at` timestamp, commonly used for soft deletes.

---

## Purpose

By default, Laravel's `unique` validation rule considers all records, including those soft-deleted. This custom rule modifies that behavior to exclude soft-deleted records (`deleted_at` is NOT NULL) from the uniqueness check.

This is useful when you want to allow "reusing" unique values from records that have been soft deleted, but still prevent duplicates among active records.

---

## Features

- Checks uniqueness for a specified table and column.
- Ignores soft-deleted records (`deleted_at` is NOT NULL).
- Optionally ignores a specific record by ID (useful when updating).
- Supports specifying the ID column name (default is `id`).
- Uses Laravel's built-in validation infrastructure internally for consistency.

---

## Constructor Parameters

| Parameter     | Type       | Description                                  |
|---------------|------------|----------------------------------------------|
| `$table`      | `string`   | The database table to validate against.      |
| `$column`     | `string`   | The column to check uniqueness for (defaults to attribute name). |
| `$ignoreId`   | `int|null` | Optional record ID to exclude from the check (for updates). |
| `$idColumn`   | `string`   | The ID column name (default: `id`).          |

---

## Usage Example

```php
use App\Rules\UniqueWithoutSoftDeletes;

$request->validate([
    'email' => [
        'required',
        new UniqueWithoutSoftDeletes('users', 'email', $ignoreId = $user->id),
    ],
]);
```
# SmallTextRule Validation Rule

`SmallTextRule` is a custom Laravel validation rule that ensures a given string is between **2 and 100 characters** in length. This rule is ideal for validating small text inputs such as names, titles, or short descriptions.

---

## Purpose

This rule simplifies the validation of small text fields by encapsulating the common constraints of minimum and maximum length (2 to 100 characters) into a reusable validation class.

---

## Features

- Validates that a string's length is at least 2 characters.
- Validates that a string's length does not exceed 100 characters.
- Uses Laravel's internal Validator for validation logic.
- Provides clear validation failure messages.
- Can be used anywhere Laravel validation rules are accepted.

---

## Usage Example

In a Form Request or controller validation:

```php
use App\Rules\SmallTextRule;

$request->validate([
    'title' => ['required', new SmallTextRule()],
    'name' => ['nullable', new SmallTextRule()],
]);
```
