<?php

namespace App\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;
use App\Routing\PendingCustomResourceRegistration;

/**
 * Class ResourceRegistrarFiles
 *
 * This class extends Laravel's default ResourceRegistrar to register custom
 * resource routes for handling file operations such as uploading and deleting
 * single/multiple files. It adds additional methods and routes specific to file handling.
 */
class ResourceRegistrarFiles extends OriginalRegistrar
{
    /**
     * The default actions for a resourceful controller specific to file handling.
     *
     * @var array
     */
    public $resourceDefaults = ['uploadFile', 'uploadFiles', 'deleteFile', 'deleteFiles'];

    /**
     * Register custom file-related resource routes.
     *
     * @param  string  $name         The base name of the resource.
     * @param  string  $controller   The controller class handling the resource.
     * @param  array   $options      Optional route configuration.
     * @return \App\Routing\PendingCustomResourceRegistration
     */
    public function registerCustomResource($name, $controller, $options = [])
    {
        // Define route configuration for each file-related action
        $config = [
            'uploadFile'  => ['method' => 'post',   'path' => '/{id}/file'],
            'uploadFiles' => ['method' => 'post',   'path' => '/{id}/files'],
            'deleteFile'  => ['method' => 'delete', 'path' => '/{id}/file'],
            'deleteFiles' => ['method' => 'delete', 'path' => '/{id}/files'],
        ];

        $routes = [];

        // Generate route definitions for each action
        foreach ($config as $key => $data) {
            $routes[$key] = [
                'method' => $data['method'],
                'uri'    => $this->getResourceUri($name) . $data['path'],
                'action' => $this->getResourceAction($name, $controller, $key, $options),
                'name'   => "{$name}.{$key}",
            ];
        }

        // Return pending registration object for router
        return new PendingCustomResourceRegistration($this->router, $routes);
    }

    ////////////////////// File Routes //////////////////////

    /**
     * Add the uploadFile method for a resourceful route.
     *
     * @param  string  $name         The base name of the resource.
     * @param  string  $controller   The controller class.
     * @param  array   $options      Optional route configuration.
     * @return \Illuminate\Support\Facades\Route
     */
    public function addResourceUploadFile($name, $controller, $options)
    {
        $uri    = $this->getResourceUri($name) . '/{id}/file';
        $action = $this->getResourceAction($name, $controller, 'uploadFile', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the uploadFiles method for a resourceful route.
     *
     * @param  string  $name         The base name of the resource.
     * @param  string  $controller   The controller class.
     * @param  array   $options      Optional route configuration.
     * @return \Illuminate\Support\Facades\Route
     */
    public function addResourceUploadFiles($name, $controller, $options)
    {
        $uri    = $this->getResourceUri($name) . '/{id}/files';
        $action = $this->getResourceAction($name, $controller, 'uploadFiles', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the deleteFile method for a resourceful route.
     *
     * @param  string  $name         The base name of the resource.
     * @param  string  $controller   The controller class.
     * @param  array   $options      Optional route configuration.
     * @return \Illuminate\Support\Facades\Route
     */
    public function addResourceDeleteFile($name, $controller, $options)
    {
        $uri    = $this->getResourceUri($name) . '/{id}/file';
        $action = $this->getResourceAction($name, $controller, 'deleteFile', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Add the deleteFiles method for a resourceful route.
     *
     * @param  string  $name         The base name of the resource.
     * @param  string  $controller   The controller class.
     * @param  array   $options      Optional route configuration.
     * @return \Illuminate\Support\Facades\Route
     */
    public function addResourceDeleteFiles($name, $controller, $options)
    {
        $uri    = $this->getResourceUri($name) . '/{id}/files';
        $action = $this->getResourceAction($name, $controller, 'deleteFiles', $options);

        return $this->router->delete($uri, $action);
    }
}
