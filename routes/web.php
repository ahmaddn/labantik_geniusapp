<?php

use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MissionController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\PlaygroundLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DragDropController;
use App\Http\Controllers\Student\PlaygroundController;
use App\Http\Controllers\Student\PretestController;
use App\Http\Controllers\Student\PosttestController;
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

// Preview route untuk Pretest (tanpa auth) - hanya untuk pengembangan UI
Route::get('/playground/pretest/preview', [PretestController::class, 'preview'])
    ->name('playground.pretest.preview');


// ── Route asli dengan middleware ─────────────────────────────
Route::middleware(['auth', 'player'])->prefix('student')->name('student.')->group(function () {
    Route::get('/missions/{mission}/dragdrop', [DragDropController::class, 'show'])
        ->name('missions.dragdrop');
});

// Playground Routes (Public - No Auth Required)
Route::prefix('player')->name('playground.')->group(function () {
    Route::get('/playground', [PlaygroundController::class, 'index'])->name('index');
    Route::get('/playground/quiz', [PlaygroundController::class, 'quiz'])->name('quiz');

    // ── Pretest ──────────────────────────────────────────────────
    Route::get('/modules/{module}/pretest',  [PretestController::class, 'show'])  ->name('pretest.show');
    Route::post('/pretest/submit',           [PretestController::class, 'submit'])->name('pretest.submit');

    // ── Posttest ─────────────────────────────────────────────────
    Route::get('/modules/{module}/posttest', [PosttestController::class, 'show'])  ->name('posttest.show');
    Route::post('/posttest/submit',          [PosttestController::class, 'submit'])->name('posttest.submit');

    // Student mission routes (session-based authentication)
    Route::prefix('missions')->name('missions.')->group(function () {
        Route::get('/module/{module}', [\App\Http\Controllers\Student\MissionController::class, 'missionsList'])->name('index');
        Route::get('/{mission}', [\App\Http\Controllers\Student\MissionController::class, 'showMission'])->name('show');
        Route::post('/{mission}/submit', [\App\Http\Controllers\Student\MissionController::class, 'submitMissionAnswers'])->name('submit');
        Route::get('/{mission}/result', [\App\Http\Controllers\Student\MissionController::class, 'showResult'])->name('result');
    });
});

// Playground Login Routes
Route::name('playground.')->group(function () {
    Route::get('/playground-login', [PlaygroundLoginController::class, 'login'])->name('login');
    Route::post('/playground-auth', [PlaygroundLoginController::class, 'authenticate'])->name('authenticate');
    Route::post('/playground-start', [PlaygroundLoginController::class, 'start'])->name('start');
    Route::post('/playground-logout', [PlaygroundLoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin,guru'])->prefix('geniAdmin')->name('admin.')->group(function () {

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
        // Module Routes
        Route::get('/modules', [ModulesController::class, 'index'])->name('index');
        Route::post('/modules', [ModulesController::class, 'store'])->name('store');
        Route::get('/modules/{modules}', [ModulesController::class, 'show'])->name('show');
        Route::put('/modules/{modules}', [ModulesController::class, 'update'])->name('update');
        Route::delete('/modules/{modules}', [ModulesController::class, 'destroy'])->name('destroy');
        Route::patch('/modules/{modules}/toggle-active', [ModulesController::class, 'toggleActive'])->name('toggle-active');
        // Module-level Quiz Routes (for quizzes without a mission_id)
        Route::prefix('{modules}/quizzes')->name('quizzes.')->group(function () {
            Route::get('/create/{category}', [QuizController::class, 'createModule'])->name('create');
            Route::post('/', [QuizController::class, 'storeModule'])->name('store');
            Route::get('/{quizzes}', [QuizController::class, 'showModule'])->name('show');
            // Note: creation of quizzes for pretest/posttest is available here
        });

        // Mission Routes (nested under modules)
        Route::prefix('{modules}/missions')->name('missions.')->group(function () {
            Route::post('/', [MissionController::class, 'store'])->name('store');
            Route::get('/{missions}', [MissionController::class, 'show'])->name('show');
            Route::put('/{missions}', [MissionController::class, 'update'])->name('update');
            Route::delete('/{missions}', [MissionController::class, 'destroy'])->name('destroy');

            // Material Routes (nested under missions)
            Route::prefix('{missions}/materials')->name('materials.')->group(function () {
                Route::get('/create', [MaterialController::class, 'create'])->name('create');
                Route::post('/', [MaterialController::class, 'store'])->name('store');
                Route::get('/{materials}', [MaterialController::class, 'show'])->name('show');
                Route::get('/{materials}/edit', [MaterialController::class, 'edit'])->name('edit');
                Route::put('/{materials}', [MaterialController::class, 'update'])->name('update');
                Route::delete('/{materials}', [MaterialController::class, 'destroy'])->name('destroy');
            });

            // Quiz Routes (nested under missions)
            Route::prefix('{missions}/quizzes')->name('quizzes.')->group(function () {
                Route::get('/create', [QuizController::class, 'create'])->name('create');
                Route::post('/', [QuizController::class, 'store'])->name('store');
                Route::get('/{quizzes}', [QuizController::class, 'show'])->name('show');
                Route::get('/{quizzes}/edit', [QuizController::class, 'edit'])->name('edit');
                Route::put('/{quizzes}', [QuizController::class, 'update'])->name('update');
                Route::delete('/{quizzes}', [QuizController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

require __DIR__ . '/auth.php';
