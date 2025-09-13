# OwnedByUserLocalScopeTrait

## Overview

`OwnedByUserLocalScopeTrait` is a Laravel trait that provides a local query scope to filter records owned by a specific user or the currently authenticated user. It is useful when you want to ensure that queries only return resources belonging to a particular user.

### Features

* Filters queries by the authenticated user or a given user.
* Default owner column is `user_id`, but it can be customized.
* Works with both User model instances and user IDs.
* Optional: if no user is provided, the currently authenticated user is used.

### Method

* **scopeOwnedByUser(\$query, \$user = null, \$ownerKey = 'user\_id')**

  * `$query`: The Eloquent query builder.
  * `$user`: Optional User model or user ID. If null, the authenticated user is used.
  * `$ownerKey`: Optional column name to filter by. Default is `user_id`.
  * Returns the query builder filtered by the user.

### Usage Examples

```php
// Using currently authenticated user
$posts = Post::OwnedByUser()->get();

// Passing a specific User model
$user = User::find(5);
$posts = Post::OwnedByUser($user)->get();

// Passing a user ID directly
$posts = Post::OwnedByUser(5)->get();
```

This ensures that queries are automatically scoped to only the resources owned by the relevant user.
