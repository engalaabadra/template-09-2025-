# Controller Traits Documentation

This section documents the **controller-level traits** that provide reusable helpers for integrating Laravel with Inertia.js frontend.

---

## FilterFrontTrait

Responsible for preparing and sharing **model filters** with the frontend (Inertia).

- **`getModelFilters($model)`**
  - Extracts filters defined in the model's custom query builder.
  - Converts each filter object to an array (using `toArray()`).
  - Supports enum-based dropdowns (e.g., Active/Not Active), ranges (min/max), and other filter metadata.
  - Returns a plain array that is frontend-ready.

- **`useFilter($filters)`**
  - Shares prepared filters with Inertia so they are globally available on the frontend.

**Example usage:**
```php
$filters = $this->getModelFilters(User::class);
$this->useFilter($filters);
```

---

## InertiaShareTrait

Centralizes logic for sharing UI-related data between Laravel and Inertia.

Includes:
- Automatic sharing of filters and breadcrumbs.
- Unified rendering of index/detail pages.
- Automatic form data setup for create/update views.

- **`prepareUiData($model, $data)`**
  - Prepares filters, breadcrumbs, and UI helpers for a given model.
  - Shares filters and enables search functionality in the frontend.
  - Returns either an Inertia web response (for frontend) or plain filters (for API).

- **`getCreateUpdateData()`**
  - Returns default form data (e.g., options for enums like `IsActiveEnum`).

- **`renderWebIndexPage($view, $rows, $extra)`**
  - Renders a unified Inertia page.
  - Automatically includes form data and page title (derived from breadcrumbs if not provided).

---

## SetsBreadcrumbsTrait

Handles **breadcrumb navigation** and page titles for dashboard resources.

- **`setBreadcrumb($resource, $action, $modelOrId, $label)`**
  - Dynamically builds breadcrumbs depending on the resource and action (index, show, edit, create).
  - Supports custom labels or model attributes (`name`, `title`).

- **`breadcrumb($items)`**
  - Shares breadcrumbs with Inertia.
  - Automatically prepends a "Home" link.
  - Updates the page title.

- **`pageTitle($title)`**
  - Shares a page title with Inertia, used as a fallback if not explicitly defined.

**Example:**
```php
$this->setBreadcrumb('users', 'edit', $user);
// Breadcrumb → Home > Users > John Doe
```

---

## Benefits

- Consistent UI data sharing across controllers.
- Automatic preparation of filters, breadcrumbs, and form data.
- Unified approach for Inertia responses (less duplication).
- Clean separation of responsibilities with reusable traits.
