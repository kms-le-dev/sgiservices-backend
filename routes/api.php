<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// Public auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
// Reset password (public - requires matching user data)
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Public endpoints
Route::get('/services', [ServiceController::class, 'index']);
// List all media
Route::get('/media', [MediaController::class, 'index']);
// Get media by category
Route::get('/media/{category}', [MediaController::class, 'getByCategory']);
Route::get('/blogs', [BlogController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);

// Protected routes (require sanctum)
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Admin-only actions guarded in controllers
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    // allow POST update for admin UI compatibility
    Route::post('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    Route::post('/media', [MediaController::class, 'store']);
    Route::post('/media/{id}', [MediaController::class, 'update']);

    Route::post('/blogs', [BlogController::class, 'store']);
    Route::put('/blogs/{id}', [BlogController::class, 'update']);
    Route::post('/blogs/{id}', [BlogController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
});
