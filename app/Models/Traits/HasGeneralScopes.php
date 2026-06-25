<?php

namespace App\Models\Traits;

use App\Scopes\GeneralScopes;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Schema;

trait HasGeneralScopes
{
    /**
     * Boot the global scopes for any model using this trait.
     * 
     * - Applies ActiveScope for User models if `is_active` column exists.
     * - Applies GeneralScopes for all other models.
     * - Works safely even if model has custom Eloquent Builder.
     */    
    protected static function bootHasGeneralScopes(): void
    {
        $modelInstance = new static;

        if ($modelInstance instanceof \App\Models\User || $modelInstance instanceof \App\Models\Role) {
            // Dashboard → no scopes applied
            if (request()->is('dashboard/*') || (request()->is('api/dashboard/*') && adminApi())) {
                return;
            }

            if (Schema::hasColumn($modelInstance->getTable(), 'is_active')) {
                static::addGlobalScope(new ActiveScope);
            }

            return;
        }

        static::addGlobalScope(new GeneralScopes);
    }
}
