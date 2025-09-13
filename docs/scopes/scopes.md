# GeneralScopes, LocalScopes

This package provides a **set of global scopes** to automatically handle common query filters across your Laravel application.  
It ensures that **`is_active`**, **`lang`**, and **`user_id` ownership** are consistently applied to queries, while skipping them in special cases like dashboard routes.

This document explains the logic for displaying data on the website depending on the context: dashboard, guest, or authenticated user. It also explains when global scopes are applied or bypassed based on ownership and user permissions.

### Summary before going into details
Scenario	Scopes Applied?
- Dashboard routes	❌ No scopes applied
- Non-dashboard + guest	✅ Apply global scopes(is_active + lang).
- Non-dashboard + authenticated user + table has user_id column + owned by user	❌ No scopes applied
- Non-dashboard + authenticated user + table has user_id column + not owned	✅ Apply global scopes(is_active + lang).
- Non-dashboard + authenticated user + table has no user_id	✅ Apply global scopes(is_active + lang).

----------------
two routes calls same method getAll , but the first route -> need to get all data like all books, the second route -> need to get all data book ,  (for owner’s)

in the first route , will pass in method `flag = false` , because we need here all data

in the second route , will pass in method `flag = true` , because we need here all data(for owner’s)
```php
$result = $this->contentRepository->getData($this->content, $forUser = true);  // Fetch content data (may be paginated or collection)
```
use it:
```php
public function getData($model, $forUser = true)
{
    /** Base Query */
    $query = $this->buildBaseQuery($model, $forUser); // from baseRepo
}
```
```php
protected function buildBaseQuery($model, $forUser = false)
{
    $query = $model::query();

    return $query
        ->when($model->getProp('eagerLoading'), fn($q) => $q->with($model->getProp('eagerLoading')))
        ->when(
            $forUser && Schema::hasColumn($model->getTable(), 'user_id'),
            fn($q) => $q->OwnedByUser()
        )->filter()
        ->search($model::getProp('columnsSearch'))
        ->latest('id');
}
```
---

## 1. General Rules for Displaying Data

### Contexts:

1. **Dashboard:**

   * All data is displayed **without applying global scopes**.

2. **Not Dashboard + Guest:**

   * Data is displayed **with global scopes applied**.

3. **Not Dashboard + Authenticated User:**

   * Two cases:

     1. **Model does not have `user_id`:** Data is displayed **with global scopes applied**.
     2. **Model has `user_id`:**

        * Check if the user is the **owner** (user\_id matches authenticated user id):

          * **Owner:** Display data **without applying global scopes** (user can manage their own data).
          * **Not owner:** Display data **with global scopes applied**.

### Digram

```


                 ┌─────────────────────────┐
                 │   Dashboard Routes      │
                 │  (dashboard/*)          │
                 └──────────┬─────────────┘
                            │
                        No Scopes
                            │
───────────────────────────────────────────────
        Non-dashboard Routes
───────────────────────────────────────────────
           ┌───────────────┐
           │ Guest Users   │
           └───────┬───────┘
                   │
           Apply Active + Language
                   │
───────────────────────────────────────────────
    Authenticated Users
───────────────────────────────────────────────
    ┌───────────────────────────────┐
    │ Record owned by user_id?      │
    └─────────────┬─────────────────┘
                  │
          Yes ───> No Scopes
          No ────> Apply Active + Language

```

---

## 2. Find a Specific Item

When searching for a specific item within a method:

1. Check if the model has `user_id`.
2. If yes, check if the authenticated user is the owner (user\_id matches).
3. If owner → bypass global scopes.
4. If not owner → apply global scopes.

```php
public function findOrFailApi($id, $model, $forUser = false)
{
    $query = $model::query();
    $user = $model->getAuthUser();
    // If the user is Admin → bypass all restrictions
    if ($user?->hasRole('superadmin') || $user?->hasRole('admin')) {
        return $query->find($id)
            ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
    }

    // check if this item for her
    $hasUserId = Schema::hasColumn($model->getTable(), 'user_id');

    if($user && $hasUserId && $forUser){
        $query->OwnedByUser();
    }

    $item = $query->find($id);
    return $item ?? throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);

}
```
---

## 3. General GET Requests

* User pages show **all data normally**, regardless of ownership.

* Special handling for authenticated users who are owners:

  * Owner’s data → displayed **without global scopes**.
  * Others’ data → displayed **with global scopes applied**.

* Example: In a module, sometimes you need **all data** (both owned and not owned), sometimes **only your data**.

  * The same GET method can be used for both routes.
  * Use a **flag** to indicate whether to filter by user ownership:

    * `true` → get **only authenticated user’s data** (bypass global scopes).
    * `false` → get **all data** (apply global scopes for other users).

---

### Example Usage in Modules

* **All books on site:** Route calls GET method → `flag = false` → returns all books, applies global scopes for others’ data, bypasses scopes for owner’s data.
* **Only my books:** Route calls same GET method → `flag = true` → returns only books where `user_id` matches authenticated user, bypasses global scopes entirely.

---

### Implementation Notes

* The GET method remains **general and reusable**.
* Routes can pass a flag for **ownership filtering**.
* Global scopes are bypassed only for owner’s data, allowing full management on the frontend.
* Other users’ data or guest views always respect global scopes.
---



## 📂 Files Overview

### 1. `HasGeneralScopes` (Trait)

**Path:** `App/Models/Traits/HasGeneralScopes.php`

- Automatically attaches the appropriate global scope (`GeneralScopes`) when a model is booted.
- Special handling for the **`User`** model:
  - If the table has `is_active` → attach `ActiveScope`.
  - Otherwise → attach `GeneralScopes`.

**Example Usage:**

```php
use App\Models\Traits\HasGeneralScopes;

class Post extends Model
{
    use HasGeneralScopes;
}
```

2. GeneralScopes (Global Scope)

```php
$posts = Post::all();


Guest → returns only records where is_active = 1 and filtered by lang.

Authenticated user:

Own posts → no filters applied.

Other users' posts → filtered by is_active and lang.

Dashboard → shows all posts (ignores global scopes).

```
### Requirements

Column is_active → for ActiveScope.

Column lang → for LanguageScope.

Column user_id (optional) → for ownership filtering.

### Benefits

Consistent query filtering across all models.

Centralized logic → no need to repeat conditions in controllers/services.

Flexible: bypass filters in dashboard or for user-owned data.

### OwnedByUserLocalScopeTrait

This trait provides a **local scope** to filter Eloquent models by the authenticated API user.

---

### Local Scopes : `OwnedByUserLocalScopeTrait`

Adds the `OwnedByUser` scope to your model:

- Filters records by the authenticated user (`userApi()->id`).  
- Default column is `user_id`, but can be customized.

```php
use App\Models\Traits\OwnedByUserLocalScopeTrait;

class Post extends Model
{
    use OwnedByUserLocalScopeTrait;
}
```
```php
$query->OwnedByUser()
```
