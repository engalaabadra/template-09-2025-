<?php

namespace App\Enums;

use App\Models\Traits\EnumOptionsTrait;

enum ProfileGenderEnum: string
{
    use EnumOptionsTrait;
    
    case Male = 'male';
    case Female = 'female';
}
