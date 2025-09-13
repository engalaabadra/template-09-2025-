<?php

namespace App\Enums;

/**
 * use in activation , restoring operation
 */
enum StrategyActionEnum: string
{
    case MODIFY = 'modify';
    case REPLACE = 'replace';
    case PREVENT = 'prevent';
}
