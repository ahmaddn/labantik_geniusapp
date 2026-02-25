<?php

use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::name('classes.')->group(function () {
        Route::get('/classes', [ClassesController::class, 'index'])->name('index');
    });
    Route::name('users.')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('index');
    });
});


require __DIR__ . '/auth.php';
