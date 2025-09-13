# Media Relations Traits for Laravel

This package provides a set of **Laravel Eloquent traits** to manage file and image relationships, upload, retrieval, and deletion of media in a polymorphic way.

---

## Traits Overview

***MediaTraits***

### 1. `HasFileRelationTrait`

- Provides a **one-to-one** relation for a single file.
- Filters media by type `'file'`.
- Example usage:

```php
$user = User::find(1);
$file = $user->file; // returns the associated File model
```

### 2. `HasFilesRelationTrait`

- Provides a **one-to-many** relation for multiple files.
- Filters media by type `'file'`.
- Example usage:

```php
$files = $user->files; // returns all associated files
```

### 3. `HasImageRelationTrait`

- Provides a **one-to-one** relation for a single image.
- Filters media by type `'image'`.
- Example usage:

```php
$image = $user->image; // returns the associated image
```

### 4. `HasImagesRelationTrait`

- Provides a **one-to-many** relation for multiple images.
- Filters media by type `'image'`.
- Example usage:

```php
$images = $user->images; // returns all associated images
```

### 5. `HasMediaTrait`

This trait provides **common functionality** for:
- **Upload single media** (image or file) and attach/update related record.
- **Upload multiple media files** and create related records.
- **Delete media by IDs** safely from any media relation (e.g., `images`, `files`).
- **Delete all media** for a given relation.
- **Delete single media** record and its physical file.
- Handles file naming with timestamp suffix and stores files under `uploads/{folder}` on `public` disk.
- Relies on media relationships defined via `MediaRelationsTrait` (e.g., `image()`, `images()`, `file()`, `files()`).

#### Methods

- `uploadSingleMedia(UploadedFile $file, string $type, string $folder): string`  
Upload and attach a single media file. Returns public URL.

- `uploadMultipleMedia(array $files, string $type, string $folder): array`  
Upload multiple media files. Returns array of uploaded items with URLs.

- `deleteSingleMedia(string $relation): void`  
Deletes a single related media item and its file.

- `deleteMediaByIds($ids, string $relation)`  
Delete multiple media by IDs. Supports `'all'` to delete all.

## Example Usage in Model

```php
use App\Models\Traits\Relations\Media\HasImageRelationTrait;
use App\Models\Traits\Relations\Media\HasFilesRelationTrait;
use App\Models\Traits\HasMediaTrait;

class User extends Authenticatable
{
    use BaseModelTrait, HasRoles, HasApiTokens, HasFactory, Notifiable, SoftDeletes,
        HasImageRelationTrait, HasFilesRelationTrait;
}
```

#### Upload a Single Image

```php
$user = User::find(1);
$url = $user->uploadSingleMedia($request->file('avatar'), 'image', 'users');
```

#### Upload Multiple Files

```php
$files = $request->file('documents');
$user->uploadMultipleMedi36a($files, 'file', 'users/documents');
```

#### Delete a Single Media

```php
$user->deleteSingleMedia('image'); // deletes the user's image
```

#### Delete Multiple Media by IDs

```php
$user->deleteMediaByIds([1,2,3], 'files');
$user->deleteMediaByIds('all', 'images'); // deletes all images
```

## Notes

- Ensure your `File` model has `type`, `url`, and polymorphic fields: `fileable_type` and `fileable_id`.
- All media is stored under `public/uploads/{folder}`.
- You must include `HasMediaTrait` in your model for uploading and deletion methods to work, i inclded it in traits relations files like : `HasFilesRelationTrait`

-----------------------------------

### File Upload Handler

This method handles file and image uploads for a given model. It supports both single and multiple file uploads and organizes them into a folder based on the model name.

### Features

* Handles validated request data.
* Supports single file uploads (`file` and `image`).
* Supports multiple file uploads (`files` and `images`).
* Organizes uploads in folders named after the model.

### Method

* **handleFiles(\$request, \$model, \$item)**

  * `$request`: The request object with validated input.
  * `$model`: The model related to the uploaded files.
  * `$item`: The item being processed.

### Usage Example

```php
$this->handleFiles($request, new Banner, $banner);
```

This ensures all uploaded files are properly stored and categorized according to their type and model.

-----------------------------------


#### App\Models\Traits\BaseModelTrait

The `BaseModelTrait` is a collection of reusable Laravel Eloquent traits that enhance models with **common features** and **application-wide behaviors**.  
It is recommended to be used in base model (e.g., `BaseModel`) so all models can inherit its capabilities.

---

##### Included Traits & Features

- **EnumOptionsTrait**
  - Provides utilities for retrieving enum option lists.

- **AutoCastTrait**
 * Enhances Eloquent models by:
  - auto-casts dynamically to the model & Merge $autoCasts (defined in model) with $casts .
  - handle dynamic *_text attributes (formatting it)
  - Remove any columns explicitly excluded from dynamic *_text handling, like 'id_text
  - Caching table columns to optimize schema lookups.

- **MorphModelTriggerTrait**
  - Handles polymorphic model callbacks (useful for logging or related media actions).

- **SmartAttributesTrait**
  - filters fillable attributes, *_text accessors, relations recursively, and input data by $fillable.

- **HasGeneralScopes**
  - applies ActiveScope for User and GeneralScopes for other models.

- **SafePropsTrait**
  - Provides a safe way to access static properties dynamically.

- **ReportableTrait**
  - Adds reporting functionality to models.

- **ForceCascadeDeleteTrait**
  - Automatically deletes related records when deleting a model

---

***Example***

```
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BaseModelTrait;

class BaseModel extends Model
{
    use BaseModelTrait;
}

class Post extends BaseModel
{
    // Post model now has all BaseModelTrait features
}
```


# SafePropsTrait

This section describes the usage of the **SafePropsTrait** and how it integrates with models like `Role` to manage static configuration and metadata safely, like : `$eagerLoading`, `$translationFields`

---

## 1. `SafePropsTrait`

The `SafePropsTrait` provides safe get/set access for **class-level (static) properties**.  
It ensures you don’t run into errors if a static property doesn’t exist.

### Methods

- **`getProp(string $name, $default = [])`**  
  Safely get a static property. Returns `$default` if it doesn’t exist.

- **`setProp(string $name, $value)`**  
  Safely set a static property. Does nothing if it doesn’t exist.

- **`staticProp(string $name, $default = [])`**  
  Backward-compatibility alias for `getProp`.

---

### Example Usage

```php
use App\Models\Traits\SafePropsTrait;

class User extends BaseModel
{
    use SafePropsTrait;

    public static array $columnsToExport = ['id', 'email'];
}
```
### Properties in model

- **`$fillable`** → Defines mass assignable attributes.  
- **`$appends`** → Accessors appended to JSON output.  
- **`$eagerLoading`** → Relations to eager load dynamically.  
- **`$excludedFields`** → Fields excluded from translation.  

### Examples for usage 

```php

// Set static property
User::setProp('columnsToExport', ['id', 'email', 'name']);

// Get static property
$cols = User::getProp('columnsToExport', []); 
// ['id', 'email', 'name']

// Backward-compatible
$cols = User::staticProp('columnsToExport'); 
```

```php
if ($model::getProp('columnsToExport')) {
    Excel::store(
        new ExcelExport($data, $model::getProp('columnsToExport')), 
        $filePath, 
        'public'
    );
}
```

```php
protected function buildBaseQuery($model, $forUser = false)
{
    $query = $model::query();

    return $query
        ->when(
            $model->getProp('eagerLoading'), 
            fn($q) => $q->with($model->getProp('eagerLoading'))
        );
}
```


## Benefits

- Centralized management of static config properties.
- Avoids `undefined property` errors when accessing static properties.
- Allows flexible per-model behavior for eager loading, export, and translation.

-------------------------------------------

# EnumOptionsTrait

The `EnumOptionsTrait` provides a set of helper methods to simplify working with PHP Enums in Laravel applications.

## Features
- Generate standardized option lists for forms and dropdowns.
- Translate enum values into human-readable labels.
- Retrieve enum values, names, and cases easily.
- Convert language codes into collections with IDs, codes, and translated names.
- Fetch random enum cases.

## Methods

### Language Options
- `getLangOptions(array $langs)` → Returns a collection of language options with `id`, `code`, and `name`.

***example***
```php
use App\Traits\EnumOptionsTrait;

$langs = ['ar', 'en'];
$options = YourClass::getLangOptions($langs);

// Result:
// [
//   ['id' => 1, 'code' => 'ar', 'name' => 'العربية'],
//   ['id' => 2, 'code' => 'en', 'name' => 'English']
// ]

```
### Enum Options
- `getOptionsData(?array $items = null)` → Options with enum object as ID and translated name.
- `getOptionsIdNameData()` → Options with enum value as ID and translated name.
- `getOptionsPluckData()` → Array `[value => translatedName]`.
- `getRandomCase()` → Get a random enum case.

### Translation
- `text(?string $locale = null)` → Translate the current enum instance.
- `translate(?string $locale = null)` → Alias for `text()`.
- `getTrans($case = null, $locale = null)` → Translate a specific case or value.

### Values & Names
- `getEnumFromValue($value)` → Find enum case by value.
- `getFileName()` → Get the enum class name without namespace.
- `names()` → Get all enum names.
- `values()` → Get all enum values.
- `getValue()` → Get the value of the current enum instance.
- `array()` → Array `[value => name]`.

## Example

```php
enum IsActiveEnum: int {
    case ACTIVE = 1;
    case NOT_ACTIVE = 0;
}

$options = IsActiveEnum::getOptionsIdNameData();
// Result:
// [
//   ['id' => 1, 'name' => 'Active'],
//   ['id' => 0, 'name' => 'Inactive']
// ]

$random = IsActiveEnum::getRandomCase();
// Example: IsActiveEnum::ACTIVE

$trans = IsActiveEnum::ACTIVE->text();
// "Active"
```
#### Use Cases

Populate dropdowns or filters with enum options.

Translate enum values into human-readable labels.

Work with languages and localization in a standardized format.

---------------------------------------------

# ForceCascadeDeleteTrait

The `ForceCascadeDeleteTrait` ensures that when a model is deleted, its defined related records are also removed automatically.  

- should write in this prop in model : `$forceCascadeDelete` relations that i would delete it when deleting this item, this way to avoid delete all relations this item , because possible i want some realtions from this item i would not delete it.

## Features
- Auto-delete related `HasMany`, `HasOne`, `MorphMany`, and `MorphOne` relations.
- Auto-detach `BelongsToMany` relations.
- Supports custom handling for media relations (`file`, `files`, `image`, `images`).
- Logs skipped or deleted relations for debugging.

## Usage

```php
class Post extends Model {
    use ForceCascadeDeleteTrait;

    // Define relations to cascade delete
    protected array $forceCascadeDelete = ['comments', 'tags'];

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }
}

// Example: deletes related comments and detaches tags
$post->handleForceCascadeDelete($post);
```


#### Notes

Add relation names in $forceCascadeDelete on the model.

Missing or undefined relations are skipped with a log warning.

Media relations (file, files, image, images) are handled with custom delete methods.

-------------------------------------



#### HelpersModelTrait

A reusable trait providing common query builder helper methods for Laravel Eloquent models.  
It includes convenient filters, search, and dynamic conditional where clauses for efficient model querying.

---

## Features

- **Date range filtering** on `created_at` and `updated_at` columns:
  - `$query->createdAtRange('2024-01-01,2024-01-31');`
  - `$query->updatedAtRange('2024-01-01,2024-01-31');`

- **Boolean filtering** on `is_active` column (only if value is not null):
  - `$query->isActive(true);`

- **Language filtering** on `lang` column (only if column exists and value is provided):
  - `$query->lang('ar');`

- **Flexible full-text search** across multiple columns, supporting:
  - Normal columns
  - JSON translatable columns
  - Related model columns via dot notation
  - ID search using prefix `#` (e.g., `#123` searches by id)
  - `$query->search(['name', 'email', 'department.name']);`

- **Smart conditional where** helper:
  - `$query->whereOrWhereIn('status', ['pending', 'approved']);`
  - Handles single value, multiple values, or skips if empty.

- **Status filtering helper**:
  - `$query->filterStatus(['active', 'suspended']);`

- **Generic column filtering** with `whereOrWhereIn`:
  - `$query->columnWhereOrWhereIn('type', ['admin', 'user']);`

- **Relation column filtering** with `whereOrWhereIn`:
  - `$query->relationColumnWhereOrWhereIn('department', 'type', ['main']);`

---

## Example Usage

```php
// Filter posts created in January 2024
Post::query()->createdAtRange('2024-01-01,2024-01-31')->get();

// Get active users only
User::query()->isActive(true)->get();

// Search by name or email (supports translation columns and related model)
User::query()->search(['name', 'email', 'department.name'], 'john')->get();

// Filter orders by status or multiple statuses
Order::query()->filterStatus(['pending', 'completed'])->get();
```

#### MorphModelTriggerTrait

A Laravel trait to automatically handle polymorphic auditing fields (`created_by`, `updated_by`, `deleted_by`) and soft delete pruning.

---

***Features***

- Automatically fills `created_by_id`, `created_by_type`, `updated_by_id`, `updated_by_type`, `deleted_by_id`, and `deleted_by_type` fields on model events.
- Uses polymorphic relations to link these fields to the responsible user or model.
- Supports soft deletes and tracks who deleted the record.
- Implements pruning to permanently delete soft-deleted records older than 30 days.
- Requires columns:  
  `created_by_id`, `created_by_type`,  
  `updated_by_id`, `updated_by_type`,  
  `deleted_by_id`, `deleted_by_type`.
- Authenticated user retrieved from `auth('api')->user()`.

---

***Relationships***

- `createdBy()`: MorphTo relation to the user/model who created the record.
- `updatedBy()`: MorphTo relation to the user/model who last updated the record.
- `deletedBy()`: MorphTo relation to the user/model who soft deleted the record.

---

***Example***

```php
class Post extends Model
{
    use MorphModelTriggerTrait;

    // Define the necessary columns in your migration
    // and the trait automatically fills auditing info on create/update/delete.
}
```
#### ReportableTrait

A Laravel trait to generate flexible aggregated reports with dynamic filtering, joining, and grouping based on a configurable report setup.

---

## Features

- Dynamically builds aggregate queries using filters, joins, and grouping.
- Uses a report configuration method (`getReportConfig`) to define how reports are generated.
- Supports filtering by columns that exist in the model's table.
- Supports joins and additional join conditions.
- Supports eager loading and relation filtering when used with Eloquent models.
- Returns results as a Laravel Collection.

---

## Requirements

- The model using this trait **must implement** a static method, via make `use ReportableTrait`, i put use this in BaseModelTrait to use it in all models:
  ```php
  public static function getReportConfig($model, string $type): array;

----------

# SmartAttributesTrait & BaseResource

This explains how to filter model attributes dynamically and return clean API responses using **traits** and **resources**.

---

### SmartAttributesTrait

The `SmartAttributesTrait` provides methods to filter a model's attributes based on `$fillable`, and also supports:

- Including `id` automatically.
- Supporting dynamically appended accessors (like `*_text`).
- Handling loaded relationships with:
  - Custom Resource classes.
  - Recursive filtering using the same trait.

### Methods

#### `getAttributesInFillable($relationsResources): array`
- Returns attributes filtered by `$fillable`.
- Includes `id` and dynamic `*_text` accessors.
- Processes relations using given Resource classes or recursively.

#### `onlyFillable(array $data): array`
- Filters any given data array by the model's `$fillable`.
- Useful for sanitizing request data before `create`/`update`.

**Example:**
```php
$data = $request->validated();
$clean = User::onlyFillable($data);
User::create($clean);
```

#### BaseResource

The BaseResource extends JsonResource and integrates with SmartAttributesTrait to build clean API responses.

***Features***

Converts model attributes using getAttributesInFillable().

Dynamically appends *_text attributes for:

Dates (created_at, updated_at, etc.).

Status fields (is_active, etc.).

Supports custom relation-to-resource mappings.

***Example***
class UserResource extends BaseResource {
    protected $relationsResources = [
        'roles' => RoleResource::class,
    ];
}

Output Example
{
  "id": 1,
  "name": "John Doe",
  "is_active": 1,
  "is_active_text": "Active",
  "created_at": "2025-09-06 09:49:00",
  "created_at_text": "Sep 06, 2025 09:49 AM",
  "roles": [
    { "id": 1, "name": "Admin" }
  ]
}

***Usage in Store Method*** 

Example of combining onlyFillable with request data in a service/repository method:

protected function store($request, $model)
{
    // Get validated data
    $data = $request->validated();

    // Keep only fillable fields (exclude roles, files, etc.)
    $enteredData = $model::onlyFillable($data);

    // Create model instance safely
    return $model::create($enteredData);
}

***Benefits*** 

## Benefits

- **Centralized Media Management**  
  Easily manage files and images across models using polymorphic relations.

- **Reusable Traits**  
  Plug-and-play traits (`HasFileRelationTrait`, `HasImageRelationTrait`, etc.) reduce boilerplate code.

- **Consistent Upload & Delete Handling**  
  Unified methods for uploading single/multiple files, deleting by ID, or wiping relations.

- **Clean API Responses**  
  With `SmartAttributesTrait` and `BaseResource`, only safe, fillable, and dynamic `_text` fields are exposed.

- **Automatic Casting & Formatting**  
  `AutoCastTrait` ensures enums, arrays, and dates are automatically cast and formatted with `_text` accessors.

- **Scoped Queries**  
  `HasGeneralScopes` applies default filters (e.g., `is_active`, language) without extra query code.

- **Safe Static Properties**  
  `SafePropsTrait` allows safe access and overrides of static props (`$eagerLoading`, `$translationFields`) per model.

- **Flexible Enum Support**  
  `EnumOptionsTrait` makes it easy to generate dropdowns, translations, and options for enums.

- **Cascade Delete Control**  
  `ForceCascadeDeleteTrait` lets you selectively delete related records to avoid orphaned data.

- **Audit & Ownership Tracking**  
  `MorphModelTriggerTrait` automatically records who created, updated, or deleted a record.

- **Dynamic Reports**  
  `ReportableTrait` enables building aggregated reports without manual SQL queries.

- **Query Helpers**  
  `HelpersModelTrait` adds reusable filters (date ranges, status, full-text search, etc.) to all models.

- **Security & Cleanliness**  
  Traits collectively ensure data integrity, reduce duplication, and enforce best practices.
