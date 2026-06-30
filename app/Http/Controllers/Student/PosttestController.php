<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Questions;
use App\Models\Quiz_attempts;
use App\Models\Quizzes;
use App\Models\User_answers;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosttestController extends Controller
{
    /**
     * Tampilkan halaman posttest.
     * Route: GET /player/modules/{module}/posttest
     * Name:  playground.posttest.show
     */
    public function show(Learning_modules $module)
    {
        $module->load(['template.backgrounds', 'template.mascots']);

        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        // Ambil quiz posttest milik modul ini
        $quiz = Quizzes::where('module_id', $module->id)
            ->where('category', 'posttest')
            ->with([
                'questions.options',
                'questions.mascot',
                'questions.dragDropGroups.items',
            ])
            ->first();
        $background = null;
        $backsound = null;
        if (!empty($module->template?->backsound)) {
            $backsound = asset('storage/' . $module->template->backsound);
        }

        // Kalau tidak ada posttest → kembali ke beranda
        if (! $quiz) {
            return redirect()->route('playground.index');
        }

        // Kalau posttest sudah pernah dikerjakan → kembali ke beranda
        $alreadyDone = Quiz_attempts::where('quiz_id', $quiz->id)
            ->where('student_id', $player['id'] ?? null)
            ->exists();

        if ($alreadyDone) {
            return redirect()->route('playground.index');
        }

        $questionsCollection = $quiz->questions;
        if ($quiz->is_randomized) {
            $questionsCollection = $questionsCollection->shuffle();
        } else {
            $questionsCollection = $questionsCollection->sortBy('order_number')->values();
        }

        // Format sama persis dengan MissionController
        $formattedQuiz = [
            'id'         => $quiz->id,
            'type'       => $quiz->type,
            'title'      => $quiz->title,
            'time_limit' => $quiz->time_limit,
            'image'      => $quiz->image,
            'questions'  => $questionsCollection->map(function ($question) {
                $formatted = [
                    'id'            => $question->id,
                    'question_text' => $question->question_text,
                    'quiz_id'       => $question->quiz_id,
                    'feedback_correct'   => $question->feedback_correct,
                    'feedback_incorrect' => $question->feedback_incorrect,
                    'explanation'        => $question->explanation,
                    'mascot' => $question->mascot ? [
                        'id'        => $question->mascot->id,
                        'name_pose' => $question->mascot->name_pose,
                        'image'     => $question->mascot->image
                            ? asset('storage/' . $question->mascot->image)
                            : null,
                    ] : null,
                ];

                if ($question->options->count() > 0) {
                    $formatted['options'] = $question->options->sortBy('created_at')->values()->map(fn($opt) => [
                        'id'           => $opt->id,
                        'text'         => $opt->option_text,
                        'option_text'  => $opt->option_text,
                        'option_image' => $opt->option_image,
                        'is_correct'   => (bool) $opt->is_correct,
                    ])->toArray();
                }

                if ($question->dragDropGroups->count() > 0) {
                    $formatted['drag_drop_items']  = [];
                    $formatted['drag_drop_groups'] = $question->dragDropGroups->map(function ($group) use (&$formatted) {
                        foreach ($group->items as $item) {
                            $formatted['drag_drop_items'][] = [
                                'id'               => $item->id,
                                'item_text'        => $item->item_text,
                                'item_image'       => $item->item_image,
                                'correct_group_id' => $group->id,
                            ];
                        }
                        return ['id' => $group->id, 'group_name' => $group->group_name];
                    })->toArray();
                }

                return $formatted;
            })->toArray(),
        ];

        return Inertia::render('Playground/Mission/TemplatePosttest', [
            'quiz'   => $formattedQuiz,
            'module' => [
                'id'          => $module->id,
                'name'        => $module->name,
                'description' => $module->description,
                'template'    => $module->template,
            ],
            'user' => [
                'name'  => $player['nama'] ?? 'Siswa',
                'class' => $player['nama_kelas'] ?? '-',
            ],
            'backsound' => $backsound,
            'background' => $background,
        ]);
    }

    /**
     * Simpan jawaban posttest.
     * Route: POST /player/posttest/submit
     * Name:  playground.posttest.submit
     *
     * Payload dari Vue:
     * {
     *   quiz_id:    string,
     *   module_id:  string,
     *   time_taken: int,
     *   answers: [ { question_id, value } ]
     * }
     */
    public function submit(Request $request)
    {
        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        $request->validate([
            'quiz_id'               => 'required|exists:quizzes,id',
            'module_id'             => 'required|exists:learning_modules,id',
            'time_taken'            => 'nullable|integer|min:0',
            'answers'               => 'required|array',
            'answers.*.question_id' => 'required',
            'answers.*.value'       => 'nullable',
        ]);

        $studentId = $player['id'] ?? null;
        $quizId    = $request->quiz_id;

        // Buat / update attempt
        $attempt = Quiz_attempts::updateOrCreate(
            ['quiz_id' => $quizId, 'student_id' => $studentId],
            ['started_at' => now(), 'finished_at' => now()]
        );

        // Simpan tiap jawaban
        $quizQuestionIds = Questions::where('quiz_id', $quizId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        foreach ($request->answers as $ans) {
            $questionId  = $ans['question_id'];
            $answerValue = $ans['value'];

            if (! in_array((string) $questionId, $quizQuestionIds)) {
                continue;
            }

            if (is_array($answerValue)) {
                User_answers::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'selected_option_id' => null,
                        'selected_group_id'  => null,
                        'response'           => json_encode($answerValue),
                    ]
                );
            } else {
                User_answers::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'selected_option_id' => $answerValue,
                        'selected_group_id'  => null,
                        'response'           => (string) $answerValue,
                    ]
                );
            }
        }

        // Hitung dan simpan skor
        $score = $this->calcScore($quizId, $studentId);
        $attempt->update(['score' => $score]);

        // Setelah posttest → arahkan ke halaman feedback kuis posttest
        return redirect()->route('playground.posttest.quiz-result', $request->module_id);
    }

    public function showQuizResult(Request $request, Learning_modules $module)
    {
        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        // Ambil quiz posttest milik modul ini
        $quiz = Quizzes::where('module_id', $module->id)
            ->where('category', 'posttest')
            ->with([
                'questions.options',
                'questions.dragDropGroups.items',
            ])
            ->first();

        if (! $quiz) {
            return redirect()->route('playground.index');
        }

        $studentId      = $player['id'] ?? null;
        $totalCorrect   = 0;
        $totalIncorrect = 0;
        $totalQuestions = 0;
        $questionsResult = [];

        $attempt = Quiz_attempts::where('quiz_id', $quiz->id)
            ->where('student_id', $studentId)
            ->latest()
            ->first();

        if ($attempt) {
            $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');

            foreach ($quiz->questions as $question) {
                $answer = $answersByQuestion->get($question->id);

                if (! $answer) {
                    continue;
                }

                $totalQuestions++;

                [
                    $isCorrect,
                    $userAnswerText,
                    $correctAnswerText,
                    $userAnswerMap,
                    $correctAnswerMap,
                ] = $this->checkAnswer($answer, $question);

                if ($isCorrect) {
                    $totalCorrect++;
                } else {
                    $totalIncorrect++;
                }

                $questionsResult[] = [
                    'question_id'         => $question->id,
                    'question_text'       => $question->question_text,
                    'quiz_type'           => $quiz->type,
                    'quiz_title'          => $quiz->title,
                    'is_correct'          => $isCorrect,
                    'user_answer_text'    => $userAnswerText,
                    'correct_answer_text' => $correctAnswerText,
                    'user_answer_map'     => $userAnswerMap,
                    'correct_answer_map'  => $correctAnswerMap,
                ];
            }
        }

        $score = $totalQuestions > 0
            ? (int) round(($totalCorrect / $totalQuestions) * 100)
            : 0;

        return Inertia::render('Playground/Mission/Result', [
            'mission'           => ['id' => null, 'name' => 'Posttest ' . $module->name, 'title' => 'Posttest'],
            'next_mission'      => null,
            'results'           => [
                'score'            => $score,
                'correct'          => $totalCorrect,
                'incorrect'        => $totalIncorrect,
                'total'            => $totalQuestions,
                'correct_answers'  => $totalCorrect,
                'total_questions'  => $totalQuestions,
                'details'          => collect($questionsResult)->map(fn($q) => [
                    'question_id' => $q['question_id'],
                    'question' => [
                        'id' => $q['question_id'],
                        'question_text' => $q['question_text'],
                        'type' => $q['quiz_type'],
                        'explanation' => Questions::find($q['question_id'])?->explanation,
                    ],
                    'is_correct' => $q['is_correct'],
                    'user_answer' => $q['user_answer_text'],
                    'correct_answer' => $q['correct_answer_text'],
                    'user_answer_map' => $q['user_answer_map'],
                    'correct_answer_map' => $q['correct_answer_map'],
                ])->toArray(),
            ],
            'user'              => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'module'            => ['id' => $module->id, 'name' => $module->name],
            'all_missions_done' => false,
            'is_posttest'       => true,
        ]);
    }

    /**
     * Tampilkan halaman result keseluruhan (Pretest + Misi + Posttest).
     */
    public function overallResult(Learning_modules $module)
    {
        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        $studentId = $player['id'] ?? null;

        // ── Helper: hitung correct/incorrect/total dari satu quiz ──────────
        $calcQuizStats = function (Quizzes $quiz) use ($studentId): array {
            $attempt = Quiz_attempts::where('quiz_id', $quiz->id)
                ->where('student_id', $studentId)
                ->latest()->first();

            if (! $attempt) {
                return ['correct' => 0, 'incorrect' => 0, 'total' => 0, 'score' => 0];
            }

            $quiz->loadMissing(['questions.options', 'questions.dragDropGroups.items']);
            $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');

            $correct = 0;
            $total   = 0;
            foreach ($quiz->questions as $question) {
                $answer = $answersByQuestion->get($question->id);
                if (! $answer) continue;
                $total++;
                [$isCorrect] = $this->checkAnswer($answer, $question);
                if ($isCorrect) $correct++;
            }

            return [
                'correct'   => $correct,
                'incorrect' => $total - $correct,
                'total'     => $total,
                'score'     => $total > 0 ? (int) round(($correct / $total) * 100) : 0,
            ];
        };

        // ── Pretest ────────────────────────────────────────────────────────
        $pretestQuiz  = Quizzes::where('module_id', $module->id)->where('category', 'pretest')->first();
        $pretestStats = $pretestQuiz ? $calcQuizStats($pretestQuiz) : ['correct' => 0, 'incorrect' => 0, 'total' => 0, 'score' => 0];

        // ── Misi (gabungan semua quiz category=mission di modul ini) ───────
        $missionQuizzes = Quizzes::where('module_id', $module->id)->where('category', 'mission')->get();
        $missionStats   = ['correct' => 0, 'incorrect' => 0, 'total' => 0, 'score' => 0];
        foreach ($missionQuizzes as $mQuiz) {
            $s = $calcQuizStats($mQuiz);
            $missionStats['correct']   += $s['correct'];
            $missionStats['incorrect'] += $s['incorrect'];
            $missionStats['total']     += $s['total'];
        }
        $missionStats['score'] = $missionStats['total'] > 0
            ? (int) round(($missionStats['correct'] / $missionStats['total']) * 100)
            : 0;

        // ── Posttest ───────────────────────────────────────────────────────
        $posttestQuiz  = Quizzes::where('module_id', $module->id)->where('category', 'posttest')->first();
        $posttestStats = $posttestQuiz ? $calcQuizStats($posttestQuiz) : ['correct' => 0, 'incorrect' => 0, 'total' => 0, 'score' => 0];

        // ── Skor akhir (rata-rata ketiga bagian) ───────────────────────────
        $totalAll   = $pretestStats['total'] + $missionStats['total'] + $posttestStats['total'];
        $correctAll = $pretestStats['correct'] + $missionStats['correct'] + $posttestStats['correct'];
        $finalScore = $totalAll > 0 ? (int) round(($correctAll / $totalAll) * 100) : 0;

        return Inertia::render('Playground/Mission/Result', [
            'is_overall' => true,
            'module'     => ['id' => $module->id, 'name' => $module->name],
            'user'       => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'mission'    => ['id' => null, 'name' => 'Hasil Akhir Modul'],
            'results'    => [
                'score'           => $finalScore,
                'correct_answers' => $correctAll,
                'total_questions' => $totalAll,
                'overall_score'   => $finalScore,
                'overall_correct' => $correctAll,
                'overall_total'   => $totalAll,
                'pretest'  => $pretestStats,
                'missions' => $missionStats,
                'posttest' => $posttestStats,
            ],
            'all_missions_done' => true,
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function calcScore(string $quizId, ?string $studentId): int
    {
        $attempt = Quiz_attempts::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->latest()->first();

        if (! $attempt) return 0;

        $quiz = Quizzes::with(['questions.options', 'questions.dragDropGroups.items'])
            ->find($quizId);
        if (! $quiz) return 0;

        $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');
        $totalCorrect = 0;
        $totalCount   = 0;

        foreach ($quiz->questions as $question) {
            $answer = $answersByQuestion->get($question->id);
            if (! $answer) continue;

            $totalCount++;
            [$isCorrect] = $this->checkAnswer($answer, $question);
            if ($isCorrect) $totalCorrect++;
        }

        return $totalCount > 0 ? (int) round(($totalCorrect / $totalCount) * 100) : 0;
    }

    /**
     * Identik dengan MissionController::checkAnswer() dan PretestController::checkAnswer()
     */
    private function checkAnswer(User_answers $answer, $question): array
    {
        $userText    = '';
        $correctText = '';
        $userMap     = [];
        $correctMap  = [];

        $quizType = $question->quiz?->type ?? '';
        $isShortOrReflection = in_array($quizType, ['short_answer', 'reflection']);

        if ($isShortOrReflection) {
            $responseStr = trim($answer->response ?? '');
            
            // Get correct/reference answer from options if any
            if ($question->options && $question->options->count() > 0) {
                $correctOpts = $question->options->where('is_correct', true);
                if ($correctOpts->isEmpty()) {
                    $correctOpts = $question->options;
                }
                $correctText = $correctOpts->pluck('option_text')->implode(', ');
            }
            
            return [true, $responseStr, $correctText, [], []];
        }

        // Options-based (multiple_choices, true_false, case_study)
        if ($question->options && $question->options->count() > 0) {
            $allOptions  = $question->options->keyBy('id');
            $correctOpts = $question->options->where('is_correct', true);
            $correctIds  = $correctOpts->pluck('id')->map(fn($id) => (string) $id)->sort()->values()->toArray();
            $correctText = $correctOpts->pluck('option_text')->implode(', ');

            $responseStr = trim($answer->response ?? '');

            if (str_starts_with($responseStr, '[')) {
                $selectedIds = collect(json_decode($responseStr, true) ?? [])
                    ->map(fn($id) => (string) $id)->sort()->values()->toArray();

                $userText = collect($selectedIds)
                    ->map(fn($id) => $allOptions->get($id)?->option_text ?? $id)
                    ->implode(', ');

                return [$selectedIds === $correctIds, $userText, $correctText, [], []];
            }

            $selectedId = $answer->selected_option_id
                ? (string) $answer->selected_option_id
                : $responseStr;

            $userText  = $allOptions->get($selectedId)?->option_text ?? $selectedId;
            $isCorrect = count($correctIds) === 1 && $selectedId === $correctIds[0];

            return [$isCorrect, $userText, $correctText, [], []];
        }

        // Drag & drop
        $responseStr = trim($answer->response ?? '');
        if (str_starts_with($responseStr, '{')) {
            $placed = json_decode($responseStr, true) ?? [];

            $question->loadMissing('dragDropGroups.items');

            if (! $question->dragDropGroups || $question->dragDropGroups->isEmpty()) {
                return [false, '', '', [], []];
            }

            $itemToCorrectGroup = [];
            $itemLabels         = [];
            $groupLabels        = [];

            foreach ($question->dragDropGroups as $group) {
                $groupLabels[(string) $group->id] = $group->group_name;
                foreach ($group->items as $item) {
                    $itemLabels[(string) $item->id]         = $item->item_text;
                    $itemToCorrectGroup[(string) $item->id] = (string) $group->id;
                    $correctMap[$item->item_text]           = $group->group_name;
                }
            }

            $allCorrect = true;
            foreach ($itemToCorrectGroup as $itemId => $correctGroupId) {
                $placedGroupId = isset($placed[$itemId]) ? (string) $placed[$itemId] : null;
                $userGroupName = $placedGroupId
                    ? ($groupLabels[$placedGroupId] ?? $placedGroupId)
                    : '(tidak dijawab)';

                $userMap[$itemLabels[$itemId]] = $userGroupName;

                if ($placedGroupId !== $correctGroupId) {
                    $allCorrect = false;
                }
            }

            return [$allCorrect, '', '', $userMap, $correctMap];
        }

        return [false, '', '', [], []];
    }
}
