
### ActiveScope

 * Global scope to automatically filter only active records.
 * This scope will append `where is_active = true` to all model queries
 * that use this scope, ensuring only active rows are retrieved.

```
class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder  The query builder instance.
     * @param  \Illuminate\Database\Eloquent\Model    $model    The model being queried.
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Add a condition to only retrieve records where 'is_active' is true
        $builder->where('is_active', '=', true);
    }
}
```

### LanguageScope Global Query Scope

`LanguageScope` is a Laravel Eloquent global scope that automatically filters database queries to only include records matching the current application language (locale). This ensures that multilingual data models return only the relevant language-specific rows based on the app's configured locale.

---

***Purpose***

This scope helps manage multilingual datasets by restricting query results to the current language, simplifying retrieval of localized content without manually adding language filters.

---

***How It Works***

- Implements Laravel's `Scope` interface.
- Applies a global `where` condition filtering the `lang` column to match the current app locale (`localeLang()`).
- Automatically applies to all queries on the model using this scope.
- Can be bypassed using Laravel's `withoutGlobalScope()` method if needed.

---

***Usage***

***Applying the Scope to a Model***

Add the scope in your model's `booted` method:

```php
use App\Scopes\LanguageScope;

class YourModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new LanguageScope);
    }
}
```
