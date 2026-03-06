<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PlaygroundLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// routes/auth.php
Route::middleware('guest')->group(function () {
    Route::get('login-geniAdmin', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login-geniAdmin', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::prefix('player')->group(function () {
    Route::get('/playground/login', [PlaygroundLoginController::class, 'login'])
        ->name('playground.login');
    Route::post('/playground/authenticate', [PlaygroundLoginController::class, 'authenticate'])
        ->name('playground.authenticate');

    // Halaman quiz (setelah start berhasil)
    Route::get('/playground/quiz', [PlaygroundLoginController::class, 'quiz'])
        ->name('playground.quiz');
});
