<?php

use Illuminate\Support\Facades\Route;

Route::get('/requires-password-confirmation', function () {
    return response()->json(['success' => true]);
})->middleware(['auth', 'password.confirm']);

Route::get('/requires-auth', function () {
    return response()->json(['success' => true]);
})->middleware('auth');

Route::get('/requires-nothing', function () {
    return response()->json(['success' => true]);
});

// Lightweight route that just sets CSRF token cookie
Route::get('/test/csrf-token', function () {
    // This will set the XSRF-TOKEN cookie without rendering anything
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
});