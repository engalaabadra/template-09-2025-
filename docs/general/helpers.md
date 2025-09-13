## Helpers

#### App\Helpers\constants

***Helper Functions***
This file contains global helper functions for common application tasks such as retrieving request parameters, managing models, working with dates/times, handling currencies, and generating tokens.

### 1. Request Parameter Helpers
Retrieve frequently used input values directly from the current HTTP request.

- `page()` → Get the current page number.  
- `query()` → Get the search keyword.  
- `clientId()` → Get the client ID.  
- `status()` → Get the status value.  
- `active()` → Get the active status flag.  
- `message()` → Get the message content.  
- `rate()` → Get the rating value.  
- `fav()` → Get the favorite flag.  
- `type()` → Get the type value.  
- `login_type()` → Get the login type.  
- `randomLink()` → Get the random link value.  
- `total()` → Get the total items per page (default `10`).

### 2. Language & Currency Helpers
- `supportedLanguages()` → Retrieve supported languages from configuration.  
- `systemCurrency()` → Return the default system currency (`SAR`).  
- `countryCurrency()` → Return the currency based on the user's IP location.  
- `urlFlag($code)` → Get the flag image URL for a given country code.  
- `isDefaultLocale($lang)` → Check if the given language is the system default.

### 3. Date & Time Helpers
- `currentTime()` → Get the current time as `HH:MM:SS`.  
- `currentDate()` → Get the current date as `YYYY-MM-DD`.  

### 4. Model Helpers
- `isSoftDeletes($model)` → Check if the model uses the `SoftDeletes` trait.  
- `modelName($model)` → Get the plural lowercase name of a model.  
- `getModelClass($modelName)` → Get the fully qualified class name of a model if it exists.  
- `refreshIfMissing($data, $model, $key)` → Refresh the model if a given key is missing from request data.

### 5. Payment & Utility Helpers
- `getTokenPayment($paymentMethod)` → Get a base64-encoded authorization token for the specified payment provider (`moyasar` or `tap`).  
- `getCode()` → Generate a code depending on the app environment (random in production, `0000` in others).  
- `filePath($url)` → Convert a public file URL to its storage path.

## Example Usage

```php
// Get supported languages
$languages = supportedLanguages(); // ['en', 'ar']

// Get currency based on IP address
$currency = countryCurrency(); // e.g., "USD"

// Get the current page number for pagination
$currentPage = page(); // e.g., 2

// Generate a payment token for Moyasar
$token = getTokenPayment('moyasar');

// Check if a model uses soft deletes
if (isSoftDeletes($user)) {
    // Perform logic for soft-deletable models
}

// Get plural model name
$name = modelName($user); // "users"

// Generate code depending on environment
$code = getCode(); // '0000' in local, random 4 digits in production
```
#### App\Helpers\ReportHelper

*** Example ***
```

// Group by active status
$activeReport = User::selectRaw(
    ReportHelper::getCommonReports()['by_active']['raw']
)
->groupBy('is_active')
->get();

// Group by creation date
$dateReport = User::selectRaw(
    ReportHelper::getCommonReports()['by_date']['raw']
)
->groupBy(\DB::raw("DATE(created_at)"))
->get();
```

#### App\Helpers\RoleHelper
This helper provides utility methods for working with the application's **main role** configuration and cache.

##### Features
- **`getMainRoleName()`**  
  Retrieves the main role name from cache, falling back to `spatie_seeder.main_role` config if not cached.

- **`getMainRoles()`**  
  Returns all main roles from the `spatie_seeder.roles_structure` config, used to identify top-level roles like `admin` or `super_admin`.

- **`isMainRole($role)`**  
  Checks if the given role matches the configured main role.

***Example*** 

```
// Get main role name
$mainRole = RoleHelper::getMainRoleName();

// Get all main roles
$roles = RoleHelper::getMainRoles();

// Check if a role is the main role
if (RoleHelper::isMainRole('admin')) {
    // Do something for main role
}
```
