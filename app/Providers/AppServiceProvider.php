<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * Class AppServiceProvider
 *
 * Bootstraps core services for the application.
 * - Registers custom route macros and resource registrars.
 * - Binds global services and observers for models.
 * - Configures frontend tooling (Vite).
 *
 * Purpose:
 * Centralizes system-level setup to ensure consistent global behavior.
 *
 * Example:
 * Route::customResource('users', UserController::class);
 */

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Reserved for future service bindings
        // Example: $this->app->bind(SomeInterface::class, SomeImplementation::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Replace Laravel's default ResourceRegistrar with a custom one
        $this->app->bind(
            'Illuminate\Routing\ResourceRegistrar', // default binding
            \App\Routing\ResourceRegistrarCustom::class // custom implementation
        );

        // Define a new router macro: customResource
        // This macro allows registering resource routes with extra options.
        app('router')->macro('customResource', function ($name, $controller, $options = []) {
            $registrar = new \App\Routing\ResourceRegistrarCustom($this); // instantiate custom registrar
            return $registrar->registerCustomResource($name, $controller, $options); // register routes
        });

        // Define a router macro for file-based resource controllers
        // Useful for handling upload/download controllers in a clean way.
        app('router')->macro('customResourceFiles', function ($name, $controller, $options = []) {
            $registrar = new \App\Routing\ResourceRegistrarFiles(app('router')); // custom file registrar
            return $registrar->registerCustomResource($name, $controller, $options); // register routes
        });

        // Vite optimization: prefetch with limited concurrency (performance tweak)
        Vite::prefetch(concurrency: 3);

        // Attach observers to models to enforce protected actions
        \App\Models\User::observe(\App\Observers\UserObserver::class); // watch User events
        \App\Models\Role::observe(\App\Observers\RoleObserver::class); // watch Role events
        \App\Models\File::observe(\App\Observers\FileObserver::class); // watch File events


        // Warm up main roles/users cache once if not set
        if (!Cache::has('main_roles_ids') || !Cache::has('main_roles_names') || !Cache::has('main_users_ids')) {
            \App\Models\Role::getMainRolesIds();
            \App\Models\Role::getMainRolesNames();
            \App\Models\User::getMainUsersIds();
            
        }

    }
}
