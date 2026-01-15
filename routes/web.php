<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Laravel backend is running']);
});

// Pages légales et informations publiques
Route::view('/privacy', 'pages.privacy');
Route::view('/terms', 'pages.terms');
Route::view('/cookies', 'pages.cookies');
Route::view('/about', 'pages.about');
