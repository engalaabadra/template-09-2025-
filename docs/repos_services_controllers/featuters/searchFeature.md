
in HelpersModelTrait

# Dynamic Full-Text Search Method

This method provides a flexible full-text search across multiple columns, including support for:

- Regular database columns.
- JSON-translatable columns (stored as JSON objects with language keys).
- Related model columns using dot notation (e.g., `department.name`).
- Direct ID-based search by prefixing the search term with `#`.

## Example Usage

```php
$query->search(['name', 'email', 'department.name']);
```

## How It Works

### Retrieve the search key  
It obtains the search key from the method argument or from the HTTP request query parameter named `search`. If none is provided, the method returns the query unchanged.

### Trim and validate the search key  
It trims whitespace from the search key and checks if either the search key or the columns array is empty, returning early if so.

### ID-based search  
If the search key starts with `#`, it treats the rest as an ID and searches directly on the primary key `id` column.

### Main search logic  
It loops through each column name provided and applies different search strategies:

- **Translatable columns:**  
  For columns listed as translationFields in the model (`$this->model::getProp('translationFields')`), it performs a search inside the related `translations` table via the `translations` relationship.  
  - On dashboard routes (`api/dashboard/*`), it searches across all languages without filtering by language.  
  - On other routes, it restricts the search to the current application locale (`app()->getLocale()`).

- **Related model columns with dot notation:**  
  If a column name includes dot notation (e.g., `user.profile.username`), it recursively queries nested relations to apply the search on the correct related table column. This is done by breaking down the relation chain and applying `whereHas` queries at each level.

- **Regular columns:**  
  For simple columns (not translatable or relations), it applies a standard `LIKE` search.

### Return the modified query builder  
The method returns `$this` to allow further chaining of query methods.

## Benefits

- Supports multilingual search that respects the application locale.  
- Handles complex nested relations seamlessly.  
- Enables quick ID-based lookups using a special prefix.  
- Can be used transparently on both dashboard and frontend routes with context-aware behavior.

This flexible search method is designed for Laravel Eloquent models with multilingual and relational data, making it suitable for rich data filtering and searching scenarios.

