<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Quizzes;
use App\Models\Quiz_attempts;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /**
     * Helper: ambil semua quiz_id milik sebuah modul
     */
    private function getQuizIdsByModule(Learning_modules $module)
    {
        $directQuizIds = Quizzes::where('module_id', $module->id)->pluck('id');

        $missionQuizIds = Quizzes::whereHas(
            'mission',
            fn($q) =>
            $q->where('module_id', $module->id)
        )->pluck('id');

        return $directQuizIds->merge($missionQuizIds)->unique()->values();
    }

    /**
     * Dashboard index — statistik ringkas semua modul
     */
    public function index()
    {
        $totalStudents = User::where('role', 'siswa')->count();
        $totalModules  = Learning_modules::count();

        // Statistik per modul
        $modules = Learning_modules::select('id', 'name')->latest()->get();

        $modulesStats = $modules->map(function ($module) {
            $quizIds = $this->getQuizIdsByModule($module);

            $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)->get();

            // Berapa siswa unik yang sudah mengerjakan
            $studentsDone = $attempts->pluck('student_id')->unique()->count();

            // Rata-rata nilai keseluruhan
            $avgScore = $attempts->count() > 0
                ? (int) round($attempts->avg('score'))
                : null;

            // Jumlah attempt selesai (finished_at tidak null)
            $completedAttempts = $attempts->whereNotNull('finished_at')->count();

            return [
                'id'                 => $module->id,
                'name'               => $module->name,
                'students_done'      => $studentsDone,
                'avg_score'          => $avgScore,
                'completed_attempts' => $completedAttempts,
                'total_quizzes'      => $quizIds->count(),
            ];
        });

        // Statistik global
        $allAttempts    = Quiz_attempts::whereNotNull('finished_at')->get();
        $globalAvgScore = $allAttempts->count() > 0
            ? (int) round($allAttempts->avg('score'))
            : null;

        $totalAttempts = $allAttempts->count();

        return Inertia::render('Admin/Reports/Index', [
            'modules'          => $modulesStats,
            'total_students'   => $totalStudents,
            'total_modules'    => $totalModules,
            'global_avg_score' => $globalAvgScore,
            'total_attempts'   => $totalAttempts,
        ]);
    }

    /**
     * Detail per modul: daftar siswa + progress mereka
     */
    public function moduleHistory(Request $request, Learning_modules $modules)
    {
        $quizIds    = $this->getQuizIdsByModule($modules);
        $totalQuizzes = $quizIds->count();

        $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
            ->with('student')
            ->get();

        $grouped = $attempts
            ->filter(fn($a) => $a->student !== null)
            ->groupBy('student_id');

        $students = [];

        foreach ($grouped as $studentId => $studentAttempts) {
            $student = $studentAttempts->first()->student;

            $byQuiz = $studentAttempts
                ->groupBy('quiz_id')
                ->map(fn($g) => (int) $g->max('score'));

            $overall = $byQuiz->count() > 0 ? (int) round($byQuiz->avg()) : 0;

            // Persentase penyelesaian quiz
            $completion = $totalQuizzes > 0
                ? (int) round(($byQuiz->count() / $totalQuizzes) * 100)
                : 0;

            $students[] = [
                'id'             => $student->id,
                'name'           => $student->name,
                'class'          => $student->class?->name ?? '-',
                'quizzes_count'  => $byQuiz->count(),
                'quizzes_total'  => $totalQuizzes,
                'overall_score'  => $overall,
                'completion'     => $completion,
            ];
        }

        if ($request->boolean('include_all')) {
            $allStudents = User::where('role', 'siswa')->get();
            $existingIds = collect($students)->pluck('id')->toArray();
            foreach ($allStudents as $s) {
                if (in_array($s->id, $existingIds)) continue;
                $students[] = [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'class'         => $s->class?->name ?? '-',
                    'quizzes_count' => 0,
                    'quizzes_total' => $totalQuizzes,
                    'overall_score' => 0,
                    'completion'    => 0,
                ];
            }
        }

        usort($students, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        // Ringkasan modul untuk header
        $moduleAvg = collect($students)->avg('overall_score');
        $moduleSummary = [
            'total_students' => count($students),
            'avg_score'      => $students ? (int) round($moduleAvg) : null,
            'total_quizzes'  => $totalQuizzes,
        ];

        return Inertia::render('Admin/Reports/ModuleHistory', [
            'module'        => ['id' => $modules->id, 'name' => $modules->name],
            'students'      => $students,
            'module_summary' => $moduleSummary,
        ]);
    }

    /**
     * Laporan detail seorang siswa di satu modul
     */
    public function studentReport(Learning_modules $modules, User $student)
    {
        $quizIds = $this->getQuizIdsByModule($modules);

        $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
            ->where('student_id', $student->id)
            ->with('quiz')
            ->orderBy('finished_at')
            ->get();

        $quizzes = $attempts->map(fn($a) => [
            'quiz_id'     => $a->quiz_id,
            'quiz_title'  => $a->quiz?->title ?? 'Quiz',
            'score'       => (int) $a->score,
            'started_at'  => $a->started_at,
            'finished_at' => $a->finished_at,
        ])->values();

        $overall = $quizzes->count() > 0 ? (int) round($quizzes->avg('score')) : 0;

        $chartLabels = $quizzes->pluck('quiz_title')->toArray();
        $chartScores = $quizzes->pluck('score')->toArray();

        $scoreDistribution = [
            'low'    => $quizzes->filter(fn($q) => $q['score'] < 60)->count(),
            'medium' => $quizzes->filter(fn($q) => $q['score'] >= 60 && $q['score'] < 80)->count(),
            'high'   => $quizzes->filter(fn($q) => $q['score'] >= 80)->count(),
        ];

        return Inertia::render('Admin/Reports/StudentReport', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'student' => [
                'id'    => $student->id,
                'name'  => $student->name,
                'class' => $student->class?->name ?? null,
            ],
            'quizzes'           => $quizzes,
            'overall'           => $overall,
            'chartLabels'       => $chartLabels,
            'chartScores'       => $chartScores,
            'scoreDistribution' => $scoreDistribution,
        ]);
    }
}
