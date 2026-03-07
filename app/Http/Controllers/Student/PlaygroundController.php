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

        $learningModules = $this->getLearningModules($player['id'] ?? null);

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
    private function getLearningModules(?string $studentId): array
    {
        $modules = Learning_modules::where('is_active', true)
            ->orderBy('name')
            ->with(['missions.quizzes'])
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
            $allMissionsDone = $missions->isNotEmpty() && $missions->every(function ($mission) use ($studentId) {
                $quizzes = $mission->quizzes->where('category', 'mission');
                if ($quizzes->isEmpty()) return false;
                return $quizzes->every(fn ($quiz) =>
                    Quiz_attempts::where('quiz_id', $quiz->id)
                        ->where('student_id', $studentId)
                        ->exists()
                );
            });

            // ── 3. Posttest ─────────────────────────────────────────
            $posttestQuiz = Quizzes::where('module_id', $module->id)
                ->where('category', 'posttest')
                ->first();

            $posttestDone = $posttestQuiz
                ? Quiz_attempts::where('quiz_id', $posttestQuiz->id)
                    ->where('student_id', $studentId)
                    ->exists()
                : true; // kalau tidak ada posttest, anggap sudah lewat

            // ── Fully completed ─────────────────────────────────────
            $fullyCompleted = $pretestDone && $allMissionsDone && $posttestDone;

            // ── has_attempt & best_score (dari semua quiz di modul) ─
            $allQuizIds = Quizzes::where('module_id', $module->id)->pluck('id');

            $attempts = Quiz_attempts::whereIn('quiz_id', $allQuizIds)
                ->where('student_id', $studentId)
                ->get();

            $hasAttempt = $attempts->isNotEmpty();
            $bestScore  = $hasAttempt ? (int) $attempts->max('score') : 0;

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
            ];
        })->toArray();
    }
}
