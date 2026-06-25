# SoftDeletes and Pruning in Laravel

This guide explains how to use **SoftDeletes** and **Prunable** in Laravel to manage model deletion.

---

## 1. SoftDeletes

- `SoftDeletes` allows models to be "soft deleted".  
- Instead of being permanently removed from the database, the model's `deleted_at` timestamp is set.  
- This means the record still exists in the database but is excluded from queries unless explicitly included.  

### Example:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

- Now, when you run `$user->delete();`, it won’t be permanently deleted.  
- Instead, the `deleted_at` field will be filled with the current timestamp.  

---

## 2. Prunable

- `Prunable` allows models to be permanently removed based on a condition.  
- You must define the `prunable()` method in your model.  
- Laravel will use this method to determine which records should be pruned.  

### Example:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;

class User extends Model
{
    use SoftDeletes, Prunable;

    /**
     * Define the condition for pruning records.
     */
    public function prunable()
    {
        return static::where('deleted_at', '<', now()->subMonth());
    }
}
```

- In this example, users that were soft deleted **more than 1 month ago** will be permanently removed.  

---

## 3. Running the Pruning Command

You can run pruning manually:

```bash
php artisan model:prune
```

- This will go through **all models** that use the `Prunable` trait.  
- It will check their `prunable()` method and remove records accordingly.  

---

## 4. Scheduling Automatic Pruning

To run pruning automatically (e.g., daily), open your `App\Console\Kernel.php` file and add:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('model:prune')->daily();
}
```

- This will make Laravel run pruning **every day automatically**.  

---

## 5. Summary

- `SoftDeletes`: Keeps deleted records in the database with a timestamp.  
- `Prunable`: Defines conditions for **permanently deleting** records.  
- `php artisan model:prune`: Cleans up records based on `prunable()` definitions.  
- Can be automated using the **scheduler**.  
