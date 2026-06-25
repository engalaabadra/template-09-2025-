# README: UserResource & BaseResource

## Overview

This document explains the `BaseResource` and `UserResource` classes in your Laravel application. These classes are responsible for transforming Eloquent models into structured JSON responses for your API.

---

## BaseResource

**Namespace:** `App\Resources`

**Extends:** `Illuminate\Http\Resources\Json\JsonResource`

### Purpose

`BaseResource` is a generic resource class that handles:

* Dynamic `_text` attributes for supported columns.
* Optional relation resource transformation.
* Returning plain arrays if the resource is already an array.

### Key Features

1. **Dynamic Text Columns:**

   * Automatically appends `_text` attributes for columns returned by `getDynamicTextColumns()`.
   * Example: `created_at_text`, `is_active_text`.

2. **Relations Handling:**

   * If a property `relationsResources` exists, related resources can be transformed using their respective Resource classes.

3. **Array Fallback:**

   * If the resource is already an array, `toArray()` will return it directly.

### Example Usage

```php
return new BaseResource($model);
```

### How It Works

```php
foreach ($allTextColumns as $column) {
    $textKey = $column . '_text';
    if (!array_key_exists($textKey, $data)) {
        $data[$textKey] = $this->resource->{$textKey};
    }
}
```

* Loops through all dynamic columns.
* Adds a `_text` key to the response if it doesn’t exist.
* Fetches value using the model accessor.

---

## UserResource

**Namespace:** `App\Resources`

**Extends:** `BaseResource`

### Purpose

`UserResource` transforms a `User` model along with its related data such as:

* `profile`
* `roles`
* `files`
* `country`

into a structured API response, including translations and dynamic `_text` attributes.

### Relations Resources Mapping

```php
public array $relationsResources = [
    'roles' => RoleResource::class,
    'profile' => ProfileResource::class,
    'files' => FileResource::class,
    'country' => CountryResource::class,
];
```

* Defines which relations are transformed using which Resource.
* Ensures nested relations are formatted consistently.

### Example API Output

```json
{
  "lang": "ar",
  "email": "employee@example.com",
  "username": "موظف1",
  "translations": [
    {"lang": "en", "username": "employee Name"},
    {"lang": "fr", "username": "Nom de l'enseignant"}
  ],
  "roles": [...],
  "profile": {...}
}
```

### How It Works

```php
$data = parent::toArray($request);
```

* Calls `BaseResource::toArray()` to handle fillable attributes and dynamic `_text` fields.
* Allows further customization for additional fields or relations.

### Notes

* This resource assumes the model has `getDynamicTextColumns()` defined to provide columns for `_text` generation.
* Additional relations can be added to `$relationsResources` to extend the response structure.

---

## Usage in Controllers

```php
return new UserResource(User::with(['roles','profile','files','country'])->find($id));
```

* Automatically transforms the User model along with its relations.
* Returns a clean and structured JSON API response.

---
