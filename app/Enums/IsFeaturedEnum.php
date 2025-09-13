<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum IsFeaturedEnum
 *
 * Represents featured/infeatured states.
 *
 * Example:
 * ```php
 * $status = IsFeaturedEnum::FEATURED;
 * echo $status->text(); // "Featured"
 * ```
 */
enum IsFeaturedEnum: int
{
    use EnumOptionsTrait; // Provides helper methods for enums

    case FEATURED = 1;      // Featured state
    case INFEATURED = 0;  // Infeatured state

}
