<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Quiz_attempts;
use App\Models\Quizzes;
use App\Http\Controllers\Controller;

class PlaygroundController extends Controller
{
    /**
     * Display the playground index page
     */
    public function index()
    {
        if (!session()->has('player')) {
            return redirect()->route('playground.login');
        }

        $player = session('player');

        $userData = [
            'name'  => $player['nama']  ?? 'Siswa',
            'email' => $player['email'] ?? ($player['nama'] ?? 'siswa') . '@example.com',
            'class' => [
                'name' => $player['nama_kelas'] ?? '-',
            ],
        ];
        $learningModules = $this->getLearningModules($player['id'] ?? null, $player['kelas'] ?? null);

        return Inertia::render('Playground/Index', [
            'user'            => $userData,
            'learningModules' => $learningModules,
        ]);
    }

    /**
     * Ambil semua modul aktif + hitung status per modul untuk siswa ini.
     *
     * Status sebuah modul dianggap "selesai" (fully_completed) jika:
     *   1. Pretest  → ada Quiz_attempt untuk quiz category='pretest' milik modul
     *   2. Misi     → semua misi di modul punya attempt di seluruh quiznya
     *   3. Posttest → ada Quiz_attempt untuk quiz category='posttest' milik modul
     */
    private function getLearningModules(?string $studentId, ?string $classId): array
    {
        $modules = Learning_modules::where('is_active', true)
            ->where(function ($query) use ($classId) {
                if ($classId) {
                    $query->whereHas('classes', function($q) use ($classId) {
                        $q->where('classes.id', $classId);
                    });
                }
                $query->orWhereDoesntHave('classes');
            })
            ->orderBy('name')
            ->with(['missions.quizzes', 'classes:id,name'])
            ->get();

        return $modules->map(function ($module) use ($studentId) {

            // ── 1. Pretest ──────────────────────────────────────────
            $pretestQuiz = Quizzes::where('module_id', $module->id)
                ->where('category', 'pretest')
                ->first();

            $pretestDone = $pretestQuiz
                ? Quiz_attempts::where('quiz_id', $pretestQuiz->id)
                    ->where('student_id', $studentId)
                    ->exists()
                : true; // kalau tidak ada pretest, anggap sudah lewat

            // ── 2. Semua misi ───────────────────────────────────────
            $missions        = $module->missions;
            
            if ($missions->isEmpty()) {
                $allMissionsDone = true;
            } else {
                $allMissionsDone = $missions->every(function ($mission) use ($studentId) {
                    $quizzes = $mission->quizzes->where('category', 'mission');
                    if ($quizzes->isEmpty()) return true;
                    
                    return $quizzes->every(fn ($quiz) =>
                        Quiz_attempts::where('quiz_id', $quiz->id)
                            ->where('student_id', $studentId)
                            ->exists()
                    );
                });
            }

            // ── 3. Posttest ─────────────────────────────────────────
            $posttestQuiz = Quizzes::where('module_id', $module->id)
                ->where('category', 'posttest')
                ->first();

            $posttestDone = $posttestQuiz
                ? Quiz_attempts::where('quiz_id', $posttestQuiz->id)
                    ->where('student_id', $studentId)
                    ->exists()
                : true; // kalau tidak ada posttest, anggap sudah lewat

            // ── has_attempt & best_score (dari semua quiz di modul) ─
            $allQuizIds = Quizzes::where('module_id', $module->id)->pluck('id');

            $attempts = Quiz_attempts::whereIn('quiz_id', $allQuizIds)
                ->where('student_id', $studentId)
                ->get();

            $hasAttempt = $attempts->isNotEmpty();
            $bestScore  = $hasAttempt ? (int) $attempts->max('score') : 0;

            // ── Fully completed ─────────────────────────────────────
            $fullyCompleted = $pretestDone && $allMissionsDone && $posttestDone && $hasAttempt;

            return [
                'id'               => $module->id,
                'name'             => $module->name,
                'description'      => $module->description,
                'thumbnail'        => $module->thumbnail
                                        ? asset('storage/' . $module->thumbnail)
                                        : null,
                'has_attempt'      => $hasAttempt,
                'best_score'       => $bestScore,
                'finished'         => $fullyCompleted, // dipakai Vue untuk styling lama
                'fully_completed'  => $fullyCompleted, // flag eksplisit untuk tombol
                'is_general'       => $module->classes->isEmpty(), // flag modul umum / general
            ];
        })->toArray();
    }

    /**
     * Reset progress for the entire module.
     */
    public function reset(Learning_modules $module)
    {
        $player = session('player');
        if (! $player) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $studentId = $player['id'] ?? null;

        // 1. Delete all quiz attempts of the module (cascade deletes user_answers)
        $quizIds = Quizzes::where('module_id', $module->id)->pluck('id');
        if ($quizIds->isNotEmpty()) {
            $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
                ->where('student_id', $studentId)
                ->get();
            foreach ($attempts as $attempt) {
                $attempt->delete();
            }
        }

        // 2. Delete StudentMissionLog for all missions of the module
        \App\Models\StudentMissionLog::where('module_id', $module->id)
            ->where('user_id', $studentId)
            ->delete();

        // 3. Delete Reflection_answers for reflection questions of missions in this module
        $missionIds = Missions::where('module_id', $module->id)->pluck('id');
        if ($missionIds->isNotEmpty()) {
            $reflectionQuestionIds = \App\Models\Reflection_questions::whereIn('mission_id', $missionIds)->pluck('id');
            if ($reflectionQuestionIds->isNotEmpty()) {
                \App\Models\Reflection_answers::whereIn('reflection_question_id', $reflectionQuestionIds)
                    ->where('user_id', $studentId)
                    ->delete();
            }
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Progress modul berhasil direset.');
    }
}
