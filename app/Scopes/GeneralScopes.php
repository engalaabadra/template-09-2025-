<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use App\Scopes\ActiveScope;
use App\Scopes\LanguageScope;

/**
 * Class GeneralScopes
 *
 * This global scope is applied to models in order to:
 * - Exclude records based on `user_id` ownership (for non-dashboard routes).
 * - Apply general filters such as `is_active` and `lang`.
 * - Skip applying the scope for dashboard routes.
 */
class GeneralScopes implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder  The query builder instance.
     * @param  Model    $model    The Eloquent model instance.
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        
        $userAuth = userApi();
        $adminAuth = adminApi();

        // 1. Dashboard → ### no scopes applied ###
        if (request()->is('dashboard/*') || request()->is('api/dashboard/*') && adminApi()) {
            return;
        }

        // 2. Non-dashboard + guest → *** apply global scopes ***
        if (!$userAuth) {
            if (Schema::hasColumn($model->getTable(), 'is_active')) {
                (new ActiveScope)->apply($builder, $model);
            }

            if (Schema::hasColumn($model->getTable(), 'lang')) {
                (new LanguageScope)->apply($builder, $model);
            }

            return;
        }

        // 3. Non-dashboard + authenticated user
        $hasUserId = Schema::hasColumn($model->getTable(), 'user_id');

        if ($hasUserId) {
            $builder->where(function ($q) use ($model, $userAuth) {
                // Records owned by current user → bypass scopes
                $q->where($model->getTable() . '.user_id', $userAuth->id);
            })->orWhere(function ($q) use ($model, $userAuth) {
                // Records not owned by current user → apply scopes
                $q->where(function ($q2) use ($model, $userAuth) {
                    $q2->where($model->getTable() . '.user_id', '!=', $userAuth->id)
                    ->orWhereNull($model->getTable() . '.user_id');
                });

                if (Schema::hasColumn($model->getTable(), 'is_active')) {
                    (new ActiveScope)->apply($q, $model);
                }

                if (Schema::hasColumn($model->getTable(), 'lang')) {
                    (new LanguageScope)->apply($q, $model);
                }
            });
        } else {
            // If no user_id column, just apply the other scopes
            if (Schema::hasColumn($model->getTable(), 'is_active')) {
                (new ActiveScope)->apply($builder, $model);
            }

            if (Schema::hasColumn($model->getTable(), 'lang')) {
                (new LanguageScope)->apply($builder, $model);
            }
        }
    }

}
