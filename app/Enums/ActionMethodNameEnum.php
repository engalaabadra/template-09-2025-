<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

/**
 * use in determine action method in param internal method 
 * 
 * like: $user->handleFiles($request, $model, $user, 'update')
 * 
 */
enum ActionMethodNameEnum: string
{
    use EnumOptionsTrait;

    case UPDATE = 'update';
    case STORE = 'store';

}
