<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Show login form
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

    // Handle login
    Route::post('/login', [LoginController::class, 'login']);

    // Route::get('login', Login::class)->name('login');
    Route::get('register', [LoginController::class, 'showRegisterForm'])->name('register');
});

Route::middleware('auth')->group(function () {

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

});
