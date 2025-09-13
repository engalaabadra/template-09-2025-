# Request Data Preparation

This section explains how the request data is prepared before validation in the Laravel application.  

The functionality is implemented in the `prepareForValidation()` method and its helper methods.

## Features

1. **Automatic JSON decoding**
   - Converts plain JSON strings into PHP arrays.
   - Recursively decodes JSON strings inside arrays.

2. **IDs normalization**
   - Supports multiple input formats for the `ids` field:
     - `"all"`
     - `["all"]`
     - `"1,2,3"` (comma-separated string)
     - `["1,2,3"]` (array with a single comma-separated string)
     - `[1, 2, 3]` (array of integers)

---

## Methods

### `prepareForValidation()`

```php
protected function prepareForValidation(): void
```

- Loops through all request fields.
- Applies special handling for `ids`.
- Decodes JSON values for other fields.

---

### `normalizeIdsValue(mixed $value): mixed`

- Normalizes the `ids` input into a consistent format.
- Converts arrays with comma-separated strings into arrays of integers.
- Cleans and casts values to integers.

**Example Inputs & Outputs:**

| Input | Output |
|-------|--------|
| `"all"` | `"all"` |
| `["all"]` | `"all"` |
| `"1,2,3"` | `[1,2,3]` |
| `["1,2,3"]` | `[1,2,3]` |
| `[1, 2, 3]` | `[1,2,3]` |

---

### `decodeJsonValue(mixed $value): mixed`

- Converts JSON strings into PHP arrays.
- Recursively converts nested JSON strings inside arrays.

**Example 1 – Plain JSON string:**

```php
"settings" => "{\"theme\":\"dark\",\"lang\":\"en\"}"
```

becomes

```php
"settings" => ["theme" => "dark", "lang" => "en"]
```

**Example 2 – Nested JSON strings inside array:**

```php
"translations" => [
    "{\"lang\":\"fr\",\"title\":\"Titre\"}"
]
```

becomes

```php
"translations" => [
    ["lang" => "fr", "title" => "Titre"]
]
```

---

### `isJsonString(string $string): bool`

- Checks whether a string is valid JSON.
- Returns `true` if valid, `false` otherwise.

---

## Summary

- `prepareForValidation()` ensures all request data is ready for validation.
- Automatically handles JSON decoding and IDs normalization.
- Allows flexibility in the request input format, especially for bulk operations.

