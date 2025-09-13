# SmartAttributesTrait

## Overview

`SmartAttributesTrait` is a Laravel Eloquent trait designed to automatically handle attribute casting, formatting, and recursive relation serialization for models.

### Features

1. **Automatic Casts**

   * Automatically applies casts defined in the `$autoCasts` property.
   * Merges model-defined `$casts` with `$autoCasts`.
   * Only applies casts for existing database columns.

2. \**Dynamic *\_text Attributes**

   * Adds `_text` attributes for enums and date/datetime columns when converting a model to an array.
   * Enums implement `BackedEnum` or `HasTextRepresentation`.
   * DateTime objects are formatted to human-readable strings.

3. **Smart Attributes Serialization**

   * `getSmartAttributes()` returns:

     * Fillable attributes plus the `id`.
     * Dynamic `_text` representations.
     * Related models recursively.
     * Optional mapping to Resource classes.

4. **Date Formatting Helpers**

   * `formatDateTime()` returns short, human-readable formats.
   * `formatFullDateTime()` returns a full date-time with hours, minutes, and AM/PM.
   * `translateDate()` localizes AM/PM markers for supported locales (English `en` and Arabic `ar`).

5. **Column Existence Check**

   * `hasColumn()` checks if a column exists in the model's database table with a static cache to avoid repeated schema queries.

### Properties

* **protected array \$autoCasts**

  ```php
  protected array $autoCasts = [
      'is_active' => \App\Enums\IsActiveEnum::class,
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'deleted_at' => 'datetime',
  ];
  ```

  Auto-defined casts applied to the model.

### Methods

* **bootSmartAttributesTrait()**
  Hooks into Eloquent lifecycle events (`retrieved`, `creating`, `updating`) to apply auto casts.

* **initializeSmartAttributesTrait()**
  Initializes the trait and applies auto casts.

* **applyAutoCasts()**
  Merges model `$casts` with `$autoCasts` and applies them if the column exists.

* **toArray()**
  Returns the model as an array with `_text` attributes.

* **getAttribute(\$key)**
  Resolves dynamic `_text` attributes for enums, DateTime, or objects implementing `HasTextRepresentation`.

* **getSmartAttributes(array \$relationsResources = \[])**
  Returns model attributes plus relations recursively, optionally mapping to Resource classes.

* **hasColumn(string \$column): bool**
  Checks if the database table has the given column.

* **formatDateTime(\$date): ?string**
  Formats a date/datetime to a short human-readable format.

* **formatFullDateTime(\$date): ?string**
  Formats a date/datetime to a full string with hours, minutes, and AM/PM.

* **translateDate(string \$formattedDateTime): string**
  Translates AM/PM markers based on the current locale.

### Usage Example

```php
use App\Models\Traits\Accessors\SmartAttributesTrait;

class Post extends Model
{
    use SmartAttributesTrait;

    protected $fillable = ['title', 'content', 'is_active'];
}

$post = Post::find(1);
$data = $post->getSmartAttributes();
print_r($data);
```

This will output the model attributes with `_text` representations and nested relations formatted properly.
---------------------
# FillableTrait

`FillableTrait` is a Laravel trait that filters input arrays to include only the model's `$fillable` attributes, preventing mass assignment issues. It provides a static method `onlyFillable()` for easy usage before storing or updating models.

***example***
```php
Banner::onlyFillable($request->validated());
```
