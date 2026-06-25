<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * Enum ContentTypeEnum
 *
 * Represents book/article states.
 *
 * Example:
 * ```php
 * $contentType = ContentTypeEnum::ARTICLE;
 * echo $contentType->text(); // "ARTICLE"
 * ```
 */
enum ContentTypeEnum: string
{
     use EnumOptionsTrait; // Provides helper methods for enums

    case ARTICLE = 'article';      // article state
    case BOOK = 'book';  // book state

}
