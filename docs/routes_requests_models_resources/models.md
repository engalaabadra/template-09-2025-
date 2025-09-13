## Models

#### BaseModel

This is a base Eloquent model class that provides common configurations, metadata, and event handling used across your application's models.

---

## Features & Configuration

### Attributes

- Tracks audit fields via polymorphic relationships:
  - `$createdBy`, `$updatedBy`, `$deletedBy` (relations to users who created, updated, or deleted the record).
- Audit columns:
  - `created_by_id`, `created_by_type`
  - `updated_by_id`, `updated_by_type`
  - `deleted_by_id`, `deleted_by_type`
- Appends dynamic accessors like `is_active_text` (disabled by default).
- Supports eager loading relations via `$eagerLoading`.
- Supports translatable fields with `$translationFields` and `$excludedFields`.
- Defines fields for search and export via `$columnsSearch` and `$columnsToExport`.
- Attribute casting (e.g., enums, dates) is predefined in `$casts`.

---

## Event Handling

- **Force Cascade Delete:**  
  On **force delete** (permanent deletion), automatically deletes related models defined in the `$forceCascadeDelete` property of the model, including handling media and pivot table detachments.  
  It skips cascade deletion for soft deletes or excluded models (commented example provided).

- **Soft Deletes & Translations:**  
  Optionally deletes related translations if the model uses `translate_id` and `lang` columns (commented out example included).

- **Caching and Roles:**  
  Loads main roles from config and caches supported languages at boot time.

---

## Usage

Extend your models from `BaseModel` to inherit:

- Common fillable audit fields.
- Auto cascade delete behavior on force delete.
- Support for dynamic attributes, eager loading, and translation support.
- Preconfigured casts for `is_active` and timestamps.

---

## Example

```php
class Post extends BaseModel
{
    protected array $forceCascadeDelete = ['comments', 'tags'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

# BaseModel

`BaseModel` is the foundational Eloquent model class extended by other models in the application. It includes common configuration, casts, event handling, and metadata properties.

---

***Attributes***

| Attribute           | Type             | Description                             |
|---------------------|------------------|-------------------------------------|
| `created_by_id`     | int|null         | ID of the user who created the record |
| `created_by_type`   | string|null      | Class type of the creator (polymorphic) |
| `updated_by_id`     | int|null         | ID of the user who last updated the record |
| `updated_by_type`   | string|null      | Class type of the last updater (polymorphic) |
| `deleted_by_id`     | int|null         | ID of the user who soft deleted the record |
| `deleted_by_type`   | string|null      | Class type of the deleter (polymorphic) |

---

***Accessors***

- `is_active_text` (string|null): Read-only accessor that provides a textual representation of the `is_active` state (optional, currently commented out).

---

***Configuration & Metadata***

| Property                 | Type       | Description                                               |
|--------------------------|------------|-----------------------------------------------------------|
| `$appends`               | array      | List of attribute accessors appended to JSON/array output (default empty) |
| `$eagerLoading`          | array      | List of relations to eager load dynamically (default empty) |
| `static::excludedFields()`| array      | Static list of fields excluded from translation on insert |
| `static::getProp('translationFields')` | array  | Static list of translatable fields for validation         |
| `static::getProp('columnsSearch')` | array      | Static list of columns used in search functionali
```
```
#### Banner Model

The `Banner` model represents banner entities with multilingual support, media relations, and soft delete capabilities. It extends the base functionality provided by `BaseModel`.

---

***Attributes***

| Attribute    | Type      | Description                         |
|--------------|-----------|-------------------------------------|
| `id`         | int       | Primary key                        |
| `lang`       | string    | Language code (e.g., 'en', 'ar')  |
| `translate_id` | int     | Translation group identifier       |
| `title`      | string    | Banner title                      |
| `url`        | string    | Link URL                          |
| `description`| string    | Banner description                |
| `is_active`  | boolean   | Active status                    |

---

***Accessors***

- `$_text` (nullable string): Custom accessor for localized or formatted text (implementation depends on traits or base model).

---

***Relationships***

- `translations` (via `TranslationRelationsTrait` trait): Related translations of this banner.
- `image` (via `HasImageRelationTrait`): Single associated image media.
  
Note: Eager loads both `translations` and `image` by default.

---

***Configuration & Metadata***

- **Fillable:**  
  `id`, `lang`, `translate_id`, `title`, `url`, `description`, `is_active`

- **Appends:**  
  None by default (empty array).

- **Eager Loading:**  
  `translations`, `image`

- **Translation Configuration:**  
  - Excluded fields from translation: `url`  
  - Translatable fields: `title`, `description`  
  - Required fields for translation validation: `title`

- **Searchable Columns:**  
  `title`, `description`, `url`, `created_at`

- **Exportable Columns:**  
  `title`, `description`, `url`, `created_at_text`

- **Force Cascade Delete:**  
  Relations to be deleted when the banner is force deleted: `image`, `translations`

- **Unique Fields for Restore:**  
  `title` (used for conflict checking when restoring soft-deleted models)

- **Casts:**  
  Currently empty, but can be used to cast attributes such as `is_active` (e.g., to an enum).

---

***Traits Used***

- `SoftDeletes`: Enables soft deletion support.
- `HasImageRelationTrait`: Adds image relation and management(via HasMediaTrait).
- `TranslationRelationsTrait`: Handles multilingual translation relations.

---

***Query Builder***

- Uses a custom `BannerBuilder` for query building and scopes.
- Overrides `query()` and `newEloquentBuilder()` to return `BannerBuilder` instances.

---

***Example***

```php
// Get active banners with a specific language
$banners = Banner::query()
    ->isActive(true)
    ->lang('en')
    ->get();

// Access banner's image URL
$imageUrl = $banner->image?->url;

// Access translations
$translations = $banner->translations;
```
or
```
$banners = Banner::query()
    ->filter() // for all filters in bsnner builder (include that in base builder)
    ->get();
```

----------------------------------------

#### AppServiceProvider

The `AppServiceProvider` is a core service provider in your Laravel application responsible for bootstrapping essential services, route bindings, and caching configuration values at application startup.

---

## Overview

- Registers route model bindings.
- Configures custom routing macros for enhanced resource routes.
- Caches role configuration from `spatie_seeder.php` config file.
- Ensures role verification command runs daily (if not running in console).
- Optimizes Vite asset prefetch concurrency.

---