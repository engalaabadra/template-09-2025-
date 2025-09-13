<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum IsSaveBookEnum
 *
 * Represents active/inactive states.
 *
 * Example:
 * ```php
 * $status = IsSaveBookEnum::SAVE;
 * echo $status->text(); // "Save"
 * ```
 */
enum IsSaveBookEnum: int
{
    use EnumOptionsTrait; // Provides helper methods for enums

    case SAVE = 1;      // Save state
    case UNSAVE = 0;  // unsave state

}
