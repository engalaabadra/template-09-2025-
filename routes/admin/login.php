<?php

use App\Http\Controllers\Auth\Admin\LoginController;

// Login route
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Logout route
Route::delete('/logout', [LoginController::class, 'destroy']);
