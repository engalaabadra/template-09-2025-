# User Resource Eager Loading Flow

This is a detailed example showing the flow of eager loading, relationships, and resources for the `User` model in Laravel.

```php
<?php

use App\Models\User;
use App\Resources\UserResource;

// Step 1: Fetch a user with eager loaded relationships
$user = User::with(['profile', 'country', 'roles', 'files', 'image'])->find(1);

// Step 2: Wrap the User model in a Resource
$userResource = new UserResource($user);

// Step 3: Convert the resource to array (for API response)
$responseArray = $userResource->toArray(request());

// Step 4: Return JSON response
return response()->json($responseArray);
```

---

### Flow Explanation

1. **Eager Loading Relationships**

```php
$user = User::with(['profile', 'country', 'roles', 'files', 'image'])->find(1);
```

- Loads the `User` model with its relationships in a single query to avoid N+1 problem.
- Relationships: `profile`, `country`, `roles`, `files`, `image`.

2. **Wrapping with Resource**

```php
$userResource = new UserResource($user);
```

- Creates an instance of `UserResource`, which extends `BaseResource`.
- `UserResource` defines `$relationsResources` mapping relationships to their corresponding resources.

```php
public array $relationsResources = [
    'roles' => RoleResource::class,
    'profile' => ProfileResource::class,
    'files' => FileResource::class,
    'country' => CountryResource::class,
];
```

3. **Conversion to Array (`toArray`)**

```php
$responseArray = $userResource->toArray(request());
```

- `BaseResource::toArray()` checks if the resource is already an array.
- If it's an Eloquent model, it calls `$this->getAttributesInFillable($relationsResources)`.

4. **Trait: `SmartAttributesTrait`**

- Filters only fillable attributes and `id`.
- Appends dynamic `*_text` accessors.
- Loops through loaded relationships:
  - If a Resource class is defined for the relation, wrap it with that resource.
  - If no resource class, recursively call `getAttributesInFillable` for related models.
  - Handles both single models and collections.

5. **Final Output**

- `$responseArray` contains:
  - Only fillable attributes + `id`.
  - Dynamic `*_text` accessors.
  - Related resources for `profile`, `country`, `roles`, `files`, and `image`.

Example JSON output:

```json
{
    "id": 1,
    "email": "user@example.com",
    "phone_no": "123456789",
    "is_active": true,
    "roles": [
        {
            "id": 1,
            "name": "Admin"
        }
    ],
    "profile": {
        "id": 10,
        "username": "John Doe",
        "translations": []
    },
    "files": [
        {
            "id": 100,
            "filename": "resume.pdf"
        }
    ],
    "country": {
        "id": 5,
        "name": "USA"
    }
}
```

