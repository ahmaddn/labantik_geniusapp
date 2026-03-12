<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Quizzes;
use App\Models\Quiz_attempts;
use App\Models\Materials;
use App\Models\Templates;
use App\Models\Classes;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Basic counts ───────────────────────────────────────────
        $totalModules   = Learning_modules::count();
        $totalMissions  = Missions::count();
        $totalQuizzes   = Quizzes::count();
        $totalMaterials = Materials::count();
        $totalTemplates = Templates::count();
        $totalClasses   = Classes::count();
        $totalStudents  = User::where('role', 'siswa')->count();
        $totalTeachers  = User::where('role', 'guru')->count();
        $activeModules  = Learning_modules::where('is_active', true)->count();

        // ── Quiz attempts ──────────────────────────────────────────
        // totalAttempts = semua attempts (termasuk yang belum selesai)
        $totalAttempts = Quiz_attempts::count();

        // Rata-rata nilai hanya dari yang sudah selesai (ada finished_at & score)
        $globalAvgScore = Quiz_attempts::whereNotNull('finished_at')
            ->whereNotNull('score')
            ->avg('score');
        $globalAvgScore = $globalAvgScore !== null ? (int) round($globalAvgScore) : null;

        // ── Score distribution — hanya dari yang sudah selesai ─────
        $scoreDistribution = [
            'low'    => Quiz_attempts::whereNotNull('finished_at')->where('score', '<', 60)->count(),
            'medium' => Quiz_attempts::whereNotNull('finished_at')->whereBetween('score', [60, 79])->count(),
            'high'   => Quiz_attempts::whereNotNull('finished_at')->where('score', '>=', 80)->count(),
        ];

        // ── Recent modules (5 terbaru) ─────────────────────────────
        $modules = Learning_modules::select('id', 'name', 'is_active', 'created_at')
            ->withCount('missions')
            ->withCount('quizzes')
            ->latest()
            ->take(5)
            ->get();

        $moduleIds = $modules->pluck('id')->toArray();

        // Quiz langsung di modul
        $directQuizzes = Quizzes::whereIn('module_id', $moduleIds)
            ->select('id', 'module_id')
            ->get();

        // Quiz via missions
        $missionQuizzes = Quizzes::whereNotNull('mission_id')
            ->whereHas('mission', fn($q) => $q->whereIn('module_id', $moduleIds))
            ->with('mission:id,module_id')
            ->select('id', 'mission_id')
            ->get();

        // Map module_id → [quiz_ids]
        $quizIdsByModule = [];
        foreach ($directQuizzes as $quiz) {
            $quizIdsByModule[$quiz->module_id][] = $quiz->id;
        }
        foreach ($missionQuizzes as $quiz) {
            $mid = $quiz->mission?->module_id;
            if ($mid) $quizIdsByModule[$mid][] = $quiz->id;
        }

        $allRelevantQuizIds = collect($quizIdsByModule)->flatten()->unique()->toArray();

        // Hitung distinct students yang sudah mengerjakan (ada finished_at)
        $doneAttempts = Quiz_attempts::whereIn('quiz_id', $allRelevantQuizIds)
            ->whereNotNull('finished_at')
            ->select('quiz_id', 'student_id')
            ->get();

        $quizToModule = [];
        foreach ($quizIdsByModule as $mid => $qids) {
            foreach ($qids as $qid) {
                $quizToModule[$qid] = $mid;
            }
        }

        $studentsDoneByModule = [];
        foreach ($doneAttempts as $attempt) {
            $mid = $quizToModule[$attempt->quiz_id] ?? null;
            if (!$mid) continue;
            $studentsDoneByModule[$mid][$attempt->student_id] = true;
        }

        $recentModules = $modules->map(fn($m) => [
            'id'             => $m->id,
            'name'           => $m->name,
            'is_active'      => (bool) $m->is_active,
            'missions_count' => $m->missions_count ?? 0,
            'quizzes_count'  => $m->quizzes_count  ?? 0,
            'students_done'  => count($studentsDoneByModule[$m->id] ?? []),
            'created_at'     => Carbon::parse($m->created_at)->diffForHumans(),
        ]);

        // ── Recent students (5 terbaru) ────────────────────────────
        $recentStudents = User::where('role', 'siswa')
            ->with('class:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'class'      => $u->class?->name ?? '-',
                'created_at' => Carbon::parse($u->created_at)->diffForHumans(),
            ]);

        // ── Recent attempts — hanya yang sudah selesai ─────────────
        $recentAttempts = Quiz_attempts::whereNotNull('finished_at')
            ->with([
                'student:id,name',
                'quiz:id,title',
            ])
            ->latest('finished_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'student_name' => $a->student?->name ?? '-',
                'quiz_title'   => $a->quiz?->title   ?? '-',
                'score'        => (int) ($a->score ?? 0),
                'finished_at'  => Carbon::parse($a->finished_at)->diffForHumans(),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_modules'    => $totalModules,
                'total_missions'   => $totalMissions,
                'total_quizzes'    => $totalQuizzes,
                'total_materials'  => $totalMaterials,
                'total_templates'  => $totalTemplates,
                'total_classes'    => $totalClasses,
                'total_students'   => $totalStudents,
                'total_teachers'   => $totalTeachers,
                'total_attempts'   => $totalAttempts,
                'global_avg_score' => $globalAvgScore,
                'active_modules'   => $activeModules,
                'inactive_modules' => $totalModules - $activeModules,
            ],
            'recent_modules'     => $recentModules,
            'recent_students'    => $recentStudents,
            'recent_attempts'    => $recentAttempts,
            'score_distribution' => $scoreDistribution,
        ]);
    }
}
