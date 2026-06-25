<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to automatically filter only active records.
 * This scope will append `where is_active = true` to all model queries
 * that use this scope, ensuring only active rows are retrieved.
 */
class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder  The query builder instance.
     * @param  \Illuminate\Database\Eloquent\Model    $model    The model being queried.
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Add a condition to only retrieve records where 'is_active' is true
        $builder->where('is_active', '=', true);
    }
}
