<?php

namespace App\Exceptions;

use App\Enums\ServiceResponseEnum;

/**
 * This custom exception class is designed to standardize all API error responses
 * within our application. 
 *
 * Instead of throwing Laravel's default exceptions directly, we throw this class
 * to ensure all errors follow the same response structure and format.
 * 
 * By using this class, we maintain full control over:
 *  - HTTP status codes - Main message - Errors array - Additional data
 *
 * This ensures consistency across all API responses and simplifies error handling.
 * for API errors, including HTTP status code, message, errors array, and data array.
 *
 * Example usage:
 *
 * Throwing validation errors (400 Bad Request):
 * throw new ApiResponseException(
 *     400,
 *     'Validation failed',
 *     [
 *         'email' => ['The email field is required.'],
 *         'password' => ['The password must be at least 8 characters.']
 *     ],
 *     []
 * );
 *
 * Throwing server error (500 Internal Server Error) with default message:
 * throw new ApiResponseException(
 *     500
 * );
 *
 * Throwing with extra data but no errors:
 * throw new ApiResponseException(
 *     400,
 *     'Invalid request',
 *     [],
 *     ['request_id' => 123]
 * );
 */

class ApiResponseException extends \Exception
{

    /**
     * HTTP status code for the response.
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Array of validation or processing errors.
     *
     * @var array
     */
    protected array $errors;

    /**
     * Additional data to return in the response.
     *
     * @var array
     */
    protected array $data;

    /**
     * The main API message to return.
     *
     * @var string
     */
    protected string $apiMessage;

    /**
     * Constructor for the exception.
     *
     * @param int         $status   HTTP status code.
     * @param string|null $message  Custom message (optional).
     * @param array       $errors   List of validation or processing errors.
     * @param array       $data     Additional data to return.
     */
    public function __construct(ServiceResponseEnum $enum, string $message = null, array $errors = [], array $data = [])
    {
        $status = $enum->status();

        if (empty($message)) {
            $message = $enum->message();
        }

        $this->statusCode = $status;
        $this->apiMessage = $message;
        $this->errors = $errors;
        $this->data = $data;

        parent::__construct($message, $status);
    }

    /**
     * Render the exception into an HTTP JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        // Start building the base response
        $response = [
            'status' => false,
            'message' => $this->apiMessage,
        ];

        // Include errors if they are not empty, or if config says show empty fields
        if (!empty($this->errors)) {
            $response['errors'] = (object) $this->errors;
        }

        // Include data if not empty, or if config says show empty fields
        if (!empty($this->data)) {
            $response['data'] = (object) $this->data;
        }

        return response()->json($response, $this->statusCode);
    }
}
