<?php

use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Student\PlaygroundController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Auth\PlaygroundLoginController;
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

    // Kelas
    Route::name('classes.')->group(function () {
        Route::get('/classes', [ClassesController::class, 'index'])->name('index');
        Route::post('/classes', [ClassesController::class, 'store'])->name('store');
        Route::put('/classes/{class}', [ClassesController::class, 'update'])->name('update');
        Route::delete('/classes/{class}', [ClassesController::class, 'destroy'])->name('destroy');
    });

    // Template Desain Modul
    Route::name('templates.')->group(function () {
        // web.php
        Route::get('/templates', [TemplatesController::class, 'index'])->name('index');
        Route::post('/templates', [TemplatesController::class, 'store'])->name('store');
        Route::put('/templates/{templates}', [TemplatesController::class, 'update'])->name('update');
        Route::delete('/templates/{templates}', [TemplatesController::class, 'destroy'])->name('destroy');
        Route::get('/templates/{templates}', [TemplatesController::class, 'show'])->name('show');

        // Hapus backgrounds dan mascots individual jika ada
        Route::delete('/backgrounds/{background}', [TemplatesController::class, 'destroyBackground'])->name('backgrounds.destroy');
        Route::delete('/mascots/{mascot}', [TemplatesController::class, 'destroyMascot'])->name('mascots.destroy');
    });

    // Pengguna
    Route::name('users.')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('index');
        Route::post('/users', [UsersController::class, 'store'])->name('store');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('update');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('destroy');
    });
    // Modul
    Route::name('modules.')->group(function () {
        Route::get('/modules', [ModulesController::class, 'index'])->name('index');

        // ⚠️ CRITICAL: CREATE routes MUST be BEFORE show routes!
        Route::get('/modules/{id}/mission/create', [ModulesController::class, 'createMission'])->name('mission');
        Route::get('/modules/{moduleId}/mission/{missionId}/material/create', [ModulesController::class, 'createMaterial'])->name('material');
        Route::get('/modules/{moduleId}/mission/{missionId}/quiz/create', [ModulesController::class, 'createQuiz'])->name('quiz');

        // Show routes go AFTER create routes
        Route::get('/modules/{id}', [ModulesController::class, 'show'])->name('show');
        Route::get('/modules/{moduleId}/mission/{missionId}', [ModulesController::class, 'showMission'])->name('mission.show');
    });
});

require __DIR__.'/auth.php';
