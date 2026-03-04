<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Backgrounds;
use App\Models\Learning_modules;
use App\Models\Mascots;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionController extends Controller
{
    /**
     * Display the mission index page.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $learningModules = Learning_modules::with([
            'template',
            'missions' => function ($q) use ($user) {
                $q->with([
                    'quizzes.quizAttempts' => function ($qa) use ($user) {
                        $qa->where('user_id', $user?->id);
                    },
                    'materials',
                ])->orderBy('order_number');
            },
        ])
        ->orderBy('quota')
        ->get()
        ->map(function ($module) use ($user) {
            $missions = $module->missions->map(function ($mission) use ($user) {
                $isCompleted = false;

                if ($user) {
                    $totalQuizzes = $mission->quizzes->count();
                    if ($totalQuizzes > 0) {
                        $completedQuizzes = $mission->quizzes->filter(function ($quiz) use ($user) {
                            return $quiz->quizAttempts->where('user_id', $user->id)->count() > 0;
                        })->count();
                        $isCompleted = $completedQuizzes >= $totalQuizzes;
                    }
                }

                return [
                    'id'              => $mission->id,
                    'title'           => $mission->title,
                    'order_number'    => $mission->order_number,
                    'is_completed'    => $isCompleted,
                    'materials_count' => $mission->materials->count(),
                    'quizzes_count'   => $mission->quizzes->count(),
                ];
            });

            return [
                'id'              => $module->id,
                'title'           => $module->title,
                'description'     => $module->description,
                'quota'           => $module->quota,
                'level'           => $module->level,
                'template'        => $module->template ? [
                'background'      => $module->template->background,
                ] : null,
                'missions'        => $missions,
                'missions_count'  => $missions->count(),
                'completed_count' => $missions->where('is_completed', true)->count(),
            ];
        });

        $mascot     = Mascots::first();
        $background = Backgrounds::first();

        return Inertia::render('Playground/MissionPage', [
            'learningModules' => $learningModules,
            'mascot'          => $mascot ? [
                'id'    => $mascot->id,
                'name'  => $mascot->name ?? 'Teman Belajar',
                'image' => $mascot->image,
            ] : null,
            'background'      => $background ? [
                'id'    => $background->id,
                'name'  => $background->name,
                'image' => $background->image,
            ] : null,
        ]);
    }

    /**
     * Display a specific mission/learning module.
     */
    public function show(Request $request, Learning_modules $learningModule)
    {
        $user = $request->user();

        $learningModule->load([
            'template',
            'missions.materials',
            'missions.quizzes.quizAttempts',
        ]);

        return Inertia::render('Playground/MissionShow', [
            'module' => $learningModule,
            'auth'   => ['user' => $user],
        ]);
    }
}
