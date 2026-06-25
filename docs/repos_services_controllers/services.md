
# EloquentService Class

This class is a base service layer for handling Eloquent model business logic and common CRUD operations. It integrates with repositories, translation services, and provides transactional safety via traits.

---

## Overview

- Uses `HandlesServiceTransactions` trait to automatically wrap `store`, `update`, `destroy`, and `restore` methods in DB transactions.
- Depends on repositories (`BaseRepository` and `EloquentRepository`) for data access.
`HandlesServiceTransactions` : 
* It is primarily used to wrap `store` and `update` methods in database transactions automatically
* i put it protected to be unvisible when calling it from controller -> in this time will go into __call() this method contain DB::transaction to apply transaction on these methods

- `TranslationService` for additional business rules and translation handling.
- Supports file and image uploads, eager loading, soft delete handling, and activation toggling.
- in failed : throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST) or throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND)
- in success : Returns standardized responses $data to controller to return success msg .

---

## Constructor

```php
public function __construct(
    BaseRepository $baseRepo,
    EloquentRepository $eloquentRepo,
    TranslationService $translationService
) { ... }


# EloquentService Additional Methods - Delete, Restore, and Safe Activation Handling

This section describes methods responsible for soft deleting, permanently deleting, restoring, and safely activating model records, including bulk operations and conflict resolution policies.

---
## store & update methods
use this method handleTranslationsAndFiles : to Handle translations and file uploads
```
// Check if file are provided
if (isset($data['file'])) {
    $item->uploadSingleMedia($request->file('file'), 'file', $folder);
}

// Check if image are provided
elseif (isset($data['image'])) {
    $item->uploadSingleMedia($request->file('image'), 'image', $folder);
}

// Check if images are provided
if (isset($data['images'])) {
    // Upload multiple images to the media collection
    $item->uploadMultipleMedia($request->file('images'), 'image', $folder);
}
// Check if files are provided
elseif (isset($data['files'])) {
    // Upload multiple files to the media collection
    $item->uploadMultipleMedia($request->file('files'), 'file', $folder);
}
```
same this:
```
// Upload single or multiple media based on provided input
foreach (['file' => 'file', 'image' => 'image'] as $key => $type) {
    if (isset($data[$key])) {
        $item->uploadSingleMedia($request->file($key), $type, $folder);
    }
}

foreach (['files' => 'file', 'images' => 'image'] as $key => $type) {
    if (isset($data[$key])) {
        $item->uploadMultipleMedia($request->file($key), $type, $folder);
    }
}

```

## Delete Methods

### `destroy($id, $model )`

- Soft deletes a single record by ID.
- Uses the `baseRepo` to find the item excluding trashed records.
- Returns a 404 response if not found or already deleted.
- Deletes the item and returns the deleted model with eager-loaded relations.

### `destroyMany($model)`

- Handles bulk soft deletion.
- Delegates to `handleBulkAction()` with `RestoringDeletingActionEnum::DESTROY->value` action.

### `forceDelete($id, $model )`

- Permanently deletes a trashed record by ID.
- Finds the trashed record using `baseRepo`.
- Returns 404 if not found.
- Calls `forceDelete()` on the model instance.

### `forceDeleteMany($model)`

- Bulk permanently deletes trashed records.
- Delegates to `handleBulkAction()` with `RestoringDeletingActionEnum::FORCE_DELETE->value` action.

---

## Restore Methods

### `restore($request, $id, $model )`

- Restores a soft-deleted record by ID.
- Checks for unique field conflicts before restore if `$model::getProp('uniqueFields')` is defined.
- If conflicts exist, calls `safeRestoreById()` with a policy (`modify`, `replace`, or `prevent`).
- Reloads eager relationships after restoring.
- Returns success response with restored model.

### `restoreMany($model)`

- Bulk restores trashed records.
- Delegates to `handleBulkAction()` with `RestoringDeletingActionEnum::RESTORE->value` action.

---

## Safe Activation & Restore Conflict Handling

### `safeActivateById($item, string $policy = 'modify')`

- Checks if activating `$item` will cause unique field conflicts among active records.
- `$uniqueFields` defines which fields are considered unique (e.g., `['title']`).
- Conflict policies:
  - `modify`: Append timestamp suffix to unique fields and activate.
  - `replace`: Delete conflicting active records, then activate.
  - `prevent`: Reject activation and return an error response.
- Updates the activation status (`is_active`) accordingly.

### `safeRestoreById($item, string $policy = 'prevent')`

- Checks for conflicts when restoring a soft-deleted item.
- Uses the same `$uniqueFields` for conflict detection among non-deleted records.
- Conflict policies:
  - `modify`: Append timestamp suffix to unique fields and restore.
  - `replace`: Delete conflicting non-deleted records, then restore.
  - `prevent`: Reject restore and return an error response.

---

## Summary

These methods provide:

- **Soft Delete & Force Delete:** Single and bulk deletion with safety checks.
- **Restore:** Conflict-aware restore with customizable policies.
- **Safe Activation:** Prevent activation conflicts via modification, replacement, or rejection policies.
- **Bulk Operations:** Delegated to a shared handler `handleBulkAction()` for consistency.

---

## Usage Example

```php
// Soft delete a record
$response = $service->destroy($id, $model );

// Force delete a trashed record
$response = $service->forceDelete($id, $model );

// Restore a trashed record with conflict policy
$response = $service->restore($request, $id, $model );

// Safely activate an item with "modify" policy on conflict
$response = $service->safeActivateById($item, 'modify');
```
# Method: `handleBulkAction`

This method handles bulk operations for restoring, soft deleting, or force deleting records on a given Eloquent model.

---

## Description

- Accepts a model and an action (`restore`, `destroy`, or `forceDelete`).
- Supports targeting specific IDs or all records (`ids = "all"`).
- Applies the action in bulk to matching records.
- Returns a standardized response including processed IDs, not found IDs, and conflict IDs (reserved for future use).
- Throws an exception if an invalid action is provided.

---

## Parameters

| Name    | Type                        | Description                              |
|---------|-----------------------------|------------------------------------------|
| `$model`| `\Illuminate\Database\Eloquent\Model` | The Eloquent model instance to operate on. |
| `$action`| `string`                   | Action to perform: `restore`, `destroy`, or `forceDelete`. |

---

## Workflow

1. **Input Handling:**
   - Retrieves `ids` from request input.
   - Checks if operation is for all items (`ids === "all"`).
   
2. **Query Preparation:**
   - For `restore` and `forceDelete` actions, uses `onlyTrashed()` scope to target soft-deleted records.
   - For `destroy`, uses `withoutTrashed()` scope to target non-deleted records.

3. **Fetching Items:**
   - If targeting all items, fetches all matching records.
   - Otherwise, fetches only records matching provided IDs.

4. **Validation:**
   - Returns a 404 error if no items are found (and not "all").

5. **Processing Items:**
   - Loops through each fetched item.
   - Executes the given action via PHP 8 `match` expression:
     - `restore`: Restores soft-deleted record.
     - `forceDelete`: Permanently deletes soft-deleted record.
     - `destroy`: Soft deletes the record.

6. **Tracking Results:**
   - Collects processed IDs.
   - Determines not found IDs by comparing requested IDs to processed.

7. **Response:**
   - Returns a standardized success response with:
     - `processed_ids`
     - `not_found_ids`
     - `conflict_ids` (currently empty, for possible future use).

---

## Example Usage

```php
// Soft delete multiple records by IDs
$response = $service->handleBulkAction($model, RestoringDeletingActionEnum::DESTROY->value);

// Restore multiple trashed records by IDs
$response = $service->handleBulkAction($model, RestoringDeletingActionEnum::RESTORE->value);

// Force delete trashed records
$response = $service->handleBulkAction($model, RestoringDeletingActionEnum::FORCE_DELETE->value);

// Soft delete all records
$response = $service->handleBulkAction($model, RestoringDeletingActionEnum::DESTROY->value, ['ids' => 'all']);

```
# Methods Overview in Service Class

This section documents key service methods for bulk activation, file upload, and file deletion management in an Eloquent-based Laravel service.

---

## `handleBulkAction($model)`

**Purpose:**  
Handle bulk activation, deactivation, or toggling of user status.

**Features:**  
- Supports IDs as an array, JSON string, or `"all"` to act on all users.  
- Supports actions: `activate`, `deactivate`, or `toggle` (default).  
- Returns detailed response with updated users, failed updates, and not found IDs.

**Workflow:**  
1. Retrieves `ids` and `action` from request.  
2. Validates `ids` format.  
3. Fetches matching users (all or specific IDs).  
4. Loops through users, updating `is_active` status per requested action.  
5. Collects successfully updated IDs, failed IDs, and not found IDs.  
6. Reloads updated users with eager relationships (if defined).  
7. Returns a structured success response with data.

---

## `uploadFile($request, $id, $model )`

**Purpose:**  
Upload a single file or image to a model instance.

**Workflow:**  
- Validates input request data.  
- Finds model instance by ID.  
- Uploads single media for `file` or `image` if present.  
- Loads eager relationships if defined.  
- Returns success response with updated model data.


```
// Upload file or image if provided and supported by the model
foreach (['file', 'image'] as $type) {
    if (isset($data[$type]) && method_exists($item, $type)) {
        $item->uploadSingleMedia($request->file($type), $type, $folder);
    }
}
```
same this:
```
if (isset($data['file']) && method_exists($item, 'file')) {
    $item->uploadSingleMedia($request->file('file'), 'file', $folder);
} elseif (isset($data['image']) && method_exists($item, 'image')) {
    $item->uploadSingleMedia($request->file('image'), 'image', $folder);
}
```
---

## `uploadFiles($request, $id, $model )`

**Purpose:**  
Upload multiple files or images to a model instance.

**Workflow:**  
- Validates input request data.  
- Finds model instance by ID.  
- Uploads multiple media files for `images` or `files`.  
- Loads eager relationships if defined.  
- Returns success response with updated model data.

```
if (isset($data['file']) && method_exists($item, 'file')) {
    $item->uploadSingleMedia($request->file('file'), 'file', $folder);
} elseif (isset($data['image']) && method_exists($item, 'image')) {
    $item->uploadSingleMedia($request->file('image'), 'image', $folder);
}

```
same this:
```
// Upload files or images if provided and supported by the model
  foreach (['files', 'images'] as $type) {
      if (isset($data[$type]) && method_exists($item, $type)) {
          $item->uploadSingleMedia($request->file($type), $type, $folder);
      }
  }

```
---

## `deleteFile($id, $model )`

**Purpose:**  
Delete a single file or image associated with a model.

**Workflow:**  
- Finds model instance by ID.  
- Attempts to delete single media in order: `image`, then `file`.  
- Returns `notFound` if deletion fails.  
- Loads eager relationships if defined.  
- Returns success response with updated model data.

```
// If image doesn't exist, try deleting file
    if (method_exists($item, 'image')) {
        $item->deleteSingleMedia('image');
    } elseif (method_exists($item, 'file')) {
        $item->deleteSingleMedia('file');
    }
```
same this:
```
// Delete image or file if supported by the model  
  foreach (['image', 'file'] as $type) {  
      if (method_exists($item, $type)) {  
          $item->deleteSingleMedia($type);  
          break;  
      }  
  }  
```


---

## `deleteFiles($id, $model )`

**Purpose:**  
Delete multiple files and/or images by IDs from a model.

**Workflow:**  
- Finds model instance by ID.  
- Retrieves file and image IDs from request inputs (`file_ids`, `image_ids`).  
- Returns bad request if no IDs provided.  
- Deletes specified media by IDs, or all if no specific IDs provided.  
- Loads eager relationships if defined.  
- Returns success response with updated model data.

```
if(method_exists($item, 'files')){
  // Handle file deletion if file IDs are 
  if (!empty($fileIds)) {
      $item->deleteMediaByIds($fileIds, 'files');
  } else {
      $item->deleteAllMedia('files');
  }
}

if(method_exists($item, 'images')){
  // Handle image deletion if image IDs are provided
  if (!empty($imageIds)) {
      $item->deleteMediaByIds($imageIds, 'images');
  } else {
      $item->deleteAllMedia('images');
  }
}
```
same this:
```
// Map relation name to request key
$mediaTypes = [
    'files'  => request()->input('file_ids', []),
    'images' => request()->input('image_ids', []),
];

// if inputs null : Bad Request
if (empty($mediaTypes['files']) && empty($mediaTypes['images'])) {
    throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST);
}

foreach ($mediaTypes as $relation => $ids) {
    if (!method_exists($item, $relation)) {
        continue;
    }

    if (!empty($ids)) {
        $item->deleteMediaByIds($ids, $relation);
    } else {
        $item->deleteAllMedia($relation);
    }
```
---

# Notes

- All methods rely on a consistent $data  for standardized API responses.  
- Media uploading and deletion assume the model implements appropriate methods like `uploadSingleMedia`, `uploadMultipleMedia`, `deleteSingleMedia`, and `deleteMediaByIds`.  
- Eager loading relations are handled dynamically if the model defines a `getProp('eagerLoading')` method returning relation names.

---

This documentation serves as a quick reference for the core service logic managing bulk user status updates and media file handling.

-------------------------------------------------

### ProccessCodesService

This service class provides methods to handle verification codes related to user registration and login processes using phone numbers or emails. It manages creating, checking, and validating verification codes, as well as user creation and token generation.

---

## Methods

### `processCode($model, $request, $code, $type, $msg = null)`

**Description:**  
Create or update a verification code record for either phone number or email.

**Parameters:**  
- `$model` (Eloquent Model): The model handling verification codes (e.g., `RegisterCodeNum`, `PasswordReset`).  
- `$request` (array): The request data containing phone or email information.  
- `$code` (string): The verification code to save.  
- `$type` (string): Specifies whether the code is for `'phone_no'` or `'email'`.  
- `$msg` (string|null): Optional message content for sending notifications (commented out in this implementation).

**Returns:**  
The created or updated Eloquent model instance representing the code record.

---

### `checkCode($model, $code)`

**Description:**  
Validate a verification code against stored records, check expiration, and return the corresponding user with a token if valid.

**Parameters:**  
- `$model` (Eloquent Model): The code model to query (e.g., `RegisterCodeNum`).  
- `$code` (string): The verification code to check.

**Behavior:**  
- Retrieves cached registration info (`info_user`).  
- Finds a matching code record using `findCodeUser()`.  
- Checks if the code is expired (older than 1 hour).  
- If the model is `RegisterCodeNum`, it ensures email and phone codes are updated/created, creates or fetches the user, attaches the "user" role, and returns the user with an API token.  
- For other models (e.g., login verification), returns the user model instance.

**Returns:**  
- On error: throw new ApiResponseException(ServiceResponseEnum::BAD_REQUEST). 
- On success: array with `'user'`, `'token'`, and `'code'` (registration) or user instance (login).

---

### `findCodeUser($model, $code, $infoUser)`

**Description:**  
Search for a verification code record by phone number or email.

**Parameters:**  
- `$model` (Eloquent Model): The code model to query.  
- `$code` (string): The code to search for.  
- `$infoUser` (array): Contains user information such as `'phone_no'`, `'country_id'`, and/or `'email'`.

**Returns:**  
- The first matching Eloquent model instance if found, otherwise `null`.

---

## Usage

- Used to process verification codes during registration and login flows.  
- Ensures codes are unique and valid within time limits.  
- Automatically creates users and assigns roles on successful verification (for registration).  
- Facilitates token generation for authenticated access.

---

## Notes

- Depends on cache key `'info_user'` to retrieve the current registration or login session data.  
- Integrates with `User` and `Role` models for user management.  
- Handles both phone and email workflows seamlessly.  
- Can be extended to send SMS or email notifications by uncommenting and configuring `SendingMessagesService`.

---

This class is essential for managing secure, code-based user verification in multi-channel authentication systems.


### SendingMessagesService

This service class handles sending messages via SMS or email using different providers and APIs. It supports sending SMS through the Msegat API, sending emails with Laravel Mail, and includes placeholders for WhatsApp messaging.

---

## Properties

- **`$username`**  
  Msegat API username, loaded from config (`services.msegat.username`).

- **`$password`**  
  Msegat API password (API key), loaded from config (`services.msegat.password`).

- **`$api`**  
  The endpoint URL for Msegat SMS API.

- **`$defaultSender`**  
  Default sender name used for SMS messages.

---

## Methods

### `__construct()`

Initializes the service by loading credentials and setting default values for SMS sending.

---

### `sendSms(string $phoneNumber, string $message, string $sender): bool`

Sends an SMS message via the Msegat API.

- **Parameters:**  
  - `$phoneNumber` — Recipient's phone number including country code.  
  - `$message` — The text message content.  
  - `$sender` — The sender name to appear on the SMS.

- **Returns:**  
  `true` if the SMS was sent successfully, `false` otherwise.

---

### `reminderSms(string $phoneNumber, int $countryId, $data): bool`

Sends an appointment reminder SMS to a user.

- **Parameters:**  
  - `$phoneNumber` — User's phone number (without country code).  
  - `$countryId` — Country ID to retrieve country phone code.  
  - `$data` — Appointment data object (expects `start_time` and `user->full_name`).

- **Returns:**  
  `true` on success, `false` otherwise.

---

### `sendResetSms(string $phoneNumber, int $countryId, string $code): bool`

Sends a password reset code via SMS.

- **Parameters:**  
  - `$phoneNumber` — User's phone number (without country code).  
  - `$countryId` — Country ID to get phone code prefix.  
  - `$code` — Password reset code to send.

- **Returns:**  
  `true` if sent successfully, `false` otherwise.

---

### `sendingMessage(array $data, string $msg = null)`

Sends a message either by email or SMS depending on the provided data.

- **Parameters:**  
  - `$data` — Array that must contain either `'email'` or `'phone_no'`.  
  - `$msg` — Optional message text used when sending SMS.

- **Behavior:**  
  - If `'email'` is set, sends an email using Laravel's Mail with the `General` Mailable class.  
  - If `'phone_no'` and `$msg` are set, sends SMS via `sendSms`.  
  - Returns server error response if neither is present.

- **Returns:**  
  The result of the mail send or SMS send operation.

---

### `sendToPhoneWattsapp(string $phone_no, string $message)`

Placeholder for future implementation to send WhatsApp messages.

---

## Notes

- Uses Laravel's HTTP client (`Http::post`) to interact with the Msegat SMS API.  
- Email sending is done via Laravel's `Mail` facade with a `General` mailable class.  
- The service relies on the `Country` model to retrieve the country phone code prefix.  
- SMS sender name defaults to `"Template"` but can be overridden per message.  
- Contains commented code suggesting potential job dispatching for async email sending.  
- WhatsApp sending functionality is planned but not implemented.

---

This service provides a unified interface for sending notifications to users via multiple channels (SMS and email) in the application.


### SendingNotificationsService

This service class handles sending push notifications to users via Firebase Cloud Messaging (FCM) and manages notification records in the database.

---

#### Methods

##### `sendNotification(array $data, int $user_id, string|null $type = null)`

Sends a push notification to a specific user via Firebase Cloud Messaging (FCM).

- **Parameters:**  
  - `$data` (array): Notification data including keys like `title`, `body`, and optionally `type`.  
  - `$user_id` (int): The ID of the user to whom the notification will be sent.  
  - `$type` (string|null): Optional notification type. If provided, it overrides the type in `$data`.

- **Process:**  
  1. Finds the user by `$user_id`.  
  2. Checks if the user exists and has an `fcm_token`; if not, returns a 404 error response.  
  3. Prepares the notification payload, attaching `user_id` and setting the notification type (`$type` or `$data['type']`).  
  4. Creates a notification database record either before or after sending, depending on whether `$type` is explicitly passed.  
  5. Sends the notification to Firebase FCM via an HTTP POST request using cURL.  
  6. Handles errors in sending and returns an error response if the request fails.  
  7. Returns the decoded response from Firebase on success.

- **Returns:**  
  - Decoded Firebase API response on success.  
  - throw new ApiResponseException(ServiceResponseEnum::FORBIDDEN) error objects on failure or missing user/token.

---

## Additional Details

- **Firebase Server Key:**  
  Loaded from configuration `services.firebase.server_key`.

- **Notification Payload:**  
  Contains both `"notification"` and `"data"` sections to support rich notifications and background data messages.

- **Notification Record:**  
  Stored in the `notifications` table via the `Notification` model, linked to the user with `user_id`.

- **cURL Configuration:**  
  - Sends POST request with JSON payload.  
  - Disables SSL verification (`CURLOPT_SSL_VERIFYPEER => false`) — consider enabling in production.  
  - Sets appropriate HTTP headers for authorization and content type.

---

## Usage

This service allows the application to send real-time push notifications to users' devices registered with Firebase Cloud Messaging, while also keeping a record of notifications sent.

---

## Notes

- This implementation uses PHP cURL directly; consider Laravel HTTP client or a dedicated FCM package for improved maintainability.  
- The notification record is created either before or after sending depending on whether the notification type is explicitly passed.  
- Ensure users have valid `fcm_token` values stored to receive notifications.

---

### TranslationService

The `TranslationService` class provides utility methods for managing multilingual translations of Eloquent models without separate translation tables. It works with models that use `lang` and `translate_id` columns to store translations in the same table.

---

## Overview

- Handles creation and update of translation records related to a main model entry.
- Supports dynamic translation fields and excludes (copies) non-translated fields.
- Uses a global `LanguageScope` for filtering translations by current locale, which is bypassed when querying translations.
- Supports JSON or array input for translation data.
- Supports the two main operations: `'store'` (create) and `'update'` (update or create).

---

## Usage Example

Given translation data like:

```
$data = [
  [
    "lang" => "ar",
    "username" => "يوزر1",
    "full_name" => "يوزر",
    "translate_id" => 90,
    "email" => "student@nnn.5585000",
    "phone_no" => "71115534813410",
    "country_id" => "63",
    "gender" => null,
    "birth_date" => null,
  ],
  // more translations...
];
```

### UserService (Dashboard Auth User Service)

This service class provides business logic for user-related operations in a Laravel application dashboard context, including user creation, updating, activation toggling, deletion, file management, and translation handling.

---

## Overview

- **Create** and **update** users with roles, profiles, file uploads, and multilingual translations.
- Toggle user activation status (single and bulk).
- Soft delete, restore, and force delete single or multiple users.
- Manage user media files: upload single/multiple files and delete single/multiple files.
- Handles translation data via `TranslationService`.
- Enforces role restrictions and permissions for some file operations.

---

## Key Methods

### `store($request, $model): object`

- Creates a user from validated request data.
- Generates a random password.
- Attaches roles and creates a profile.
- Handles translations for the profile.
- Uploads single or multiple media files.
- Returns the created user with eager-loaded relations if defined.

### `update($request, $id, $model ): object`

- Updates a user by ID with validated data.
- Syncs roles.
- Updates translations.
- Uploads media files if provided.
- Returns the updated user with eager-loaded relations.

### `changeActivate($id, $model ): object`

- Toggles the `is_active` status between active and not active.
- Returns the updated user wrapped in a success response.

### `changeActivateMany($model): array`

- Bulk activate multiple users.
- Calls internal bulk activation handler.

### `destroy($id, $model ): object`

- Soft deletes a single user.
- Returns the deleted user in a success response.

### `destroyMany($model): object`

- Soft deletes multiple users.
- Uses internal bulk restore/delete handler.

### `restore($request, $id, $model ): object`

- Restores a soft-deleted user by ID.
- Returns restored user in success response.

### `forceDelete($id, $model ): void`

- Permanently deletes a soft-deleted user.

### `forceDeleteMany($model): object`

- Permanently deletes multiple users.
- Uses internal bulk restore/delete handler.

### `handleBulkAction($model, string $action): JsonResponse`

- Bulk handles soft deletes, restores, or force deletes.
- Supports input `"all"` or specific ID arrays.
- Returns processed IDs and not found IDs in a standardized response.

### `handleBulkAction($model)`

- Bulk activates, deactivates, or toggles user activation statuses.
- Accepts `"all"` or list of IDs and an action input (`activate`, `deactivate`, or `toggle`).
- Tracks and returns updated, failed, and not found IDs.
- Loads eager relationships if defined.

---

## File Management Methods

### `uploadFile($request, $id, $model )`

- Upload a single image file for a user, excluding users with main role.
- Returns updated user with eager loading.

### `uploadFiles($request, $id, $model )`

- Upload multiple files for a user, excluding users with main role.
- Returns updated user with eager loading.

### `deleteFile($id, $model )`

- Delete the single image file for a user (if supported).
- Returns updated user with eager loading.

### `deleteFiles($id, $model )`

- Delete specific or all files from user's file collection.
- Restricts access to users with main role.
- Returns updated user with eager loading.

---

## Notes

- The service uses a repository pattern and expects `$model` parameters for Eloquent models.
- Methods use a centralized $data for consistent API responses.
- Bulk operations support JSON-encoded IDs or the string `"all"` to target all records.
- Role restrictions are enforced via `RoleHelper` to protect certain user types.
- Supports eager loading relations configured on the model via `getProp('eagerLoading')`.

---

## Example Usage

```php
// Create user service instance via dependency injection
$userService = app(\App\Services\Dashboard\Auth\User\UserService::class);

// Create a user
$createdUser = $userService->store($request, new User());

// Update a user
$updatedUser = $userService->update($request, $userId, new User());

// Toggle activation
$response = $userService->changeActivate($userId, new User());

// Bulk activate users
$response = $userService->changeActivateMany(new User());

// Upload files for a user
$response = $userService->uploadFiles($request, $userId, new User());


```
