<?php

namespace App\Traits\Observers;

use App\Exceptions\ApiResponseException;
use App\Enums\ServiceResponseEnum;

trait ProtectedActionsTrait
{
    /**
     * Throw standardized exception for protected models.
     *
     * @param mixed $model
     * @param string $action
     * @throws ApiResponseException
     */
    protected function throwProtectedException($model, string $action = null): void
    {
        throw new ApiResponseException(
            ServiceResponseEnum::FORBIDDEN,
            __('messages.can_not_make_this_action:' . ($action))
        );
    }
}
