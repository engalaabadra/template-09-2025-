<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ===========================================
 *  MODEL HELPERS
 * ===========================================
 */

if (!function_exists('isSoftDeletes')) {
    /**
     * Check if a model uses SoftDeletes trait.
     *
     * @param object $model
     * @return bool
     */
    function isSoftDeletes($model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}

if (!function_exists('modelName')) {
    /**
     * Get plural lowercase model name.
     *
     * @param object $model
     * @return string
     */
    function modelName($model)
    {
        return strtolower(class_basename($model)) . 's';
    }
}

if (!function_exists('modelNameSingular')) {
    /**
     * Get plural lowercase model name.
     *
     * @param object $model
     * @return string
     */
    function modelNameSingular($model)
    {
        return class_basename($model);
    }
}

if (!function_exists('getModelClass')) {
    /**
     * Get fully qualified model class name.
     *
     * @param string $modelName
     * @return string|null
     */
    function getModelClass($modelName)
    {
        $modelClass = 'App\\Models\\' . ucfirst($modelName);
        return class_exists($modelClass) ? $modelClass : null;
    }
}

if (!function_exists('getTableFromRouteModel')) {
    /**
     * Get the table name of the model bound to the current route dynamically.
     *
     * @param string|null $paramName Optional route parameter name
     * @return string|null
     */
    function getTableFromRouteModel(?string $paramName = null)
    {
        $route = request()->route();
        if (!$route) return null;

        // إذا أعطينا اسم الباراميتر، استخدمه، وإلا خذ أول موديل موجود
        $parameters = $route->parameters();

        if ($paramName && isset($parameters[$paramName])) {
            $model = $parameters[$paramName];
        } else {
            // خذ أول قيمة إذا كانت موديل
            $model = collect($parameters)->first(fn($p) => is_object($p) && method_exists($p, 'getTable'));
        }

        if ($model && method_exists($model, 'getTable')) {
            return $model->getTable();
        }

        return null;
    }
}

if (!function_exists('refreshIfMissing')) {
    /**
     * Refresh the model if a key is missing from request data.
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @return void
     */
    function refreshIfMissing(array $data, Model $model, string $key = 'is_active'): void
    {
        if (!array_key_exists($key, $data)) {
            $model->refresh();
        }
    }
}
