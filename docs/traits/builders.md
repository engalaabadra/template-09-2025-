### Builders

#### App\Models\Builders\BaseBuilder
The `BaseBuilder` class extends Laravel's Eloquent `Builder` to add **custom filtering capabilities**.  
It uses dedicated filter classes (`ActiveFilter`, `LangFilter`, `CreatedAtDateRangeFilter`) that apply conditions to the query based on provided input.

```php
public function filters(): array
{
    return [
        // Apply filter by active status using the isActive() scope
        new ActiveFilter(fn ($value) => $this->isActive($value)), // isActive() : in HelpersModelTrait

        // Apply filter by language using the lang() scope
        new LangFilter(fn ($value) => $this->lang($value)), // lang() : in HelpersModelTrait

        // Apply filter by creation date range using the createdAtRange() scope
        new CreatedAtDateRangeFilter(fn ($date) => $this->createdAtRange($date)), // createdAtRange() : in HelpersModelTrait
    ];
}
```
##### Features
- **ActiveFilter** – Filter results by the `is_active` status.
- **LangFilter** – Filter results by the `lang` field.
- **CreatedAtDateRangeFilter** – Filter results by a specific `created_at` date range.

***Example in HelpersModelTrait***

```php
 final public function createdAtRange(string|array|null $dateRange, $column = 'created_at'): static
    {
        return $this->rangeDateFilter($dateRange, $column);
    }

    final public function isActive(?bool $active = null): static
    {
        return $this->when($active !== null, function (Builder $q) use ($active) {
            $q->where('is_active', $active);
        });
    }

    final public function lang(?string $lang): static
    {
        if (!Schema::hasColumn($this->getModel()->getTable(), 'lang')) {
            return $this;
        }
        return $this->when($lang, function (Builder $q) use ($lang) {
           // $q->whereRaw("TRIM(lang) = ?", [$lang]);

            $q->where('lang', $lang);
        });
    }
```
```php
// Filter active users created between 2024-01-01 and 2024-01-31
$users = User::query()
    ->isActive(true)
    ->createdAtRange(['2024-01-01', '2024-01-31'])
    ->get();
```
but we use it in fact(in ***BaseRepo***):
```php
 protected function buildBaseQuery($model, $forUser = false)
    {
        $query = $model::query();
        //Apply all request-based filters dynamically to the query (this method in basebuilder in FilterTrait)
        return $query->filter();
    }
```
in ***FilterTrait***
```php
trait FilterTrait{
  /**
   * Apply filters to the current instance based on request values.
   *
   * @return $this
   */
  public function filter(): static
  {
      foreach ($this->filters() as $filter) { // Loop through defined filters in BaseBuilder
          $value = request($filter->key); // Get value from request
          if ($value !== null) { // Apply only if value exists
              data_get($filter, 'callback')($value); // Execute filter callback($this->isActive($value)),in ->(filter) : new ActiveFilter(fn ($value) => $this->isActive($value)),
            //now i excuted isActive($value) -> inside it : get data only 'is_active' = 1
          }
      }
      return $this; // For method chaining
  }
}
```
***filter file like : ActiveFilter***
```php
final class ActiveFilter extends Filter
{
    public string $key = 'is_active';//key used in the frontend to identify this filter (usually the database column).

    public FilterTypeEnum $filterTypeEnum = FilterTypeEnum::DROPDOWN;//Defines the UI type of the filter in the frontend (dropdown, checkbox, range, etc.).

  //Returns the available dropdown options like :
  // [
  //   ['id' => IsActiveEnum::ACTIVE, 'name' => 'Active']
  // ]
  public static function getData(): null|Arrayable|array|string
    {
        return IsActiveEnum::getOptionsData();
    }
}
```
#### App\Models\Builders\UserBuilder

The `UserBuilder` class is a **custom Eloquent query builder** for the `User` model.  
It extends [`BaseBuilder`] to inherit common filters and uses the `UseFilter` trait to apply dynamic, reusable filter pipelines.

##### Features
- Inherits all filters from `BaseBuilder`:
  - **ActiveFilter** – Filter users by `is_active` status.
  - **CreatedAtDateRangeFilter** – Filter users by a specific creation date range.
- Easily extendable to add user-specific filters.

***UserBuilder***
```php
class UserBuilder extends BaseBuilder
{
  public function filters(): array
  {
      return array_merge(
          parent::filters(), // call filters() from BaseBuilder
          [
              //add your filters this model

          ]
      );
  }
}
```
---------------------------------------