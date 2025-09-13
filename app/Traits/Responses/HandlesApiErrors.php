<?php

namespace App\Traits\Responses;

use Illuminate\Http\JsonResponse;

trait HandlesApiErrors
{
    /**
     * Return a standardized JSON error response.
     *
     * @example
     *  {
     *       "status": false,
     *       "message": "Validation failed.",
     *       "errors": {
     *           "email": ["The email field is required."],
     *           "password": ["The password must be at least 8 characters."]
     *       }
     *   }
     * @param  string  $message
     * @param  int     $status  HTTP status code (default 500)
     * @param  array   $extra   Additional data to include in the response
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message = 'Error.', int $status = 500, array $extra = []): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if (!empty($extra) && is_array($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, $status);
    }
}
