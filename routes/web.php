<?php

use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;

// Session (cookie) based authentication for the SPA, protected by CSRF.
Route::post('/auth/login', [SessionController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('auth.login');
Route::post('/auth/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('auth.logout');

// Every other GET request is handled by the Vue router.
Route::view('/{any?}', 'app')
    ->where('any', '^(?!api|sanctum|up).*$')
    ->name('spa');
