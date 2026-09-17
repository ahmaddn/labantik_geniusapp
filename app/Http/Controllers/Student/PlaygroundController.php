<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Quiz_attempts;
use App\Models\Quizzes;
use Inertia\Inertia;

class PlaygroundController extends Controller
{
    /**
     * Display the playground index page
     */
    public function index()
    {
        if (! session()->has('player')) {
            return redirect()->route('playground.login');
        }

        $player = session('player');

        $userData = [
            'name' => $player['nama'] ?? 'Siswa',
            'email' => $player['email'] ?? ($player['nama'] ?? 'siswa').'@example.com',
            'class' => [
                'name' => $player['nama_kelas'] ?? '-',
            ],
        ];
        $learningModules = $this->getLearningModules($player['id'] ?? null, $player['kelas'] ?? null);

        return Inertia::render('Playground/Index', [
            'user' => $userData,
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
                    $query->whereHas('classes', function ($q) use ($classId) {
                        $q->where('classes.id', $classId);
                    });
                }
                $query->orWhereDoesntHave('classes');
            })
            ->orderBy('name')
            ->with(['missions.quizzes', 'classes:id,name'])
            ->get();

        $allQuizzes = Quizzes::whereIn('module_id', $modules->pluck('id'))->get();
        $allQuizIds = $allQuizzes->pluck('id');

        $attemptsByQuiz = Quiz_attempts::whereIn('quiz_id', $allQuizIds)
            ->where('student_id', $studentId)
            ->get()
            ->groupBy('quiz_id');

        return $modules->map(function ($module) use ($allQuizzes, $attemptsByQuiz) {
            $moduleQuizzes = $allQuizzes->where('module_id', $module->id);

            // ── 1. Pretest ──────────────────────────────────────────
            $pretestQuiz = $moduleQuizzes->firstWhere('category', 'pretest');
            $pretestDone = $pretestQuiz ? $attemptsByQuiz->has($pretestQuiz->id) : true;

            // ── 2. Semua misi ───────────────────────────────────────
            $missions = $module->missions;

            if ($missions->isEmpty()) {
                $allMissionsDone = true;
            } else {
                $allMissionsDone = $missions->every(function ($mission) use ($attemptsByQuiz) {
                    $quizzes = $mission->quizzes;
                    if ($quizzes->isEmpty()) {
                        return true;
                    }

                    return $quizzes->every(fn ($quiz) => $attemptsByQuiz->has($quiz->id));
                });
            }

            // ── 3. Posttest ─────────────────────────────────────────
            $posttestQuiz = $moduleQuizzes->firstWhere('category', 'posttest');
            $posttestDone = $posttestQuiz ? $attemptsByQuiz->has($posttestQuiz->id) : true;

            // ── has_attempt & best_score (dari semua quiz di modul) ─
            $modQuizIds = $moduleQuizzes->pluck('id');
            $hasAttempt = false;
            $bestScore = 0;

            foreach ($modQuizIds as $qId) {
                if ($attemptsByQuiz->has($qId)) {
                    $hasAttempt = true;
                    $maxScore = $attemptsByQuiz->get($qId)->max('score');
                    if ($maxScore > $bestScore) {
                        $bestScore = $maxScore;
                    }
                }
            }

            // ── Fully completed ─────────────────────────────────────
            $fullyCompleted = $pretestDone && $allMissionsDone && $posttestDone && $hasAttempt;

            return [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'thumbnail' => $module->thumbnail
                                        ? asset('storage/'.$module->thumbnail)
                                        : null,
                'has_attempt' => $hasAttempt,
                'best_score' => $bestScore,
                'finished' => $fullyCompleted, // dipakai Vue untuk styling lama
                'fully_completed' => $fullyCompleted, // flag eksplisit untuk tombol
                'is_general' => $module->classes->isEmpty(), // flag modul umum / general
            ];
        })->toArray();
    }
}
