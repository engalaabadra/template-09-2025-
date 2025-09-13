# ResourceRegistrarCustom

`ResourceRegistrarCustom` is an extension of Laravel's default `ResourceRegistrar` that adds support for **custom resourceful routes** beyond the standard RESTful actions. It enables registration of additional routes such as restoring soft-deleted records, batch activating/deactivating, batch deleting, and permanent deletion.

---

## Overview

Laravel's built-in resource routes cover the typical CRUD operations:

- `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

However, many applications require **extra functionality**, especially when dealing with **soft deletes**, batch operations, or toggling activation status.

This class enhances the resource routing system by adding:

- `restoreMany` - Restore multiple soft-deleted records at once
- `restore` - Restore a single soft-deleted record
- `changeActivate` - Toggle activation for a single record
- `changeActivateMany` - Toggle activation for multiple records
- `destroyMany` - Soft-delete multiple records
- `forceDelete` - Permanently delete a single record
- `forceDeleteMany` - Permanently delete multiple records

---

## Features

- **Full compatibility** with Laravel resource routing conventions.
- **Custom route filtering:** Use the `custom_only` and `custom_except` options to include or exclude specific custom routes.
- **Automatic route naming** that follows Laravel's naming conventions.
- **Support for batch operations** via routes like `restoreMany`, `destroyMany`, and `changeActivateMany`.
- **Support for soft-delete and force-delete routes**, useful in applications using Laravel’s SoftDeletes trait.

---

## Usage

Register resource routes including custom actions by calling:

```php
app('router')->registerCustomResource('users', UserController::class, [
    'custom_only' => ['restoreMany', 'forceDeleteMany'], // Register only these custom routes
    // OR
    'custom_except' => ['destroyMany'],                  // Register all custom routes except these
    // Optional: pass any standard resource options like 'only' or 'except' for default routes
]);


```

# ResourceRegistrarFiles

`ResourceRegistrarFiles` is a custom extension of Laravel's default `ResourceRegistrar` that provides **custom resource routes** specifically designed for handling file operations related to a resource.

---

## Overview

While Laravel's default resource routes handle typical CRUD operations, this class adds support for common file-related actions such as:

- Uploading a single file to a resource
- Uploading multiple files to a resource
- Deleting a single file from a resource
- Deleting multiple files from a resource

These additional routes simplify managing file attachments related to models and improve API consistency.

---

## Supported Custom Routes

| Route Name   | HTTP Method | URI Pattern           | Description                          |
|--------------|-------------|-----------------------|------------------------------------|
| uploadFile   | POST        | `/resource/{id}/file` | Upload a single file to the resource|
| uploadFiles  | POST        | `/resource/{id}/files`| Upload multiple files to the resource|
| deleteFile   | DELETE      | `/resource/{id}/file` | Delete a single file from the resource|
| deleteFiles  | DELETE      | `/resource/{id}/files`| Delete multiple files from the resource|

---

## Usage

To register these file-specific routes for a resource, use the `registerCustomResource` method provided by the class, for example:

```php
app('router')->registerCustomResource('products', ProductController::class);
```

# PendingCustomResourceRegistration

`PendingCustomResourceRegistration` is a helper class designed to manage the registration of **custom resource routes** in Laravel with flexible filtering options.

---

## Purpose

This class wraps a set of custom routes for a resource and allows you to:

- **Filter routes** by including only specific methods (`only`)
- **Exclude specific methods** (`except`)
- **Automatically register routes** with Laravel's router when the object is destroyed (i.e., at the end of the request lifecycle)

This makes defining and controlling custom routes more fluent and manageable.

---

## Key Features

### Route Filtering

- `only(array $methods)`: Keep only the specified route methods.
- `except(array $methods)`: Remove the specified route methods.

Both methods return the instance itself to allow method chaining.

### Route Registration

- The `register()` method iterates over the filtered routes and registers each one to Laravel's router.
- The class uses PHP's magic destructor `__destruct()` to **automatically register all routes** when the instance is destroyed (typically at the end of the request lifecycle), so explicit registration is usually not needed.

---

## Example Usage

```php
$pending = new PendingCustomResourceRegistration($router, $routes);

// Register all routes except 'destroyMany' and 'forceDeleteMany'
$pending->except(['destroyMany', 'forceDeleteMany']);

// Or register only specified routes
$pending->only([RestoringDeletingActionEnum::RESTORE->value, 'changeActivate']);

// Routes are registered automatically when $pending goes out of scope,
// but you can call register() manually if needed:
$pending->register();
```

## Usage All
```php
// Users resource routes and custom resource routes for extended functionality
Route::resource('users', UserController::class);
Route::customResource('users', UserController::class);
Route::customResourceFiles('users', UserController::class);
