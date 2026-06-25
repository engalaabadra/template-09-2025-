<?php

namespace App\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;
use App\Routing\PendingCustomResourceRegistration;

class ResourceRegistrarCustom extends OriginalRegistrar
{
    // Default Laravel resource actions
    public $resourceDefaults = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
    ];

    /**
     * Register resource with custom routes.
     */
    public function registerCustomResource(string $name, string $controller, array $options = []): PendingCustomResourceRegistration
    {
        $defaultOptions = $options;

        $customOnly   = $options['custom_only']   ?? null;
        $customExcept = $options['custom_except'] ?? null;
        unset($defaultOptions['custom_only'], $defaultOptions['custom_except']);

        // Register default resource routes first
        parent::register($name, $controller, $defaultOptions);

        // Custom routes config with 'actions' prefix for safety
        $customRoutesConfig = [
            'changeActivateMany' => ['method' => 'patch',  'path' => '/actions/activate'],
            'restoreMany'        => ['method' => 'patch',  'path' => '/actions/restore'],
            'restore'            => ['method' => 'patch',  'path' => '/{id}/actions/restore'],
            'changeActivate'     => ['method' => 'patch',  'path' => '/{id}/actions/activate'],
            'destroyMany'        => ['method' => 'delete', 'path' => '/actions/destroy'],
            'forceDelete'        => ['method' => 'delete', 'path' => '/{id}/actions/force'],
            'forceDeleteMany'    => ['method' => 'delete', 'path' => '/actions/force'],
        ];

        $customMethods = array_keys($customRoutesConfig);
        if ($customOnly) {
            $customMethods = array_intersect($customMethods, $customOnly);
        } elseif ($customExcept) {
            $customMethods = array_diff($customMethods, $customExcept);
        }

        $routes = [];
        foreach ($customMethods as $key) {
            $data = $customRoutesConfig[$key];
            $routes[$key] = [
                'method' => $data['method'],
                'uri'    => $this->getResourceUri($name) . $data['path'],
                'action' => $this->getResourceAction($name, $controller, $key, $options),
                'name'   => "{$name}.{$key}",
            ];
        }

        return new PendingCustomResourceRegistration($this->router, $routes);
    }

    /** ===================== Individual AddResource Methods ===================== */

    public function addResourceRestoreMany($name, $controller, $options)
    {
        return $this->router->patch($this->getResourceUri($name) . '/actions/restore', 
            $this->getResourceAction($name, $controller, 'restoreMany', $options));
    }

    public function addResourceRestore($name, $controller, $options)
    {
        return $this->router->patch($this->getResourceUri($name) . '/{id}/actions/restore', 
            $this->getResourceAction($name, $controller, 'restore', $options));
    }

    public function addResourceChangeActivate($name, $controller, $options)
    {
        return $this->router->patch($this->getResourceUri($name) . '/{id}/actions/activate', 
            $this->getResourceAction($name, $controller, 'changeActivate', $options));
    }

    public function addResourceChangeActivateMany($name, $controller, $options)
    {
        return $this->router->patch($this->getResourceUri($name) . '/actions/activate', 
            $this->getResourceAction($name, $controller, 'changeActivateMany', $options));
    }

    public function addResourceDestroyMany($name, $controller, $options)
    {
        return $this->router->delete($this->getResourceUri($name) . '/actions/destroy', 
            $this->getResourceAction($name, $controller, 'destroyMany', $options));
    }

    public function addResourceForceDelete($name, $controller, $options)
    {
        return $this->router->delete($this->getResourceUri($name) . '/{id}/actions/force', 
            $this->getResourceAction($name, $controller, 'forceDelete', $options));
    }

    public function addResourceForceDeleteMany($name, $controller, $options)
    {
        return $this->router->delete($this->getResourceUri($name) . '/actions/force', 
            $this->getResourceAction($name, $controller, 'forceDeleteMany', $options));
    }
}
