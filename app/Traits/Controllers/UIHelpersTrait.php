<?php

namespace App\Traits\Controllers;

use App\Traits\Controllers\FilterFrontTrait;
use App\Traits\Controllers\InertiaShareTrait;
use App\Traits\Controllers\SetsBreadcrumbsTrait;

/**
 * Trait for UI rendering helpers (Inertia, breadcrumbs, etc.)
 *
 * Includes:
 * - InertiaShareTrait (sharing props with frontend -> breadcrumb , pageTitle, allowSearch)
 */
trait UIHelpersTrait
{
    use FilterFrontTrait;
    use InertiaShareTrait;
    use SetsBreadcrumbsTrait;
}

