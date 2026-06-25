
# Custom Resource Routing for Laravel

This README explains the custom resource routing system implemented in `App\Routing` to extend Laravel's default resource controllers.

---

## PendingCustomResourceRegistration

**Purpose:**
Handles deferred registration of custom resource routes with optional filtering using `only` or `except`. If not explicitly registered, routes are automatically registered when the object is destroyed.

**Key Features:**

* `except(array $methods)`: Remove specific methods from registration.
* `only(array $methods)`: Keep only specific methods.
* `register()`: Manually register routes immediately.
* `__destruct()`: Automatically registers routes if not done yet.

---

## ResourceRegistrarCustom

**Purpose:**
Extends Laravel's `ResourceRegistrar` to add custom actions for resources.

**Custom Actions Include:**

* `changeActivateMany`  → PATCH `/actions/activate`
* `restoreMany`         → PATCH `/actions/restore`
* `restore`             → PATCH `/{id}/actions/restore`
* `changeActivate`      → PATCH `/{id}/actions/activate`
* `destroyMany`         → DELETE `/actions/destroy`
* `forceDelete`         → DELETE `/{id}/actions/force`
* `forceDeleteMany`     → DELETE `/actions/force`

**Methods:**

* `registerCustomResource(string $name, string $controller, array $options)`: Registers both default and custom resource routes. Supports `custom_only` and `custom_except` options.
* Individual methods like `addResourceRestoreMany`, `addResourceChangeActivate` to register specific actions.

**Example:**

```php
app('router')->customResource('users', UserController::class, [
    'custom_only' => ['restore', 'changeActivate'],
]);
```

---

## ResourceRegistrarFiles

**Purpose:**
Specialized registrar for handling file operations in resources, including uploading and deleting files.

**Default Actions:**

* `uploadFile`    → POST `/{id}/file`
* `uploadFiles`   → POST `/{id}/files`
* `deleteFile`    → DELETE `/{id}/file`
* `deleteFiles`   → DELETE `/{id}/files`

**Methods:**

* `registerCustomResource($name, $controller, $options)`: Registers file-related routes.
* Individual methods for each action like `addResourceUploadFile`, `addResourceDeleteFiles`.

**Example:**

```php
app('router')->customResourceFiles('documents', DocumentController::class);
```

---

**Usage Notes:**

* `PendingCustomResourceRegistration` allows chaining `.only()` or `.except()` before final route registration.
* Custom routes use `/actions/...` path to avoid conflicts with default resource methods.
* Designed for modular and maintainable API routing in Laravel projects.
