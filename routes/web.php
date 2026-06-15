<?php

use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MissionController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\TemplatesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\PlaygroundLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DragDropController;
use App\Http\Controllers\Student\PlaygroundController;
use App\Http\Controllers\Student\PretestController;
use App\Http\Controllers\Student\PosttestController;
use App\Http\Controllers\Student\MissionController as StudentMissionController;
use App\Http\Controllers\Admin\SimulationConfigController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Preview route untuk Pretest (tanpa auth) - hanya untuk pengembangan UI
Route::get('/playground/pretest/preview', [PretestController::class, 'preview'])
    ->name('playground.pretest.preview');
Route::get('/posttest/preview', [PosttestController::class, 'preview'])
    ->name('posttest.preview');
Route::post('/posttest/submit', [PosttestController::class, 'submit'])
    ->name('posttest.submit');



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
    Route::get('/modules/{module}/pretest',  [PretestController::class, 'show'])->name('pretest.show');
    Route::post('/pretest/submit',           [PretestController::class, 'submit'])->name('pretest.submit');

    // ── Posttest ─────────────────────────────────────────────────
    Route::get('/modules/{module}/posttest', [PosttestController::class, 'show'])->name('posttest.show');
    Route::post('/posttest/submit',          [PosttestController::class, 'submit'])->name('posttest.submit');
    Route::get('/modules/{module}/posttest/result', [PosttestController::class, 'overallResult'])->name('posttest.result');

    // Student mission routes (session-based authentication)
    Route::prefix('missions')->name('missions.')->group(function () {
        Route::get('/module/{module}', [StudentMissionController::class, 'missionsList'])->name('index');
        Route::get('/{mission}', [StudentMissionController::class, 'showMission'])->name('show');
        Route::post('/{mission}/submit', [StudentMissionController::class, 'submitMissionAnswers'])->name('submit');
        Route::get('/{mission}/result', [StudentMissionController::class, 'showResult'])->name('result');
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    // Templates for imports
    Route::get('/quizzes/template', [QuizController::class, 'downloadTemplate'])->name('quizzes.template');
    Route::get('/materials/template', [MaterialController::class, 'downloadTemplate'])->name('materials.template');


    // Kelas
    Route::name('classes.')->group(function () {
        Route::get('/classes', [ClassesController::class, 'index'])->name('index');
        Route::post('/classes', [ClassesController::class, 'store'])->name('store');
        Route::get('/classes/{class}/details', [ClassesController::class, 'getDetails'])->name('details');
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

    // Reports & History
    Route::name('reports.')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('index');
        Route::get('/reports/modules/{modules}/history', [ReportsController::class, 'moduleHistory'])->name('history');
        Route::get('/reports/modules/{modules}/export', [ReportsController::class, 'exportModuleXlsx'])->name('export');
        Route::get('/reports/modules/{modules}/students/{student}', [ReportsController::class, 'studentReport'])->name('student');
        Route::post('/reports/modules/{modules}/students/{student}/update-score', [ReportsController::class, 'updateScore'])->name('update_score');
    });

    // Pengaturan
    Route::name('settings.')->prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::delete('/logo', [SettingsController::class, 'deleteLogo'])->name('logo.delete');
        Route::delete('/mascot', [SettingsController::class, 'deleteMascot'])->name('mascot.delete');
        Route::post('/bgm', [SettingsController::class, 'uploadBgm'])->name('bgm.upload');
        Route::delete('/bgm/{bgm}', [SettingsController::class, 'deleteBgm'])->name('bgm.delete');
        Route::post('/bgm/{bgm}/active', [SettingsController::class, 'setActiveBgm'])->name('bgm.active');
        Route::post('/bgm/clear', [SettingsController::class, 'clearActiveBgm'])->name('bgm.clear');
    });

    // Pengguna
    Route::name('users.')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('index');
        Route::post('/users', [UsersController::class, 'store'])->name('store');
        Route::get('/users/template', [UsersController::class, 'downloadTemplate'])->name('template');
        Route::post('/users/import', [UsersController::class, 'importExcel'])->name('import');
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
        Route::prefix('modules/{modules}/quizzes')->name('quizzes.')->group(function () {
            Route::get('/create/{category}', [QuizController::class, 'createModule'])->name('create');
            Route::post('/', [QuizController::class, 'storeModule'])->name('store');
            // Import module-level quizzes (CSV)
            Route::post('/import', [QuizController::class, 'importModule'])->name('import');
            Route::get('/{quizzes}', [QuizController::class, 'showModule'])->name('show');
            Route::get('/{quizzes}/edit', [QuizController::class, 'editModule'])->name('edit');
            Route::put('/{quizzes}', [QuizController::class, 'updateModule'])->name('update');
            Route::delete('/{quizzes}', [QuizController::class, 'destroyModule'])->name('destroy');
        });

        // Mission Routes (nested under modules)
        Route::prefix('modules/{modules}/missions')->name('missions.')->group(function () {
            Route::post('/', [MissionController::class, 'store'])->name('store');
            Route::get('/{missions}', [MissionController::class, 'show'])->name('show');
            Route::put('/{missions}', [MissionController::class, 'update'])->name('update');
            Route::delete('/{missions}', [MissionController::class, 'destroy'])->name('destroy');
            Route::post('/{missions}/reorder', [MissionController::class, 'reorderSteps'])->name('reorder');

            // Material Routes (nested under missions)
            Route::prefix('{missions}/materials')->name('materials.')->group(function () {
                Route::get('/create', [MaterialController::class, 'create'])->name('create');
                Route::post('/', [MaterialController::class, 'store'])->name('store');
                // Import materials via CSV
                Route::post('/import', [MaterialController::class, 'import'])->name('import');
                Route::get('/{materials}', [MaterialController::class, 'show'])->name('show');
                Route::get('/{materials}/edit', [MaterialController::class, 'edit'])->name('edit');
                Route::put('/{materials}', [MaterialController::class, 'update'])->name('update');
                Route::delete('/{materials}', [MaterialController::class, 'destroy'])->name('destroy');
            });

            // Quiz Routes (nested under missions)
            Route::prefix('{missions}/quizzes')->name('quizzes.')->group(function () {
                Route::get('/create', [QuizController::class, 'create'])->name('create');
                Route::post('/', [QuizController::class, 'store'])->name('store');
                // Import mission-level quizzes (CSV)
                Route::post('/import', [QuizController::class, 'importMission'])->name('import');
                Route::get('/{quizzes}', [QuizController::class, 'show'])->name('show');
                Route::get('/{quizzes}/edit', [QuizController::class, 'edit'])->name('edit');
                Route::put('/{quizzes}', [QuizController::class, 'update'])->name('update');
                Route::delete('/{quizzes}', [QuizController::class, 'destroy'])->name('destroy');
            });

            // Simulation Config Routes (nested under missions)
            Route::prefix('{missions}/simulation')->name('simulation.')->group(function () {
                Route::get('/', [SimulationConfigController::class, 'edit'])->name('edit');
                Route::put('/', [SimulationConfigController::class, 'update'])->name('update');
                Route::delete('/{id}', [SimulationConfigController::class, 'destroy'])->name('destroy');
            });

            // Reflection Routes (nested under missions)
            Route::prefix('{missions}/reflections')->name('reflections.')->group(function () {
                Route::get('/create', [App\Http\Controllers\Admin\ReflectionController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\ReflectionController::class, 'store'])->name('store');
                Route::get('/{reflections}', [App\Http\Controllers\Admin\ReflectionController::class, 'show'])->name('show');
                Route::get('/{reflections}/edit', [App\Http\Controllers\Admin\ReflectionController::class, 'edit'])->name('edit');
                Route::put('/{reflections}', [App\Http\Controllers\Admin\ReflectionController::class, 'update'])->name('update');
                Route::delete('/{reflections}', [App\Http\Controllers\Admin\ReflectionController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

require __DIR__ . '/auth.php';
