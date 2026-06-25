<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum FileTypeEnum
 *
 * Represents IMAGE/FILE states.
 *
 * Example:
 * ```php
 * $fileType = FileTypeEnum::FILE;
 * echo $fileType->text(); // "FILE"
 * ```
 */
enum FileTypeEnum: string
{
     use EnumOptionsTrait; // Provides helper methods for enums

    case FILE = 'file';      // file state
    case IMAGE = 'image';  // image state

}
