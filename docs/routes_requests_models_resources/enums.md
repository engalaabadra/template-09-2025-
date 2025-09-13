#### App\Enums\IsActiveEnum

 * This enum defines two possible states for an entity: ACTIVE and NOT_ACTIVE.
 * It uses the EnumOptionsTrait to provide helper methods like getting all values/options.
 * The `text()` method returns a translated label for display in the UI.
 *
 *** Example: ***
  ```
  use App\Enums\IsActiveEnum;
 // 1. All options with enum objects as IDs
IsActiveEnum::getOptionsData();
/*
[
   ['id' => IsActiveEnum::ACTIVE, 'name' => 'Active'],
   ['id' => IsActiveEnum::NOT_ACTIVE, 'name' => 'Inactive'],
]
*/

// 2. All options with raw values as IDs
IsActiveEnum::getOptionsIdNameData();
/*
[
   ['id' => 1, 'name' => 'Active'],
   ['id' => 0, 'name' => 'Inactive'],
]
*/

// 3. Pluck-style options
IsActiveEnum::getOptionsPluckData();
// [1 => 'Active', 0 => 'Inactive']

// 4. Translate directly
IsActiveEnum::getTrans(IsActiveEnum::ACTIVE); // "Active"
IsActiveEnum::getTrans(1, 'ar'); // "نشط"

// 5. Get a random case
IsActiveEnum::getRandomCase(); // e.g., IsActiveEnum::NOT_ACTIVE
  ```
 
#### App\Enums\ServiceResponseEnum

Represents standardized service response types for API or service layers.  
Each enum case contains:
- **A string value** representing the response type.
- **A localized message** retrieved from the `service_responses.*` translation file.
- **A corresponding HTTP status code**.

This enum is useful for:
- Returning consistent responses in APIs.
- Avoiding hardcoded status codes and messages throughout the codebase.
- Centralizing message localization.

---

**Example Usage:**

```php
use App\Enums\ServiceResponseEnum;

// Example: Successful response
$response = ServiceResponseEnum::SUCCESS;

echo $response->message(); // e.g., "Operation completed successfully"
echo $response->status();  // 200

// Example: Not found response
$response = ServiceResponseEnum::NOT_FOUND;

echo $response->message(); // e.g., "The requested resource was not found"
echo $response->status();  // 404

```

## Filters
come here ehen write : query->filter() , this go into filter() method that in UseFilter trait in base builder 
will call filters() that it filled from class builder this model , like UserBuilder :
 public function filters(): array
    {
        return array_merge(
            parent::filters(), // call filters() from BaseBuilder
            [
                //add your filters this model
                
            ]
        );
    }
    these filters in parent in base builder and another for this class

BaseBuilder:
```
public function filters(): array
    {
        return [
            // Apply filter by active status using the isActive() scope
            new ActiveFilter(fn ($value) => $this->isActive($value)),

            // Apply filter by language using the lang() scope
            new LangFilter(fn ($value) => $this->lang($value)),

            // Apply filter by creation date range using the createdAtRange() scope
            new CreatedAtDateRangeFilter(fn ($date) => $this->createdAtRange($date)),
        ];
    }
```
ActiveFilter this will go into this file that contain getData() -> this contain on data for dropdown options in front to show it 
//[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ]

and in callback this in backenkend  : excute filter is_active in col. in db
```
final public function isActive(?bool $active = null): static
    {
        return $this->when($active !== null, function (Builder $q) use ($active) {
            $q->where('is_active', $active);
        });
    }
```


#### App\Models\Filters\ActiveFilter
The `ActiveFilter` is a **reusable query filter** designed to filter records based on their `is_active` status.  
It is often used in admin dashboards, API endpoints, or data tables to quickly toggle between active, inactive, or all records.

#### Key Features
- **Dropdown filter** for selecting status (Active / Not Active).
- Uses `IsActiveEnum` to provide translated and standardized options.
- Supports **custom filtering logic** via an optional closure in the constructor.
- Treats selected values as integers (`1` or `0`) for backend compatibility.

***Example***
```
[ ['id' => 1, 'name' => 'Active'], ['id' => 0, 'name' => 'Not Active'] ]
```

------------------------------------