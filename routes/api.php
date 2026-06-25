<?php

use App\Http\Controllers\Auth\User\LoginController;
use App\Http\Controllers\Auth\User\RegisterController;
use App\Http\Controllers\Auth\User\RecoveryPasswordController;

use App\Http\Controllers\User\Geocode\CountryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\BannerController;
use App\Http\Controllers\User\BoardController;
use App\Http\Controllers\User\ChatController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ShelfController;
use App\Http\Controllers\User\ContentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\FollowController;
use App\Http\Controllers\User\CommentController;
use App\Http\Controllers\User\ReplyController;
use App\Http\Controllers\User\LikeController;

/**
 * API Routes for User and Authentication.
 *
 * Defines endpoints for user registration, login, password recovery,
 * profile management, and other related resources.
 */

// Login route
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Registration routes grouped under /register prefix
Route::prefix('register')->group(function () {
    Route::post('/', [RegisterController::class, 'register'])->name('register');

    // Registration operations
    Route::post('/check-code', [RegisterController::class, 'checkCodeRegister'])->name('check-code-register');
    Route::get('/resend-code', [RegisterController::class, 'resendCodeRegister'])->name('resend-code-register');
});

// Password recovery routes grouped under /recovery-by-password prefix
Route::prefix('recovery-by-password')->group(function () {
    Route::post('forgot-password', [RecoveryPasswordController::class, 'forgotPassword'])->name('forgot-password');

    // Recovery operations
    Route::post('check-code', [RecoveryPasswordController::class, 'checkCode'])->name('check-code-pass');
    Route::get('resend-code', [RecoveryPasswordController::class, 'resendCode'])->name('resend-code-pass');
    Route::post('reset-password', [RecoveryPasswordController::class, 'resetPassword'])->name('reset-password');
});

// Language routes grouped under /lang prefix with alias 'lang'
Route::prefix('lang')->as('lang')->group(function () {
    Route::get('switch/{lang}', [LanguageController::class, 'switchLang'])->name('switch');
    Route::get('all', [LanguageController::class, 'getAllLangs'])->name('all');
    Route::get('default', [LanguageController::class, 'defaultLang'])->name('default');
});

// Routes protected by 'auth:api' and 'role:user' middleware
Route::middleware(['auth:api', 'role:user'])->group(function () {

    // Profile related routes grouped under /profile prefix with alias 'profile.'
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('update-password', [ProfileController::class, 'updatePassword'])->name('update-password');

        // File upload routes under /profile/file prefix
        Route::prefix('/file')->group(function () {
            Route::post('/', [ProfileController::class, 'uploadFile'])->name('upload-file');
            Route::delete('/', [ProfileController::class, 'deleteFile'])->name('delete-file');
        });

        // Multiple files upload routes under /profile/files prefix
        Route::prefix('/files')->group(function () {
            Route::post('/', [ProfileController::class, 'uploadFiles'])->name('upload-files');
            Route::delete('/', [ProfileController::class, 'deleteFiles'])->name('delete-files');
        });
    });

    
// Banners resource with only index method
Route::resource('banners', BannerController::class)->only(['index']);

// Boards resource with only index method
Route::resource('boards', BoardController::class)->only(['index']);

// Category resource with only index method
Route::resource('categories', CategoryController::class)->only(['index']);
Route::get('contents/{category}/category', [CategoryController::class, 'contentsCategory']);


Route::customResource('contents', ContentController::class);
Route::customResourceFiles('contents', ContentController::class);
    
Route::get('contents/me', [ContentController::class, 'myContents']);
Route::get('contents/{contentId}/related-contents', [ContentController::class, 'relatedContents']);
Route::get('contents/{contentId}/next-contents', [ContentController::class, 'nextContents']);
Route::get('contents/{contentId}/editions-contents', [ContentController::class, 'editionsContents']);
Route::get('contents/{contentId}/featured-contents', [ContentController::class, 'featuredContents']);
Route::get('contents/{contentId}/latest-contents', [ContentController::class, 'latestContents']);
Route::get('contents/popular', [ContentController::class, 'popularContents']);
Route::post('contents/my-saved', [ContentController::class, 'addToMySavedconent']);
Route::post('contents/my-reads', [ContentController::class, 'addToMySavedconent']);

//Route::resource('users', AuthorController::class)->only(['index', 'show']);
// Route::get('users/popular', [AuthorController::class, 'popularAuthors']);

Route::get('followers/{user}', [FollowController::class, 'followersUser']);
Route::post('followers/{user}', [FollowController::class, 'addFollowAuthor']);
Route::delete('followers/{follow}', [FollowController::class, 'deleteFollowAuthor']);


Route::customResource('comments', CommentController::class, [
    'only' => ['index','update'],              // for defaults
]);

Route::post('comments/{content}', [CommentController::class, 'addCommentContent']);
Route::customResourceFiles('comments', CommentController::class);

Route::customResource('replies', ReplyController::class, [
    'only' => ['update'],              // for defaults
]);
Route::put('replies/{reply}', [ReplyController::class, 'updateReplyComment']);


Route::post('likes/{content}', [LikeController::class, 'addLikeContent']);
Route::post('likes/{comment}', [LikeController::class, 'addLikeComment']);
Route::post('likes/{reply}', [LikeController::class, 'addLikeReply']);


Route::customResource('reviews', ReviewController::class, [
    'only' => ['update'],              // for defaults
]);

Route::post('reviews/{content}', [ReviewController::class, 'addReviewContent']);
// Route::post('reviews/{user}', [ReviewController::class, 'addReviewAuthor']);

Route::post('favorites/{content}', [favoriteController::class, 'addfavoriteContent']);



    Route::resource('shelves', ShelfController::class);
    Route::customResource('shelves', ShelfController::class);

    // chats resource routes with both resource and customResource methods
    Route::resource('chats', ChatController::class);
    Route::customResource('chats', ChatController::class);
    Route::customResourceFiles('chats', ChatController::class);

     Route::resource('countries', CountryController::class)->only(['index']);


     // orders resource routes with both resource and customResource methods
    Route::resource('orders', OrderController::class);
    Route::customResource('orders', OrderController::class);

     // Route::get('/', [NotificationController::class, 'index']);
    // Logout route
    Route::delete('/logout', [LoginController::class, 'destroy']);

});
