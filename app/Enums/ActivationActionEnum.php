<?php

namespace App\Enums;

/**
 * Enum ActivationActionEnum
 *
 * Defines actions for changing a model’s activation state:
 * - ACTIVATE   → Mark as active.
 * - DEACTIVATE → Mark as inactive.
 * - TOGGLE     → Flip current state (active ↔ inactive).
 *
 * Benefits:
 * - Avoids magic strings.
 * - Ensures type safety & readability.
 *
 * Example:
 * ```php
 * $action = ActivationActionEnum::TOGGLE;
 * if ($action === ActivationActionEnum::ACTIVATE) {
 *     $model->is_active = true;
 * }
 * ```
 */

enum ActivationActionEnum: string
{
    case ACTIVATE = 'activate';
    case DEACTIVATE = 'deactivate';
    case TOGGLE = 'toggle';
}
