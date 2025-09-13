## Exceptions
#### App\Exceptions\ApiResponseException

A custom Laravel exception to return **consistent JSON responses** for API errors.  
It supports:
- HTTP status codes
- Translated or custom error messages
- Validation or processing errors
- Extra data in the response
- Option to hide empty fields

---

## Purpose

Instead of returning error responses manually in every controller,  
this exception provides a **standardized structure** for all API error responses.

---

## JSON Response Structure

```json
{
  "status": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  },
  "data": {
    "extra": "info"
  }
}
```
### Example Usage

#### 1️⃣ Throwing Validation Errors (400 Bad Request)
```php
throw new ApiResponseException(
    400,
    'Validation failed',
    [
        'email' => ['The email field is required.'],
        'password' => ['The password must be at least 8 characters.']
    ],
    []
);
```
#### 2️⃣ Throwing Server Error (500 Internal Server Error) with Default Message
```php
throw new ApiResponseException(
    500
);

```
#### 3️⃣ Throwing with Extra Data but No Errors
```php
throw new ApiResponseException(
    400,
    'Invalid request',
    [],
    ['request_id' => 123]
);
```
this instead write this:
```php
    throw response()->json([
        'message' => 'Invalid request',
        'errors' => [],
        'data' => ['request_id' => 123]
    ], 400);
```

 but in private response to be unified in whole project

***example result***
```php
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    },
    "data": {}
}

```
#### App\Exceptions\RegisterExceptionHandlers 
***Laravel API Exception Handlers***

This file defines **custom exception handlers** for a Laravel application to ensure all API error responses are returned in a consistent JSON format.

Its purpose is to ensure that all exceptions return a consistent JSON format, making it easier for clients (front-end or mobile apps) to handle errors uniformly.

---

## How It Works

- Receives an `Exceptions` object.
- Uses a trait `HandlesApiErrors` to provide a reusable method for standardized JSON error responses.
- Defines multiple `renderable` handlers for different exception types.
- Each handler checks if the request expects JSON (`$request->expectsJson()`).
- If yes, it returns a JSON error response with an appropriate HTTP status code.

---


####  Purpose
- Make all API error messages consistent.
- Improve developer and client experience with clear, predictable responses.
- Control the level of detail shown to the user (especially in `debug` mode).

---

## Handled Exception Types

**ValidationException**  
   Handles validation errors (e.g., missing or invalid input fields).  
   **HTTP Status:** 422  
   **Example Response:**
   ```json
   {
     "status": false,
     "message": "Validation failed",
     "errors": {
       "email": ["The email field is required."]
     }
   }
#### Example Usage

### Example: Accessing a non-existent route
**Request:**
```bash
GET /api/unknown-route

// output
{
    "status": false,
    "message": "Route not found."
}
```
