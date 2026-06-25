<?php

use App\Http\Controllers\Dashboard\Auth\UserController;
use App\Http\Controllers\Dashboard\Auth\RoleController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\BoardController;
use App\Http\Controllers\Dashboard\ChatController;
use App\Http\Controllers\Dashboard\ContactController;
use App\Http\Controllers\Dashboard\FavoriteController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\OrderController;
// use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Dashboard\Geocode\CountryController;


/**
 * Dashboard Module Routes
 *
 * Defines the routes for dashboard modules including authentication,
 * banners, boards, chats, contacts, geocodes, orders, and reviews.
 */

// case 1 :if i need only original route ( Route::resource ), case 2 : if i need original routes + custom routes ( Route:: customResource) ,
// case 3 : if i need only routes files (Route::customResourceFiles), case 4 : i need original routes + custom routes + files routes ( Route::customResourceWithFiles )
// case 5 : if i need original routes + files routes (Route::resource , Route::customResource)

// Users resource routes and custom resource routes for extended functionality
Route::resource('users', UserController::class);
Route::customResource('users', UserController::class);
Route::customResourceFiles('users', UserController::class);

// Additional user-related routes for roles and permissions

// Roles resource routes with custom resource routes
Route::resource('roles', RoleController::class);
Route::customResource('roles', RoleController::class);


// Banners resource routes with custom resource and file management

Route::resource('banners', BannerController::class);
Route::customResource('banners', BannerController::class);
Route::customResourceFiles('banners', BannerController::class)
    ->except(['uploadFiles', 'deleteFiles']);

// Route::customResourceWithFiles('banners', BannerController::class, [], [
//     'except' => ['restore', 'uploadFiles', 'deleteFiles']
// ]);

// Route::customResourceWithFiles('banners', BannerController::class, [], ['except' => ['restore']]);

// Boards resource routes with custom resource and file management
Route::resource('boards', BoardController::class);
Route::customResource('boards', BoardController::class);
Route::customResourceFiles('boards', BoardController::class);

// Chats resource routes with exception and custom resource routes
Route::resource('chats', ChatController::class)->except(['show']);
Route::customResource('chats', ChatController::class);
Route::customResourceFiles('chats', ChatController::class);

// Contacts resource routes with custom resource routes
Route::resource('contacts', ContactController::class);
Route::customResource('contacts', ContactController::class);

// Countries (Geocode) resource routes with custom resource routes
Route::resource('countries', CountryController::class);
Route::customResource('countries', CountryController::class);

// Notifications route (commented out)
// Route::get('/', [NotificationController::class, 'index']);

// Orders resource routes with exception on store and update, plus custom resource routes
Route::resource('orders', OrderController::class)->except(['store', 'update']);
Route::customResource('orders', OrderController::class);

// Reviews resource routes with exception on store and update, plus custom resource routes
Route::resource('reviews', ReviewController::class)->except(['store', 'update']);
Route::customResource('reviews', ReviewController::class);
