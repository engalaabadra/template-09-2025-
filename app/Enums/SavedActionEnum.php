<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

enum SavedActionEnum: string
{
    use EnumOptionsTrait;

    case SAVE = 'save';
    case UNSAVE = 'unsave';
    case TOGGLE = 'toggle';
}
