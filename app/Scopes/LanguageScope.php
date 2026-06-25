<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to automatically filter records by the current application language.
 * This ensures that only rows matching the current locale (from config) are retrieved.
 */
class LanguageScope implements Scope
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
        // Add a condition to only retrieve records where 'lang' matches the app's locale
        $builder->where('lang', localeLang());
    }
}
