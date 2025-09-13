<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum IsAuthorEnum
 *
 * Represents author/inauthor states.
 *
 * Example:
 * ```php
 * $status = IsAuthorEnum::AUTHOR;
 * echo $status->text(); // "Author"
 * ```
 */
enum IsAuthorEnum: int
{
    use EnumOptionsTrait; // Provides helper methods for enums

    case AUTHOR = 1;      // Author state
    case NOT_AUTHOR = 0;  // not_author state

}
