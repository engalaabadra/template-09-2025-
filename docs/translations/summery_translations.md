# Translation System README

## Overview

custom translation system used in the Laravel project. It allows multi-language content handling without external packages. Each translation is stored as a separate row in the same table and linked via `translate_id` to the original record.

---

## Key Concepts

* **`lang`**: Column indicating the language of the record.
* **`translate_id`**: References the ID of the original record for which this record is a translation. Null for the original language version.
* **Default language**: Created first; translations link to it.
* **Global scope (`LanguageScope`)**: Automatically filters results to the current locale.
* **Validation**: Dynamic rules enforce language-specific constraints.

---
### Request `validation translations item (lang, translationsFields) via ***dynamicTranslationRules*** in BaseRequest & unique translationsFields via ***UniqueTranslationValue***` -> not allowed store same a value in ((((same lang)))) in table

### Request Examples

#### Store Request

```json
{
  "lang": "ar",
  "title": "عنوان رئيسي",
  "url": "https://example.com",
  "translations": [
    {"lang": "en", "title": "Main Title"},
    {"lang": "fr", "title": "Titre Principal"}
  ]
}
```

#### Update Request (id: 1)

```json
{
  "lang": "ar",
  "title": "عنوان رئيسي",
  "url": "https://example.com",
  "translations": [
    {"translate_id": 1, "lang": "en", "title": "Main Title"},
    {"translate_id": 1, "lang": "fr", "title": "Titre Principal"}
  ]
}
```

## Validation

* **DynamicTranslationRules** in Base Requests:

  * Validates main `lang` field.
  * Validates each `translations.*.lang` and translatable fields.
  * Ensures not allowed store same a value in ((((same lang)))) in table (uniqueness per language) using `UniqueTranslationValue`.

* **UniqueTranslationValue**

  * Ensures a translated value is unique per language.
  * Allows same value for different languages.
  * Supports ignoring current record during updates.

---
## TranslationService `creation, update trnslations item`

Handles creation, update, and storage of translations.

### Methods

1. **createTranslations(\$model, \$translations, \$mainItem)**

   * Links each translation to the main item.
   * Copies excluded fields.
   * Creates translations via relation.

2. **updateTranslations(\$model, \$translations, \$mainItem)**

   * Updates or creates translations safely.
   * Excludes `id` field to avoid PK conflict.

3. **handleTranslations(\$model, \$mainItem, \$translations)**

   * Dynamically handles `store` or `update` operations.
   * Accepts array or JSON string.

---

## Model Configuration `translationFields, excludedFields`

```php
public static $translationFields = ['title', 'description'];
public static $excludedFields = ['url'];
```

* Include `TranslationRelationsTrait` to add `translations()` and `original()` relationships.

---

### Database Example

```
id lang translate_id title
1  ar   NULL         "عنوان رئيسي"
2  en   1            "Main Title"
3  fr   1            "Titre Principal"
```

---

## Advantages

* Full control over structure and behavior.
* Same-table storage simplifies relationships and queries.
* Supports bulk operations efficiently.
* Works with JSON or array input.
* Avoids conflicts using `translate_id` linkage.

---

## Conventions

* Default language saved first.
* Translations reference main record via `translate_id`.
* Non-translatable fields(exludedFields) copied automatically.
* `updateOrCreate` ensures no duplicate entries for the same language.

---

## Example Usage

```php
$translationService->handleTranslations(User::class, $user, $translationsData);
```

This service ensures consistency and simplifies handling multilingual translations in Laravel projects.
