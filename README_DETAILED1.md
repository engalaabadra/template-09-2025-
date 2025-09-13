# Project Internal API Documentation (Detailed Descriptions)

This document lists classes and their public methods with refined descriptions of what each method does, based on naming conventions and domain context.

## Controllers
### LanguageController (app/Http/Controllers/LanguageController.php)
- **switchLang()** - **defaultLang()** - **getAllLangs()**

### NotificationController (app/Http/Controllers/NotificationController.php)
- **__construct()**: Perform domain-specific business logic.
- **index()**: Return a paginated list of resources with optional filters.
- **updateFcm()**: Perform domain-specific business logic.  
  ↳ Expected fields: fcm_token
- **sendNotificationMethod()**: Perform domain-specific business logic.  
  ↳ Expected fields: title, body, type

### LoginController (app/Http/Controllers/Auth/Admin/LoginController.php)
- **__construct()** - **login()** - **destroy()**

### LoginController (app/Http/Controllers/Auth/User/LoginController.php)
- **__construct()** - **login()** - **destroy()**

### RecoveryPasswordController (app/Http/Controllers/Auth/User/RecoveryPasswordController.php)
- **__construct()** - **forgotPassword()** - **checkCode()** - **resendCode()** - **resetPassword()**

### RegisterController (app/Http/Controllers/Auth/User/RegisterController.php)
- **__construct()** - **register()** - **checkCodeRegister()** - **resendCodeRegister()**

### BannerController (app/Http/Controllers/Dashboard/BannerController.php)
- **__construct()**: Perform domain-specific business logic.
- **index()**: Display a listing of items (Return a paginated list of resources with optional filters.)
      * Handles both Web and API responses : - Web: returns an Inertia view or downloadable file. - API: returns JSON or downloadable file.
                  
- **show()**: Retrieve a single resource by its ID.
- **store()**: Validate and create a new resource entry.
- **update()**: Update an existing resource with new data.
- **forceDelete()**: Permanently delete an item from the database.
- **forceDeleteMany()**:  Permanently delete multiple items at once.
- **destroy()**:  Soft delete an item (mark as deleted without removing from DB)-> can be restored later.
- **destroyMany()**: Soft delete multiple items at once.
- **restore()**: Restore a soft deleted item.
- **restoreMany()**: Restore multiple soft deleted items at once.
- **changeActivate()**: Toggle activation status (activate/deactivate) for a specific item.
- **changeActivateMany()**: Activate or deactivate multiple items at once.
- **uploadFile()**: Upload a single file (image or file) related to an item. 
  ↳ Expected fields: image or file
- **deleteFile()**: Delete a single file associated with an item.
- **uploadFiles()**: Upload multiple files related to an item. 
  ↳ Expected fields: image or file
- **deleteFiles()**:  Delete a single file associated with an item.

### ProfileController (app/Http/Controllers/User/ProfileController.php)
- **__construct()** - **show()** - **update()** - **updatePassword()** - **uploadFile()** - **deleteFile()** - **uploadFiles()** - **deleteFiles()**

## Services
### LoginService (app/Services/Auth/Admin/Login/LoginService.php)
- **__construct()** - **login()**

### LoginService (app/Services/Auth/User/Login/LoginService.php)
- **__construct()** - **login()**

### app/Services/Auth/User/Recovery/PasswordService.php
- **__construct()** - **forgotPassword()** - **resendCode()** - **resetPassword()**

### app/Services/Auth/User/Register/RegisterService.php
- **__construct()** - **register()** - **resendCode()**

### EloquentService (app/Services/Eloquent/EloquentService.php)
The `EloquentService` class provides a base service layer for handling **Eloquent model logic** and applying business rules.  

provides:  
  - Full CRUD with translation and file handling.  
  - Trash management (soft delete, restore, force delete).  
  - Activation toggling for single and multiple records.  
  - Support for bulk operations and eager loading  of related data if defined on the model
  - `$forUser = true` restricts access to item owned by the authenticated user.
  - Exceptions are thrown with proper API response codes if validation fails or items are not found.

- **__construct(BaseRepository $baseRepo, TranslationService $translationService)**: Initializes the service with a base repository and translation service.



***CRUD Methods***

- **store($request, $model)**:  
      Create a new record.  
        - Filters input to only fillable model fields.  
        - Handles translations and file uploads if provided.  
        - Supports eager loading of related models.  

  **Request Example:**  
    ```json
    {
      "name": "Sample Item",
      "is_active": true,
      "translations": {
        "en": { "title": "Sample" },
        "ar": { "title": "عينة" }
      },
      "files": ["file1.png", "file2.jpg"]
    }
    ```

    **Response Example:**  
    ```json
    {
      "id": 1,
      "name": "Sample Item",
      "is_active": true,
      "translations": { ... },
      "files": [ ... ]
    }
    ```
- **update($request, $id, $model, $forUser = false)**: 

      Update an existing record by ID.  
        - Validates and filters input.  
        - Updates translations and files if provided.  
        - Returns updated model with eager-loaded relations if defined.

  **Request Example:**  
  ```json
  {
    "name": "Updated Item",
    "translations": {
      "en": { "title": "Updated" }
    }
  }
  ```

  **Response Example:**  
  ```json
  {
    "id": 1,
    "name": "Updated Item",
    "translations": { ... }
  }
  ```

- **forceDelete($id, $model, $forUser = false)**: 

      Permanently delete a trashed item by ID. 

  **Response Example:**  
  ```json
  { "message": "Item permanently deleted" }
  ```
       

- **forceDeleteMany($request, $model, $forUser = false)**: 
    Permanently delete multiple trashed items in bulk.  
  **Request Example:**  
  ```json
  { "ids": [1, 2, 3] }
  ```  
  **Response Example:**  
  ```json
  { "deleted": [1, 2, 3] }
  ```

---

## Trash Methods

- **destroy($id, $model, $forUser = false)**: 

  Soft delete (or permanently delete if SoftDeletes not used) a record by ID.  
  **Response Example:**  
  ```json
  {
    "id": 1,
    "deleted_at": "2025-09-12T12:00:00Z"
  }
  ```

- **destroyMany($request, $model, $forUser = false)**: 

      Bulk soft delete multiple records.   
  **Request Example:**  
  ```json
  { "ids": [4, 5, 6] }
  ```  
  **Response Example:**  
  ```json
  { "deleted": [4, 5, 6] }
  ```


- **restore($request, $id, $model, $forUser = false)**: 

  Restore a single trashed record by ID.  
  - Supports strategies: **modify**, **replace**, **prevent**.  

  **Request Example:**  
  ```json
  { "strategy": "modify" }
  ```  
  **Response Example:**  
  ```json
  {
    "id": 1,
    "restored": true
  }
  ```  

- **restoreMany($request, $model, $forUser = false)**: 
    Bulk restore trashed records.  
    **Request Example:**  
    ```json
    { "ids": [1, 2, 3] }
    ```  
    **Response Example:**  
    ```json
    { "restored": [1, 2, 3] }
    ```

## Activation Methods
  - **changeActivate($request, $id, $model, $forUser = false)**: 
  Toggle, activate, or deactivate a record by ID.  
  - Actions: **activate**, **deactivate**, **toggle**.  

    **Request Example:**  
    ```json
    { "action_activation": "toggle" }
    ```  
    **Response Example:**  
    ```json
    {
      "id": 1,
      "is_active": false
    }
    ```

- **changeActivateMany($request, $model, $forUser = false)**: 

  Bulk toggle/activate/deactivate multiple records.  
  **Request Example:**  
  ```json
  { "ids": [10, 11], "action_activation": "activate" }
  ```  
  **Response Example:**  
  ```json
  { "activated": [10, 11] }
  ```

## File Handling
**uploadFile($request, $id, $model, $forUser = false)**: 
Upload a single file or image for a given model instance.

**Request Body (JSON + Multipart form-data):**
```json
{
  "file": "<binary>", 
  "image": "<binary>"
}
```

**Response Example:**
```json
{
  "id": 12,
  "name": "Sample Item",
  "file_url": "https://example.com/storage/files/123.pdf",
  "image_url": "https://example.com/storage/images/456.png"
}
```
**uploadFiles($request, $id, $model, $forUser = false)**: 

Upload multiple files or images for a given model.

**Request Body (Multipart form-data):**
```json
{
  "files": ["<binary1>", "<binary2>"],
  "images": ["<binary1>", "<binary2>"]
}
```

**Response Example:**
```json
{
  "id": 34,
  "title": "Gallery",
  "files": [
    {"id": 1, "url": "https://example.com/storage/files/a.pdf"},
    {"id": 2, "url": "https://example.com/storage/files/b.pdf"}
  ],
  "images": [
    {"id": 11, "url": "https://example.com/storage/images/x.png"},
    {"id": 12, "url": "https://example.com/storage/images/y.png"}
  ]
}
```


**deleteFile($id, $model, $forUser = false)**: 

Delete a single file or image associated with a model.

**Request (no body needed):**
```http
DELETE /api/{model}/{id}/file
```

**Response Example:**
```json
{
  "id": 12,
  "name": "Sample Item",
  "file_url": null,
  "image_url": null
}
```

**deleteFiles($request, $id, $model, $forUser = false)**: 

Delete multiple files or images associated with a model.

**Request Body (JSON):**
```json
{
  "ids": [1, 2, 3]
}
```
or delete all files:
```json
{
  "ids": "all"
}
```

**Response Example:**
```json
{
  "id": 34,
  "title": "Gallery",
  "files": [],
  "images": []
}
```
---------------------------------------------------

#### All services modules extends from this EloquentService to use any method from this service or override on it to flex with it  , like app/Services/Dashboard/Banner/BannerService.php

## General Services

#### GeneratePdfService (app/Services/General/PdfMethods/GeneratePdfService.php)
  - **renderPdf()**: 

#### ProccessCodesService (app/Services/General/ProcessCodeMethods/ProccessCodesService.php)
**processCode()** - **checkCode()** - **findCodeUser()**

#### SendingMessagesService (app/Services/General/SendingMessageMethods/SendingMessagesService.php)
**reminderSms()** - **sendResetSms()** - **sendingMessage()**- **sendToPhoneWattsapp()**: 

#### SendingNotificationsService (app/Services/General/SendingNotificationMethods/SendingNotificationsService.php)
- **sendNotification()**

#### VonageCheckValidateNumber (app/Services/General/VonageCheckMethods/VonageCheckValidateNumber.php)
- **checkPhoneNumberValidity()**

#### TranslationService (app/Services/Translation/TranslationService.php)
- **handleTranslations()**

#### PaymentService (app/Services/User/Payment/PaymentService.php)
- **getPayment()** - **createPayment()** - **updatePayment()**

#### WalletService (app/Services/User/Payment/WalletService.php)
- **__construct()** - **create()** - **verify()**

#### handles (app/Services/User/Payment/PaymentGateways/Paypal/Paypal.php)
- **__construct()** - **create()** - **verify()** - **formOptions()**

#### PaypalService (app/Services/User/Payment/PaymentGateways/Paypal/PaypalService.php)
- **__construct()** - **client()** - **setDataPayment()**

#### handles (app/Services/User/Payment/PaymentGateways/Tap/Tap.php)
- **__construct()** - **create()** - **verify()**

#### TapService (app/Services/User/Payment/PaymentGateways/Tap/TapService.php)
- **getStatusTap()** - **curl()** - **getCapturePayment()**

#### handles (app/Services/User/Payment/PaymentGateways/Thawani/Thawani.php)
- **__construct()** - **create()** - **verify()**

### app/Services/User/Profile/ProfileService.php
- **__construct()** - **update()** - **updatePassword()** - **uploadFile()** - **uploadFiles()** - **deleteFile()** - **deleteFiles()**


## Repositories
### LoginRepository (app/Repositories/Auth/Admin/Login/LoginRepository.php)
- **findUserWithRolesByEmailOrPhone()**

### LoginRepository (app/Repositories/Auth/User/Login/LoginRepository.php)
- **findUserWithRolesByEmailOrPhone()**

### app/Repositories/Auth/User/Recovery/PasswordRepository.php
- **findUserByEmailOrPhone()**

### app/Repositories/User/Payment/PaymentRepository.php
- **callback()**

### app/Repositories/User/Profile/ProfileRepository.php
- **show()**

### EloquentRepository extends (app/Repositories/Base/BaseRepository.php)
It provides **reusable methods** for querying, filtering, searching, reporting, deleting/restoring, exporting, and sharing UI-related data across the whole project.

####  Usage Flow
- `EloquentRepository` calls **BaseRepository methods** like:
  - `buildBaseQuery()` → for listing & filtering.
  - `findOrFailApi()` → for retrieving single records.
  - `handleReport()` / `exportToExcel()` → for reporting & exporting.

####  Responsibilities
- Build dynamic queries with **filters**, **search**, **ownership rules**, and **ordering**.
- Handle **soft delete operations** (restore, force delete, only trashed).
- Provide consistent **API error handling** (throws `ApiResponseException` with `ServiceResponseEnum`).
- Support **Excel exports** for web/API responses.
- Manage **UI flash messages** and **Inertia data sharing**.

- **buildBaseQuery($model, $forUser = false)**
Creates a **dynamic query** with filtering, searching, eager-loading, and ownership restrictions.
  1. Starts with base query.
  2. Adds eager loading (if defined in model).
  3. Restricts by `user_id` if enabled.
  4. Applies filters from `FilterTrait`.
  5. Applies search from `SearchTrait`.
  6. Orders results by latest `id`.
- **Returns:** `\Illuminate\Database\Eloquent\Builder`

- **handleReport($model, $filters)**
Generates **custom reports** based on request parameters.

- Collects `report_types` from request (default: `['default']`).
- Calls `generateReport()` method on the model for each type.
- **Returns:** Array of reports grouped by type.

- **findOrFailApi($id, $model, $forUser = false)**
Finds a record by ID for API responses.  
Throws `ApiResponseException` if not found.

- **Ownership check:** If `$forUser = true` and model has `user_id`, restricts query.
- **Returns:** Found model instance.


- **findWithoutTrashedOrFail($id, $model, $forUser = false)**
Fetches a record **excluding trashed**.  
Used in `activate` and `destroy`.

- Allows **admin/superadmin** to bypass restrictions.
- **Throws:** `ApiResponseException` if not found.



- **findOnlyTrashedOrFail($id, $model, $forUser = false)**
Fetches **soft-deleted** records.  
Used for `restore` and `force delete`.

- Checks ownership if `$forUser = true`.
- **Throws:** `ApiResponseException` if not found.



- **tryDelete($row, ?Closure $callback = null)**
Attempts to soft-delete a record in a **transaction**.

- Runs optional callback after deletion.
- Sets success/error flash messages.

- **tryForceDelete($row)**
Permanently deletes a record.

- Executes inside a **transaction**.
- Returns `true/false` depending on success.

- **tryDeleteForceDelete($model, $id, $makeMessageSession = false)**
Deletes or force-deletes a record by ID.

- **For web:** shows flash messages.
- **For API:** silent boolean result.

- **tryRestore($model, $id, $makeMessageSession = false)**
Restores a **soft-deleted record**.

- Resets `deleted_by_id` to `NULL`.
- Sets optional flash success message.

- **Flash Messages & UI Sharing**
- `makeSuccessSessionMessage($message = null)` → success toaster.
- `makeErrorSessionMessage($message = null)` → error toaster.
- `createToaster($type, $title, $message)` → internal flash generator.
- `refreshDom()` → triggers DOM refresh in Inertia.
- `flashShareData($data)` → passes temporary flash data to UI.
- `useTransparent($transparent = true)` → shares transparency state with Inertia.

- **exportToExcel($model, $query)**
Exports model data into an **Excel file**.

- **Web request:** triggers file download.
- **API request:** returns file URL.
- Uses `$columnsToExport` defined in the model.

**Example Response (API):**
```json
{
  "url": "http://yourapp.com/storage/exports/User 2025-09-12.xlsx"
}
```

- **UI Helpers**
- `addElFileCard($collection, $label, $archives, $el_file_card_type = 'archive_card')`  
  → Returns structured array for file cards.

- `makeStatisticCard($title, $value, $icon = 'pi pi-chart-line', $is_price = false)`  
  → Returns an array used for statistic display widgets.


### Example Usage

#### Fetch Data (with filter + search)
```php
$users = $baseRepo->buildBaseQuery(User::class, true)->get();
```

#### Delete Record
```php
$baseRepo->tryDelete($user, fn($row) => Log::info("Deleted: " . $row->id));
```

#### Restore Record
```php
$baseRepo->tryRestore(User::class, 15, true);
```

#### Export Data
```php
return $baseRepo->exportToExcel(User::class, User::query());
```

#### All Repositories modules extends from this EloquentRepository to use any method from this service or override on it to flex with it  , like app/Repositories/Dashboard/Banner/BannerRepository.php

- **getData($model, $forUser = false)** 
  Fetches model records with support for filtering, searching, eager loading, and ownership restrictions.
    - Builds a **base query** with filters, search, ownership check, and ordering.
    - Supports both **paginated** results and full collections.
    - Prepares UI filters and metadata using `prepareUiData()` (from `InertiaShareTrait`).
    - Supports multiple response modes:
      - **Web Request (Inertia.js)** → returns UI filters & dataset for Inertia.
      - **API Request** → returns JSON response (Standard CRUD responses.)
      - **Report Request (`?report=true`)** → generates structured reports.
      - **Export Request (`?export=true`)** → exports dataset to Excel.

        **Example Request (API)**
        ```http
        GET /api/{model}?page=1&search=name&report=true
        ```

        **Example Response**
        ```json
        {
          "data": [
            { "id": 1, "name": "Example", "is_active": 1 }
          ],
          "filters": {
            "search": "name",
            "order": "latest"
          }
        }
        ```


- **show($id, $model, $forUser = false)**
Retrieves a single record by ID with optional ownership and eager loading.
- Uses `findOrFailApi()` to fetch record or return 404.
- Eager loads relations if defined via `$model->getProp('eagerLoading')`.
- Returns the fully hydrated model.

**Example Request**
```http
GET /api/{model}/5
```

**Example Response**
```json
{
  "id": 5,
  "name": "Demo Item",
  "is_active": 1,
  "relations": {
    "category": { "id": 2, "name": "Category A" }
  }
}
```

--------------------------------