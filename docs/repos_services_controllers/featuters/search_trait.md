
# SearchTrait

`SearchTrait` provides dynamic search capabilities for Eloquent models with support for columns, nested relations, and translations.

## Features

* Search in normal columns.
* Search in translatable fields via the `translations` relation.
* Search through nested relations of any depth (e.g., `user.profile.username`).
* Quick ID search using the `#123` format.
* Optional logging of search queries and results count.

## Usage

```php
$users = User::query()
    ->search(['name', 'email', 'profile.bio'])
    ->get();
```

## Key Methods

* **search(array \$columns, ?string \$search\_key = null): static**
  Performs the dynamic search. Defaults to `request('search')` if `$search_key` is not provided.

* **applyNestedRelationSearch(\$query, string \$columnPath, string \$searchKey)**
  Recursively searches through nested relations based on dot notation.

* **afterSearchLogging(string \$search\_key): void**
  Logs search queries and result counts for analytics.
