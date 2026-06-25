<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

enum PaymentMethodStatusEnum: string
{
    use EnumOptionsTrait;

    case ACTIVE = 'active';       // Payment method is available
    case INACTIVE = 'inactive';   // Payment method is not available
    case PENDING = 'pending';     // Payment method is temporarily pending or requires activation
    case DISABLED = 'disabled';   // Payment method is permanently disabled

}
