<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {

            // Admin login route
            Route::prefix('api/admin')
                ->as('api.admin.')
                ->group(base_path('routes/admin/login.php'));

            // Dashboard API
            // Route::prefix('api/dashboard')
            //     ->as('api.dashboard.')
            //   //   ->middleware(['auth:admin-api', 'role:admin|superadmin'])
            //     ->group(base_path('routes/dashboard/api.php'));
            
            // User API
            Route::prefix('api')
                ->as('api.')
                ->group(base_path('routes/api.php'));

            // Dashboard Web
            Route::prefix('dashboard')
                ->as('dashboard.')
                // ->middleware(['auth:admin', 'role:admin|superadmin'])
                ->group(base_path('routes/dashboard/web.php'));

            // Dashboard API
            Route::prefix('api/dashboard')
                ->as('api.dashboard.')
                 ->middleware(['auth:admin-api', 'role:admin|superadmin'])
                ->group(base_path('routes/dashboard/api.php'));
            
            
            // Future additions

        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\SetAuthUser::class,
        ]);
        $middleware->validateCsrfTokens(
            except: ['api/*']
        );
        // This will apply middleware to all API routes
        // $middleware->api(append: [
        //     \App\Http\Middleware\CheckIfAdmin::class, // Admin role middleware
        // ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Load and run exception handler closures from a separate file
        // (require base_path('app/Exceptions/RegisterExceptionHandlers.php'))($exceptions);

    })
    ->create();
