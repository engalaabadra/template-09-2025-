# TranslationService Usage Guide

This document explains the functionality of the `TranslationService` used to create and update multilingual translations for Laravel models.

---

## Overview

The `TranslationService` provides reusable methods to handle translation logic for models that have a `translations()` relation. It ensures:

* Linking translations to the main record via `translate_id`.
* Excluding non-translatable fields automatically.
* Safe insert or update using `updateOrCreate`.
* Handling multiple languages in bulk.

---

## 1. Creating Translations

**Method:** `createTranslations(object $model, array $translations, object $mainItem)`

**Parameters:**

* `$model`: The model class (e.g., `User::class`).
* `$translations`: An array of translation data (each containing `lang` and translated fields).
* `$mainItem`: The original record (default language).

**Example:**

```php
$data = [
    [
        "lang" => "ar",
        "username" => "يوزر1",
        "full_name" => "يوزر",
        "translate_id" => 90,
        "email" => "student@nnn.5585000",
        "phone_no" => "71115534813410",
        "country_id" => "63",
        "gender" => null,
        "birth_date" => null
    ]
];

$translationService->createTranslations(User::class, $data, $user);
```

**Behavior:**

* Adds `translate_id` to link translations to the main record.
* Copies non-translatable fields (from `excludedFields` property) automatically.
* Creates the translations through the Eloquent relation.

---

## 2. Updating Translations

**Method:** `updateTranslations(object $model, array $translations, object $mainItem)`

**Behavior:**

* Loops through translations.
* Links each translation to the main record via `translate_id`.
* Copies excluded fields from main record.
* Uses `updateOrCreate` to either update existing translations or create new ones.
* Ensures `id` field is excluded to prevent accidental PK updates.

**Example:**

```php
$translationService->updateTranslations(User::class, $data, $user);
```

---

## 3. Handling Translations Dynamically

**Method:** `handleTranslations(object $model, object $mainItem, array|string $translations)`

**Parameters:**

* `$model`: The model class.
* `$mainItem`: The main record.
* `$translations`: Array or JSON string of translation data.

**Behavior:**

* Converts JSON to array if needed.
* Ensures valid array for processing.
* for `'store'` or `'update'`.

**Example:**

```php
$translationService->handleTranslations(User::class, $user, $translationsData);
```

---

## 4. Key Points

* All translations are linked via `translate_id` to the main record.
* Non-translatable fields are automatically copied from the main record.
* `updateOrCreate` ensures no duplicate entries for the same language.
* Can handle both single and bulk translation updates.
* Accepts JSON string or array for flexibility.

---

This service ensures consistency, avoids conflicts, and simplifies handling multilingual translations in Laravel projects.
