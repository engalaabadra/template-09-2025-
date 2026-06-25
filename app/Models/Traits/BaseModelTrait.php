<?php

namespace App\Models\Traits;

use App\Models\Traits\PaginatableTrait;
use App\Models\Traits\EnumOptionsTrait;
use App\Models\Traits\Accessors\SmartAttributesTrait;
use App\Models\Traits\MorphModelTriggerTrait;
use App\Models\Traits\HasGeneralScopes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ReportableTrait;
use App\Models\Traits\OverridesQueryTrait;
use App\Models\Traits\ForceCascadeDeleteTrait;
use App\Traits\Services\AuthUserTrait;
use App\Models\Traits\SafePropsTrait;
use App\Models\Traits\FillableTrait;

/**
 * Trait BaseModelTrait
 *
 * Combines a powerful set of reusable traits that enhance Laravel Eloquent models with
 * commonly used features and application-wide behaviors.
 *
 * Recommended for all base models via inheritance from a `BaseModel`.
 *
 * Included Traits:
 * ----------------
 *
 * @mixin \App\Models\Traits\EnumOptionsTrait
 * @mixin \App\Models\Traits\MorphModelTriggerTrait
 * @mixin \App\Models\Traits\SmartAttributesTrait
 * @mixin \App\Models\Traits\HasGeneralScopes
 * @mixin \App\Models\Traits\SafePropsTrait
 * @mixin \App\Models\Traits\SmartAttributesTrait
 * @mixin \App\Traits\Services\AuthUserTrait
 *
 * 
 * Breakdown:
 * ----------
 *
 * 
 * - AuthUserTrait –Provides a helper method to fetch the currently authenticated user
 * 
 * - SmartAttributesTrait – * Enhances Eloquent models by:
 *  - auto-casts dynamically to the model & Merge $autoCasts (defined in model) with $casts .
 *  - handle dynamic *_text attributes (formatting it)
 * - Remove any columns explicitly excluded from dynamic *_text handling, like 'id_text
 * - Caching table columns to optimize schema lookups.

 * 
 * - EnumOptionsTrait – Provides utilities for retrieving enum option lists.

 * - MorphModelTriggerTrait
 *   - Handles polymorphic callbacks, useful for logging or media relations.
 *
 * - SmartAttributesTrait
 *   - filters fillable attributes, *_text accessors, relations recursively, and input data by $fillable.
 * 
 * - HasGeneralScopes
 *   - Provides shared scopes like `->active()`, `->inactive()`, and `->whereLang()`.
 *   - Adds a global scope for language filtering if enabled.
 *
 * - ReportableTrait
 *  Provides a flexible way to generate aggregated reports with optional filtering and joining.
 * - ForceCascadeDeleteTrait
 * Automatically deletes related records when deleting a model
 * - SafePropsTrait – Provides a safe way to access static properties dynamically.
*   - Allows calling any static property as a method (e.g., User::eagerLoading()).
*   - Returns the property value if it exists, or a default fallback (empty array or passed value) if it doesn’t.
*   - Useful for models, services, or any class where static properties may or may not exist.

 * 
*/

trait BaseModelTrait
{
    use AuthUserTrait;
    use SmartAttributesTrait;
    use FillableTrait;
    use EnumOptionsTrait;
    use MorphModelTriggerTrait;
   use HasGeneralScopes;
    use SafePropsTrait;
    use ReportableTrait;
    use ForceCascadeDeleteTrait;
}
