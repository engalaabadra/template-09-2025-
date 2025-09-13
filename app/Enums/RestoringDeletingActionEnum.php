<?php

namespace App\Enums;

enum RestoringDeletingActionEnum: string
{
    case RESTORE = 'restore';
    case DESTROY = 'destroy';
    case FORCE_DELETE = 'force_delete';
}
