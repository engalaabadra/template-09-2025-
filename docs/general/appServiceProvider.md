# AppServiceProvider

**Namespace:** `App\Providers`
**Purpose:** Central bootstrapper for core application services.

## Overview

The `AppServiceProvider` class is responsible for initializing and bootstrapping essential services in the application. It provides:

* Custom route macros for resource registration.
* Observer attachment for models (e.g., `User`, `Role`, `File`).
* Frontend tooling configuration using Vite.
* Caching of main roles and users for quick access.

---

## Key Features

### 1. Custom Route Macros

Two macros are added to the router:

* **`customResource`**: Registers a resource route with extra options using a custom resource registrar.
* **`customResourceFiles`**: Handles resource controllers for file operations.

**Example:**

```php
// Register standard resource routes with extra features
Route::customResource('users', UserController::class);

// Register resource routes for file uploads/downloads
Route::customResourceFiles('files', FileController::class);
```

---

### 2. Model Observers

Attaches observers to monitor model events for enforcing rules or additional logic.

```php
\App\Models\User::observe(\App\Observers\UserObserver::class);
\App\Models\Role::observe(\App\Observers\RoleObserver::class);
\App\Models\File::observe(\App\Observers\FileObserver::class);
```

---

### 3. Vite Optimization

Pre-fetch resources with limited concurrency to optimize frontend performance:

```php
Vite::prefetch(concurrency: 3);
```

---

### 4. Main Roles & Users Cache

Automatically warms up caches for main roles and users if not present:

```php
\App\Models\Role::getMainRolesIds();
\App\Models\Role::getMainRolesNames();
\App\Models\User::getMainUsersIds();
```

---

### 5. Service Registration

Currently reserved for future service bindings:

```php
$this->app->bind(SomeInterface::class, SomeImplementation::class);
```

---

**Purpose Summary:**
Centralizes system-level setup to ensure consistent behavior across routes, models, and caching. This allows developers to use enhanced resource routing, automatically track model events, and optimize front-end requests.
