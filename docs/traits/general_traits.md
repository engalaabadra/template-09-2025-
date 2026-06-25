
## Response Traits

## WebApiSuccessResponseTrait
This trait provides a **unified response** to handle **successful responses** for both **API (JSON)** and **Web (Redirect)** requests in Laravel controllers.  

It supports:
- Pagination results
- Collections
- Single model instances
- Optional Laravel Resource wrapping
- Custom success messages
- Redirects with flash messages (for Web requests)

---

## Methods Overview

---

#### `respond($data = null, $message = null, string $resourceClass = null, ?string $redirectRoute = null)`

Returns a unified response depending on the request type (API or Web).

**Parameters:**
- `$data` : Data to return.
- `$message` : Optional success message.
- `$resourceClass` : Optional resource class name for formatting.
- `$redirectRoute` : Route name to redirect to for web responses.

**Returns:**
- `JsonResponse` for API requests.
- `RedirectResponse` for Web requests.

---

#### `handleApi($data, $message = null, ?string $resourceClass = null)`

Formats and returns a structured API JSON response.

**Supported cases:**
- Paginated data (with or without Resource).
- Laravel Collections (with or without Resource).
- Single model or data item (with or without Resource).
- Direct string/URL responses.

**Returns:**  
`JsonResponse` with a standardized success format.

---

#### `handleWeb($data, $message = null, ?string $redirectRoute = null)`

Handles Web responses via redirect with a flash success message.

**Returns:**  
`RedirectResponse` to a specific route or back to the previous page.

---

#### `jsonSuccessResponse($data = null, string $message = null)`

Creates a standardized JSON success response.

**Returns:**  
`JsonResponse` containing:
```json
{
  "status": true,
  "message": "Success message",
  "data": [...]
}
```



## Example Usage in Controller

```php
use App\Traits\Controllers\WebApiSuccessResponseTrait;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    use WebApiSuccessResponseTrait;

    public function index()
    {
        $users = User::paginate(10);
        return $this->respond($users, 'Users fetched successfully', UserResource::class);
    }

    public function store(Request $request)
    {
        $user = User::create($request->all());
        return $this->respond($user, 'User created successfully', UserResource::class);
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->all());
        // Redirect to users.index with flash success message (for web requests)
        return $this->respond(null, 'User updated successfully', null, 'users.index');
    }
}
```

---

### JsonArrayFieldsHandlerTrait

This trait helps decode JSON-encoded string fields into PHP arrays during request validation preparation, especially useful for handling array inputs sent via multipart/form-data requests (like file uploads), where array fields arrive as JSON strings (e.g., `"[1,2,3]"`) instead of traditional repeated keys.

---

***Usage***

- Include this trait in your FormRequest class.
- Call `decodeJsonArrayFields(['field1', 'field2'])` inside the `prepareForValidation()` method.
- It will automatically detect if the specified fields are JSON strings representing arrays and decode them to native PHP arrays before validation.

---

***Example***

```php
use App\Traits\Requests\JsonArrayFieldsHandlerTrait;

class UserRequest extends FormRequest
{
    use JsonArrayFieldsHandlerTrait;

    protected function prepareForValidation()
    {
        $this->decodeJsonArrayFields(['roles', 'tags']);
    }
}

```
### HandlesServiceTransactions Trait

This trait enables automatic wrapping of specified service methods inside a database transaction. It intercepts calls to methods listed in `$transactionalMethods` and executes them within a `DB::transaction()` to ensure atomic operations.

---

***Features***

- Defines a list of method names (`store`, `update`, `destroy`, `restore` by default) that should run inside a database transaction.
- Uses PHP’s magic `__call` method to intercept calls to these methods, when these methods be protected not public to be not visicle in class , in this time eill calling this magic method __call() , to excute DB::transaction in these method (`store`, `update`, `destroy`, `restore`)
- Wraps the intercepted method execution inside a transaction using Laravel’s `DB::transaction()`.
- For other methods not listed, it calls them normally without a transaction.

---

***Usage***

Simply include the trait in your service class:

```php
use App\Traits\Services\HandlesServiceTransactions;

class UserService
{
    use HandlesServiceTransactions;

    protected array $transactionalMethods = ['store', 'update'];

    protected function store(array $data)
    {
        // Your store logic here, automatically wrapped in a transaction
    }

    protected function update(int $id, array $data)
    {
        // Your update logic here, automatically wrapped in a transaction
    }
}
```

# AuthUserTrait

Trait for retrieving the currently authenticated user from multiple guards (`admin-api` or `api`).

**Usage:**

```php
$user = $this->getAuthUser();
```

Returns the first authenticated user or `null` if none.

```php
public function getAuthUser()
    {
        // Define guards to check in order of priority
        $guards = ['admin-api', 'api']; 

        // Loop through guards until a user is found
        foreach ($guards as $guard) {
            if ($user = auth($guard)->user()) {
                return $user; // return the first authenticated user
            }
        }

        return null; // No user found in any guard
    }
```