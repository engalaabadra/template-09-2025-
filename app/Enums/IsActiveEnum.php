<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum IsActiveEnum
 *
 * Represents active/inactive states.
 *
 * Example:
 * ```php
 * $status = IsActiveEnum::ACTIVE;
 * echo $status->text(); // "Active"
 * ```
 */
enum IsActiveEnum: int
{
    use EnumOptionsTrait; // Provides helper methods for enums

    case ACTIVE = 1;      // Active state
    case NOT_ACTIVE = 0;  // Inactive state

}
