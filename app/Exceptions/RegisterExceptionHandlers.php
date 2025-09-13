<?php
namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Exceptions\ApiResponseException;
use App\Traits\Responses\HandlesApiErrors;
use Illuminate\Database\QueryException;

/**
 * Exception Handlers Configuration
 *
 * This file centralizes how all exceptions are handled and converted
 * into JSON API responses. Instead of returning Laravel's default
 * HTML error pages, every error is normalized into a unified JSON format.
 *
 * Why:
 * - Ensures a consistent API response structure across the entire app.
 * - Simplifies error handling for frontend clients (no need to parse HTML).
 * - Gives fine-grained control over HTTP status codes and messages.
 *
 * Key Behaviors:
 * 1. ValidationException (422)
 *    → Returns detailed validation errors for each input field.
 *
 * 2. AuthenticationException (401)
 *    → Returned when the user is not logged in.
 *
 * 3. AuthorizationException / AccessDeniedHttpException (403)
 *    → Returned when user lacks permission or role to perform an action.
 *
 * 4. ModelNotFoundException (404)
 *    → Returned when a requested record does not exist in DB.
 *
 * 5. NotFoundHttpException (404)
 *    → Returned when a route (URL) does not exist.
 *
 * 6. MethodNotAllowedHttpException (405)
 *    → Returned when the HTTP verb (GET/POST/etc.) is invalid for the route.
 *
 * 7. QueryException (500 / 403)
 *    → Returns DB errors. Custom handling for MySQL SIGNAL (45000, 1644) 
 *      to return developer-defined DB messages with status 403.
 *
 * 8. PDOException (500)
 *    → Handles DB connection failures (with optional debug info).
 *
 * 9. HttpException (4xx/5xx)
 *    → Generic fallback for any HTTP-related errors.
 *
 * 10. ApiResponseException (custom)
 *    → Allows throwing project-specific errors with custom messages and codes.
 *
 * 11. Throwable (500)
 *    → Catch-all for any unexpected error; hides details unless debug mode is enabled.
 *
 * Example JSON Response:
 * {
 *   "status": false,
 *   "message": "Validation failed",
 *   "errors": {
 *     "email": ["The email field is required."]
 *   },
 *   "data": []
 * }
 *
 * Notes:
 * - Uses HandlesApiErrors trait for consistent response formatting.
 * - Status codes strictly follow RESTful conventions.
 * - In production (debug = false), sensitive system details are hidden.
 */

return function (Exceptions $exceptions) {
    $helper = new class {
        use HandlesApiErrors; // Mixin for reusable API error responses
    };

    // 1. Handle validation errors (e.g., invalid email format, missing fields)
    $exceptions->renderable(function (ValidationException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            
            return $helper->errorResponse('Validation failed', 422, [
                'errors' => $e->errors(), // Returns array of field-specific errors
            ]);
        }
    });

    // 2. Handle unauthenticated user access
    $exceptions->renderable(function (AuthenticationException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Unauthenticated', 401);
        }
    });

    // 3. Handle unauthorized actions (logged in but no permission)
    $exceptions->renderable(function (AuthorizationException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Unauthorized action.', 403);
        }
    });

    // 4. Handle missing model records (e.g., User::find(999))
    $exceptions->renderable(function (ModelNotFoundException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Resource not found.', 404);
        }
    });

    // 5. Handle missing routes (404)
    $exceptions->renderable(function (NotFoundHttpException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Route not found.', 404);

            // return response()->json([
            //     'status'  => false,
            //     'message' => 'Route not found.',
            // ], 404);
        }
    });

    // 6. Handle invalid database queries
    $exceptions->renderable(function (QueryException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Database query error.', 500, [
                'errors' => config('app.debug') ? $e->getMessage() : null, // Show details in debug mode only
            ]);
            
            // return response()->json([
            //     'status'  => false,
            //     'message' => 'Database query error.',
            //     'error'   => config('app.debug') ? $e->getMessage() : null, // Show details in debug mode only
            // ], 500);
        }
    });

    // 7. Handle database connection issues
    $exceptions->renderable(function (PDOException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Database connection error.', 500, [
                'errors' => config('app.debug') ? $e->getMessage() : null, // Show details in debug mode only
            ]);

        }
    });


    // 8. Handle invalid HTTP methods (e.g., GET instead of POST)
    $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('HTTP method not allowed.', 405);
        }
    });

    // 9. Handle access denial (user lacks role)
    $exceptions->renderable(function (AccessDeniedHttpException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Access denied.', 403);
        }
    });

    // 10. Generic HTTP exception handler
    $exceptions->renderable(function (HttpException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse($e->getMessage() ?: 'HTTP error occurred.', $e->getStatusCode());
        }
    });

    // 11. Optional: Custom project exception using ApiResponseException
    $exceptions->renderable(function (ApiResponseException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse($e->getMessage(), $e->status ?? 500);

        }
    });

    // 12. Optional: Catch-all for any unhandled exceptions
    $exceptions->renderable(function (Throwable $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            return $helper->errorResponse('Server error.', 500, [
            'errors' => config('app.debug') ? $e->getMessage() : null
            ]);

        }
    });
    $exceptions->renderable(function (QueryException $e, $request) use ($helper) {
        if ($request->expectsJson()) {
            $message = $e->getMessage();

            // if error SIGNAL SQLSTATE[45000]
            if (preg_match('/SQLSTATE\[45000\]:.*1644\s(.+)/', $message, $matches)) {
                return $helper->errorResponse(
                    trim($matches[1]) ?? 'Custom database error.',
                    403
                );
            }

            return $helper->errorResponse('Database query error.', 500, [
                'errors' => config('app.debug') ? $message : null,
            ]);
        }
    });

};
