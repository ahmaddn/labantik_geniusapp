<?php

use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PlaygroundController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/playground', [PlaygroundController::class, 'index'])
    ->name('playground.index');

// POST saat klik "Mulai Petualangan"
Route::post('/playground/start', [PlaygroundController::class, 'start'])
    ->name('playground.start');

// Halaman quiz (setelah start berhasil)
Route::get('/playground/quiz', [PlaygroundController::class, 'quiz'])
    ->name('playground.quiz');

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
    });

    // Template Desain Modul
    Route::name('templates.')->group(function () {
        Route::get('/templates', [TemplatesController::class, 'index'])->name('index');
    });

    // Pengguna
    Route::name('users.')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('index');
    });
    // Modul
    Route::name('modules.')->group(function () {
        // Module List (Index)
        Route::get('/modules', [ModulesController::class, 'index'])->name('index');

        // Show Module Detail (with Missions list)
        Route::get('/modules/{id}', [ModulesController::class, 'show'])->name('show');

        // Show Mission Detail (with Materials & Quizzes list)
        Route::get('/modules/{moduleId}/mission/{missionId}', [ModulesController::class, 'showMission'])->name('mission.show');

        // Create Mission Wizard
        Route::get('/modules/{id}/mission/create', [ModulesController::class, 'createMission'])->name('mission');

        // Create Material Wizard
        Route::get('/modules/{moduleId}/mission/{missionId}/material/create', [ModulesController::class, 'createMaterial'])->name('material');

        // Create Quiz Wizard
        Route::get('/modules/{moduleId}/mission/{missionId}/quiz/create', [ModulesController::class, 'createQuiz'])->name('quiz');
    });
});


require __DIR__ . '/auth.php';
