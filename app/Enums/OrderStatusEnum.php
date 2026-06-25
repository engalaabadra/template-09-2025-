<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

enum OrderStatusEnum: string
{
    use EnumOptionsTrait;

    case PENDING = 'pending';
    case INPROGRESS = 'in-progress';
    case COMPLETED = 'completed';

}
