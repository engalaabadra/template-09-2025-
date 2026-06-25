<?php

namespace App\Enums;

/**
 * Enum ServiceResponseEnum
 *
 * Represents standardized service response types with:
 * - A translation message
 * - Corresponding HTTP status codes
 *
 * Example:
 * $response = ServiceResponseEnum::SUCCESS;
 * echo $response->message(); // "Operation completed successfully"
 * echo $response->status();  // 200
 */
enum ServiceResponseEnum: string
{
    case SUCCESS = 'success';
    case NOT_FOUND = 'not_found';
    case FORBIDDEN = 'forbidden';
    case BAD_REQUEST = 'bad_request';
    case UNAUTHORIZED = 'unauthorized';
    case UNPROCESSABLE_ENTITY = 'unprocessable_entity';
    case SERVER_ERROR = 'server_error';

    public function message(): string
    {
        return __('service_responses.' . $this->value);
    }

    public function status(): int
    {
        return match ($this) {
            self::SUCCESS => 200,
            self::NOT_FOUND => 404,
            self::FORBIDDEN => 403,
            self::BAD_REQUEST => 400,
            self::UNAUTHORIZED => 403,
            self::UNPROCESSABLE_ENTITY => 422,
            self::SERVER_ERROR => 500,
        };
    }
}
