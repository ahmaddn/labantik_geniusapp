<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitMissionAnswersRequest;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Questions;
use App\Models\Quizzes;
use App\Models\Quiz_attempts;
use App\Models\User_answers;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  STUDENT PAGES
    // ─────────────────────────────────────────────────────────────

    public function missionsList(Request $request, Learning_modules $module)
    {
        $player = session('player');
        if (! $player) return redirect()->route('playground.login');

        $module->load(['missions.quizzes']);

        $missions = $module->missions->map(function ($mission) use ($player) {
            $totalQuestions = $mission->quizzes->sum(fn($q) => count($q->questions ?? []));
            $totalQuizzes   = $mission->quizzes->count();

            $completedQuizzes = $mission->quizzes->filter(fn($q) =>
                Quiz_attempts::where('quiz_id', $q->id)
                    ->where('student_id', $player['id'] ?? null)
                    ->exists()
            )->count();

            $status = 'not_started';
            if ($completedQuizzes > 0)
                $status = $completedQuizzes >= $totalQuizzes ? 'completed' : 'in_progress';

            $bestScore = Quiz_attempts::whereIn('quiz_id', $mission->quizzes->pluck('id'))
                ->where('student_id', $player['id'] ?? null)
                ->max('score') ?? 0;

            return [
                'id'                => $mission->id,
                'name'              => $mission->name,
                'description'       => $mission->hint ?? '',
                'status'            => $status,
                'total_questions'   => $totalQuestions,
                'completed_quizzes' => $completedQuizzes,
                'total_quizzes'     => $totalQuizzes,
                'best_score'        => $bestScore,
            ];
        });

        return Inertia::render('Playground/Missions/Index', [
            'module'   => ['id' => $module->id, 'name' => $module->name, 'description' => $module->description],
            'missions' => $missions,
            'user'     => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
        ]);
    }

    public function showMission(Request $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) return redirect()->route('playground.login');

        $mission->load([
            'quizzes.questions.options',
            'quizzes.questions.dragDropGroups.items',
        ]);

        $formattedMission = [
            'id'      => $mission->id,
            'name'    => $mission->name,
            'quizzes' => $mission->quizzes->map(fn($quiz) => [
                'id'         => $quiz->id,
                'type'       => $quiz->type,
                'title'      => $quiz->title,
                'time_limit' => $quiz->time_limit,
                'questions'  => $quiz->questions->map(function ($question) {
                    $formatted = [
                        'id'            => $question->id,
                        'question_text' => $question->question_text,
                        'quiz_id'       => $question->quiz_id,
                    ];

                    if ($question->options->count() > 0) {
                        $formatted['options'] = $question->options->map(fn($opt) => [
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
            ])->toArray(),
        ];

        return Inertia::render('Playground/Mission/Template', [
            'mission' => $formattedMission,
            'user'    => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
        ]);
    }

    /**
     * Dedicated result page — reads saved answers and builds full review data.
     */
    public function showResult(Request $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) return redirect()->route('playground.login');

        $mission->load([
            'quizzes.questions.options',
            'quizzes.questions.dragDropGroups.items',
        ]);

        $studentId       = $player['id'] ?? null;
        $totalCorrect    = 0;
        $totalIncorrect  = 0;
        $totalQuestions  = 0;
        $byType          = [];
        $questionsResult = [];

        foreach ($mission->quizzes as $quiz) {
            if ($quiz->type === 'materials') continue;

            $attempt = Quiz_attempts::where('quiz_id', $quiz->id)
                ->where('student_id', $studentId)
                ->latest()
                ->first();

            if (! $attempt) continue;

            $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');

            foreach ($quiz->questions as $question) {
                $answer = $answersByQuestion->get($question->id);
                if (! $answer) continue;

                $totalQuestions++;
                if (! isset($byType[$quiz->type])) {
                    $byType[$quiz->type] = ['correct' => 0, 'incorrect' => 0, 'total' => 0];
                }
                $byType[$quiz->type]['total']++;

                [
                    $isCorrect,
                    $userAnswerText,
                    $correctAnswerText,
                    $userAnswerMap,
                    $correctAnswerMap,
                ] = $this->checkAnswer($answer, $question);

                if ($isCorrect) {
                    $totalCorrect++;
                    $byType[$quiz->type]['correct']++;
                } else {
                    $totalIncorrect++;
                    $byType[$quiz->type]['incorrect']++;
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

        $breakdown = collect($byType)->map(fn($d, $type) => [
            'type'      => $type,
            'correct'   => $d['correct'],
            'incorrect' => $d['incorrect'],
            'total'     => $d['total'],
            'score'     => $d['total'] > 0 ? (int) round(($d['correct'] / $d['total']) * 100) : 0,
        ])->values()->toArray();

        return Inertia::render('Playground/Mission/Result', [
            'mission' => ['id' => $mission->id, 'name' => $mission->name],
            'results' => [
                'score'            => $score,
                'correct'          => $totalCorrect,
                'incorrect'        => $totalIncorrect,
                'total'            => $totalQuestions,
                'breakdown'        => $breakdown,
                'questions_result' => $questionsResult,
            ],
            'user' => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  SUBMIT
    // ─────────────────────────────────────────────────────────────

    public function submitMissionAnswers(SubmitMissionAnswersRequest $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) return response()->json(['error' => 'Unauthorized'], 401);

        $validated = $request->validated();
        $answers   = $validated['answers'];
        $quizIds   = $validated['quiz_ids'];
        $studentId = $player['id'] ?? null;

        try {
            foreach ($quizIds as $quizId) {
                // Only save answers that belong to this quiz
                $quizQuestionIds = Questions::where('quiz_id', $quizId)
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();

                $attempt = Quiz_attempts::updateOrCreate(
                    ['quiz_id' => $quizId, 'student_id' => $studentId],
                    ['started_at' => now(), 'finished_at' => now()]
                );

                foreach ($answers as $questionId => $answerValue) {
                    if (! in_array((string) $questionId, $quizQuestionIds)) continue;

                    if (is_array($answerValue)) {
                        User_answers::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                            ['selected_option_id' => null, 'selected_group_id' => null, 'response' => json_encode($answerValue)]
                        );
                    } else {
                        User_answers::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                            ['selected_option_id' => $answerValue, 'selected_group_id' => null, 'response' => (string) $answerValue]
                        );
                    }
                }

                // Persist score
                $score = $this->calcQuizScore($quizId, $studentId);
                $attempt->update(['score' => $score]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function calcQuizScore(string $quizId, ?string $studentId): int
    {
        $attempt = Quiz_attempts::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->latest()->first();

        if (! $attempt) return 0;

        $quiz = Quizzes::with(['questions.options', 'questions.dragDropGroups.items'])->find($quizId);
        if (! $quiz) return 0;

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

        return $total > 0 ? (int) round(($correct / $total) * 100) : 0;
    }

    /**
     * Core answer checker.
     *
     * Returns: [bool $isCorrect, string $userText, string $correctText, array $userMap, array $correctMap]
     * $userMap / $correctMap only populated for drag_drop: ['item label' => 'group name']
     */
    private function checkAnswer(User_answers $answer, $question): array
    {
        $userText    = '';
        $correctText = '';
        $userMap     = [];
        $correctMap  = [];

        // ── Options-based questions ───────────────────────────────
        if ($question->options && $question->options->count() > 0) {
            $allOptions  = $question->options->keyBy('id');
            $correctOpts = $question->options->where('is_correct', true);
            $correctIds  = $correctOpts->pluck('id')->map(fn($id) => (string) $id)->sort()->values()->toArray();
            $correctText = $correctOpts->pluck('option_text')->implode(', ');

            $responseStr = trim($answer->response ?? '');

            // Multiple selected (JSON array)
            if (str_starts_with($responseStr, '[')) {
                $selectedIds = collect(json_decode($responseStr, true) ?? [])
                    ->map(fn($id) => (string) $id)->sort()->values()->toArray();

                $userText  = collect($selectedIds)
                    ->map(fn($id) => $allOptions->get($id)?->option_text ?? $id)
                    ->implode(', ');

                return [$selectedIds === $correctIds, $userText, $correctText, [], []];
            }

            // Single selected
            $selectedId = $answer->selected_option_id
                ? (string) $answer->selected_option_id
                : $responseStr;

            $userText  = $allOptions->get($selectedId)?->option_text ?? $selectedId;
            $isCorrect = count($correctIds) === 1 && $selectedId === $correctIds[0];
            return [$isCorrect, $userText, $correctText, [], []];
        }

        // ── Drag & drop ──────────────────────────────────────────
        $responseStr = trim($answer->response ?? '');
        if (str_starts_with($responseStr, '{')) {
            $placed = json_decode($responseStr, true) ?? [];

            $question->loadMissing('dragDropGroups.items');

            if (! $question->dragDropGroups || $question->dragDropGroups->isEmpty()) {
                return [false, '', '', [], []];
            }

            // Build lookup maps
            $itemToCorrectGroup = []; // itemId (string) => correctGroupId (string)
            $itemLabels         = []; // itemId => item_text
            $groupLabels        = []; // groupId => group_name

            foreach ($question->dragDropGroups as $group) {
                $groupLabels[(string) $group->id] = $group->group_name;
                foreach ($group->items as $item) {
                    $itemLabels[(string) $item->id]         = $item->item_text;
                    $itemToCorrectGroup[(string) $item->id] = (string) $group->id;
                    $correctMap[$item->item_text]            = $group->group_name;
                }
            }

            $allCorrect = true;
            foreach ($itemToCorrectGroup as $itemId => $correctGroupId) {
                $placedGroupId  = isset($placed[$itemId]) ? (string) $placed[$itemId] : null;
                $userGroupName  = $placedGroupId
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

    // ─────────────────────────────────────────────────────────────
    //  LEGACY
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) return redirect()->route('playground.login');
        return Inertia::render('Playground/MissionPage', ['learningModules' => []]);
    }

    public function show(Request $request, Learning_modules $learningModule)
    {
        $user = $request->user();
        $learningModule->load(['template', 'missions.materials', 'missions.quizzes.quizAttempts']);
        return Inertia::render('Playground/MissionShow', [
            'module' => $learningModule,
            'auth'   => ['user' => $user],
        ]);
    }
}
