# 📝 Translation System Documentation Index

This documentation explains the custom translation system used in this Laravel project. Instead of using external packages like Spatie, this system handles translations via a `lang` column and a `translate_id` reference inside the same model's table.

It is suitable for multi-language content where each translation is saved as a separate row, linked to its original via `translate_id`, and filtered via a global `lang` scope.

---

## 📁 Files Overview

### 1. [`structure.md`](structure.md)

Explains the database structure, including the purpose of `lang` and `translate_id`, and how translations are related to the main record.

### 2. [`requests.md`](requests.md)

Details how the system handles validation for translations using `dynamicTranslationRules()` inside Form Requests.

### 3. [`service.md`](service.md)

Documents the core `TranslationService` class, which handles creating, updating, and storing translations linked to their parent records.

### 4. [`model.md`](model.md)

Describes how translation-related behavior is configured in models using properties like `$translationFields`, `$excludedFields`, and relationships.

### 5. [`scope.md`](scope.md)

Explains the `LanguageScope`, which filters results automatically based on the application's current locale.

### 6. [`examples.md`](examples.md)

Provides practical request and response examples showing how translations are submitted and retrieved.

### 7. [`testing.md`](testing.md)

(Optional) Guidelines for writing tests for translation logic.

---

## 🧠 Why Not Use Spatie or Other Packages?

* You have full control over structure, naming, and behavior.
* Translations live in the same table, making relationships and filtering simpler.
* Better performance in bulk data usage.

However, because this system relies on conventions and service logic, proper documentation is important for new developers.

---

## 📌 Conventions

* Default language records are created first.
* Translations reference the main record via `translate_id`.
* The `lang` column is used for filtering using a global scope.
* Validation logic dynamically accepts and enforces language-specific rules.

---
