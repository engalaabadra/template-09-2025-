<?php

namespace App\Enums;


/**
 * Enum ActivatedRestoredActionEnum
 *
 * Defines actions for when a model is re-enabled:
 * - _ACTIVATED → model activated.
 * - _RESTORED  → model restored from soft delete.
 *
 * Example:
 * $action = ActivatedRestoredActionEnum::_ACTIVATED;
 * echo $action->value; // "_activated"
 */
enum ActivatedRestoredActionEnum: string
{
    case _ACTIVATED = '_activated';
    case _RESTORED = '_restored';
}
