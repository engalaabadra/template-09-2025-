<?php

namespace App\Routing;

use Illuminate\Routing\Router;

/**
 * Class PendingCustomResourceRegistration
 *
 * Handles the registration of custom resource routes with optional filtering
 * using `only` and `except`. Routes are registered automatically on destruction
 * if not explicitly registered.
 */
class PendingCustomResourceRegistration
{
    /**
     * The Laravel router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected Router $router;

    /**
     * The list of routes to register.
     *
     * @var array<string, array>
     */
    protected array $routes;

    /**
     * Flag to prevent multiple registrations.
     *
     * @var bool
     */
    protected bool $registered = false;

    /**
     * Create a new PendingCustomResourceRegistration instance.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  array  $routes
     */
    public function __construct(Router $router, array $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
    }

    /**
     * Remove the given methods from the list of routes.
     *
     * @param  array<int, string>  $methods
     * @return $this
     */
    public function except(array $methods): self
    {
        foreach ($methods as $method) {
            unset($this->routes[$method]);
        }

        return $this;
    }

    /**
     * Keep only the given methods in the list of routes.
     *
     * @param  array<int, string>  $methods
     * @return $this
     */
    public function only(array $methods): self
    {
        $this->routes = array_intersect_key($this->routes, array_flip($methods));

        return $this;
    }

    /**
     * Register the defined routes using the router.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->routes as $route) {
            $this->router
                ->addRoute(strtoupper($route['method']), $route['uri'], $route['action'])
                ->name($route['name']);
        }

        $this->registered = true;
    }

    /**
     * Automatically register routes when the object is destroyed
     * (if not already registered).
     */
    public function __destruct()
    {
        $this->register();
    }
}
