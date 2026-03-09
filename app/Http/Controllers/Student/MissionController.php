<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitMissionAnswersRequest;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Questions;
use App\Models\Quiz_attempts;
use App\Models\Quizzes;
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
        if (! $player) {
            return redirect()->route('playground.login');
        }

        $module->load(['missions.quizzes']);

        $missions = $module->missions->map(function ($mission) use ($player) {
            $totalQuestions = $mission->quizzes->sum(fn ($q) => count($q->questions ?? []));
            $totalQuizzes = $mission->quizzes->count();

            $completedQuizzes = $mission->quizzes->filter(fn ($q) => Quiz_attempts::where('quiz_id', $q->id)
                ->where('student_id', $player['id'] ?? null)
                ->exists()
            )->count();

            $status = 'not_started';
            if ($completedQuizzes > 0) {
                $status = $completedQuizzes >= $totalQuizzes ? 'completed' : 'in_progress';
            }

            $bestScore = Quiz_attempts::whereIn('quiz_id', $mission->quizzes->pluck('id'))
                ->where('student_id', $player['id'] ?? null)
                ->max('score') ?? 0;

            return [
                'id' => $mission->id,
                'name' => $mission->name,
                'description' => $mission->hint ?? '',
                'status' => $status,
                'total_questions' => $totalQuestions,
                'completed_quizzes' => $completedQuizzes,
                'total_quizzes' => $totalQuizzes,
                'best_score' => $bestScore,
            ];
        });

        $allMissionsDone = $missions->isNotEmpty() && $missions->every(fn ($m) => $m['status'] === 'completed');

        return Inertia::render('Playground/Missions/Index', [
            'module'            => ['id' => $module->id, 'name' => $module->name, 'description' => $module->description],
            'missions'          => $missions,
            'user'              => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'all_missions_done' => $allMissionsDone,
        ]);
    }

    public function showMission(Request $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        $mission->load([
            'module',
            'materials'                    => fn ($q) => $q->orderBy('created_at', 'asc'),
            'materials.mascot',
            'quizzes'                      => fn ($q) => $q->orderBy('created_at', 'asc'),
            'quizzes.questions.mascot',
            'quizzes.questions.options',
            'quizzes.questions.dragDropGroups.items',
        ]);

        // Format quizzes
        $quizzes = $mission->quizzes->map(fn ($quiz) => [
            'id'         => $quiz->id,
            'type'       => $quiz->type,
            'title'      => $quiz->title,
            'time_limit' => $quiz->time_limit,
            'created_at' => $quiz->created_at,
            'questions'  => $quiz->questions->map(function ($question) {
                $formatted = [
                    'id'            => $question->id,
                    'question_text' => $question->question_text,
                    'quiz_id'       => $question->quiz_id,
                    'mascot'        => $question->mascot ? [
                        'id'        => $question->mascot->id,
                        'name_pose' => $question->mascot->name_pose,
                        'image'     => $question->mascot->image,
                    ] : null,
                ];

                if ($question->options->count() > 0) {
                    $formatted['options'] = $question->options->map(fn ($opt) => [
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
        ])->toArray();

        // Format materials as quiz items with type 'materials'
        $materials = $mission->materials->map(fn ($material) => [
            'id'         => $material->id,
            'type'       => 'materials',
            'image'      => $material->image,
            'title'      => $material->title,
            'subtitle'   => $material->description,
            'created_at' => $material->created_at,
            'mascot'     => $material->mascot ? [
                'id'        => $material->mascot->id,
                'name_pose' => $material->mascot->name_pose,
                'image'     => $material->mascot->image,
            ] : null,
            'questions'  => [
                [
                    'id'            => $material->id,
                    'image'         => $material->image,
                    'title'         => $material->title,
                    'subtitle'      => $material->description,
                    'content'       => $material->content,
                    'material_type' => 'text',
                ],
            ],
        ])->toArray();

        // Merge materials and quizzes, sorted by created_at ascending (oldest first)
        $allItems = collect(array_merge($quizzes, $materials))
            ->sortBy(fn ($item) => $item['created_at'])
            ->values()
            ->toArray();

        $formattedMission = [
            'id'      => $mission->id,
            'name'    => $mission->name,
            'quizzes' => $allItems,
        ];

        return Inertia::render('Playground/Mission/Template', [
            'mission' => $formattedMission,
            'user'   => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'module' => ['id' => $mission->module_id, 'name' => $mission->module?->name ?? 'Module', 'description' => $mission->module?->description ?? ''],
        ]);
    }

    /**
     * Dedicated result page — reads saved answers and builds full review data.
     */
    public function showResult(Request $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) {
            return redirect()->route('playground.login');
        }

        $mission->load([
            'quizzes.questions.options',
            'quizzes.questions.dragDropGroups.items',
        ]);

        $studentId = $player['id'] ?? null;
        $totalCorrect = 0;
        $totalIncorrect = 0;
        $totalQuestions = 0;
        $byType = [];
        $questionsResult = [];

        foreach ($mission->quizzes as $quiz) {
            if ($quiz->type === 'materials') {
                continue;
            }

            $attempt = Quiz_attempts::where('quiz_id', $quiz->id)
                ->where('student_id', $studentId)
                ->latest()
                ->first();

            if (! $attempt) {
                continue;
            }

            $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');

            foreach ($quiz->questions as $question) {
                $answer = $answersByQuestion->get($question->id);
                if (! $answer) {
                    continue;
                }

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
                    'question_id'        => $question->id,
                    'question_text'      => $question->question_text,
                    'quiz_type'          => $quiz->type,
                    'quiz_title'         => $quiz->title,
                    'is_correct'         => $isCorrect,
                    'user_answer_text'   => $userAnswerText,
                    'correct_answer_text'=> $correctAnswerText,
                    'user_answer_map'    => $userAnswerMap,
                    'correct_answer_map' => $correctAnswerMap,
                ];
            }
        }

        $score = $totalQuestions > 0
            ? (int) round(($totalCorrect / $totalQuestions) * 100)
            : 0;

        $breakdown = collect($byType)->map(fn ($d, $type) => [
            'type'      => $type,
            'correct'   => $d['correct'],
            'incorrect' => $d['incorrect'],
            'total'     => $d['total'],
            'score'     => $d['total'] > 0 ? (int) round(($d['correct'] / $d['total']) * 100) : 0,
        ])->values()->toArray();

        $moduleId = $mission->module_id;
        $module   = $mission->module;

        // Cek apakah semua misi di modul ini sudah selesai
        $allMissionIds = Missions::where('module_id', $moduleId)->pluck('id');
        $allMissionsDone = $allMissionIds->isNotEmpty() && $allMissionIds->every(function ($missionId) use ($studentId) {
            $missionQuizIds = Quizzes::where('mission_id', $missionId)
                ->where('category', 'mission')
                ->pluck('id');
            if ($missionQuizIds->isEmpty()) return false;
            return $missionQuizIds->every(fn ($qid) => Quiz_attempts::where('quiz_id', $qid)
                ->where('student_id', $studentId)->exists()
            );
        });

        return Inertia::render('Playground/Mission/Result', [
            'mission'           => ['id' => $mission->id, 'name' => $mission->name],
            'results'           => [
                'score'            => $score,
                'correct'          => $totalCorrect,
                'incorrect'        => $totalIncorrect,
                'total'            => $totalQuestions,
                'breakdown'        => $breakdown,
                'questions_result' => $questionsResult,
            ],
            'user'              => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'module'            => ['id' => $moduleId, 'name' => $module?->name ?? 'Modul'],
            'all_missions_done' => $allMissionsDone,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  SUBMIT
    // ─────────────────────────────────────────────────────────────

    public function submitMissionAnswers(SubmitMissionAnswersRequest $request, Missions $mission)
    {
        $player = session('player');
        if (! $player) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validated();
        $answers   = $validated['answers'];
        $quizIds   = $validated['quiz_ids'];
        $studentId = $player['id'] ?? null;

        try {
            foreach ($quizIds as $quizId) {
                $quizQuestionIds = Questions::where('quiz_id', $quizId)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->toArray();

                $attempt = Quiz_attempts::updateOrCreate(
                    ['quiz_id' => $quizId, 'student_id' => $studentId],
                    ['started_at' => now(), 'finished_at' => now()]
                );

                foreach ($answers as $questionId => $answerValue) {
                    if (! in_array((string) $questionId, $quizQuestionIds)) {
                        continue;
                    }

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

                $scores = $this->calcQuizScoreWithTypes($quizId, $studentId);
                \Log::info('Quiz Score Calculation', [
                    'quiz_id'    => $quizId,
                    'student_id' => $studentId,
                    'scores'     => $scores,
                ]);
                $attempt->update([
                    'score'                  => $scores['overall'],
                    'score_multiple_choice'  => $scores['multiple_choices'],
                    'score_true_false'       => $scores['true_false'],
                    'score_case_study'       => $scores['case_study'],
                    'score_drag_drop'        => $scores['drag_drop'],
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function calcQuizScoreWithTypes(string $quizId, ?string $studentId): array
    {
        $attempt = Quiz_attempts::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->latest()->first();

        if (! $attempt) {
            return ['overall' => 0, 'multiple_choices' => 0, 'true_false' => 0, 'case_study' => 0, 'drag_drop' => 0];
        }

        $quiz = Quizzes::with(['questions.options', 'questions.dragDropGroups.items'])->find($quizId);
        if (! $quiz) {
            return ['overall' => 0, 'multiple_choices' => 0, 'true_false' => 0, 'case_study' => 0, 'drag_drop' => 0];
        }

        $answersByQuestion = $attempt->answers()->get()->keyBy('question_id');

        \Log::info('Quiz Score Debug', [
            'quiz_id'         => $quizId,
            'quiz_type'       => $quiz->type,
            'total_questions' => count($quiz->questions),
            'total_answers'   => $answersByQuestion->count(),
        ]);

        $scoresByType = [
            'multiple_choices' => ['correct' => 0, 'total' => 0],
            'true_false'       => ['correct' => 0, 'total' => 0],
            'case_study'       => ['correct' => 0, 'total' => 0],
            'drag_drop'        => ['correct' => 0, 'total' => 0],
        ];

        $totalCorrect   = 0;
        $totalQuestions = 0;

        foreach ($quiz->questions as $question) {
            $answer = $answersByQuestion->get($question->id);
            if (! $answer) {
                continue;
            }

            $quizType = $quiz->type;
            if (! isset($scoresByType[$quizType])) {
                continue;
            }

            $scoresByType[$quizType]['total']++;
            $totalQuestions++;

            [$isCorrect] = $this->checkAnswer($answer, $question);
            if ($isCorrect) {
                $scoresByType[$quizType]['correct']++;
                $totalCorrect++;
            }
        }

        $result = [];
        foreach ($scoresByType as $type => $data) {
            $result[$type] = $data['total'] > 0
                ? (int) round(($data['correct'] / $data['total']) * 100)
                : 0;
        }

        $result['overall'] = $totalQuestions > 0
            ? (int) round(($totalCorrect / $totalQuestions) * 100)
            : 0;

        return $result;
    }

    private function calcQuizScore(string $quizId, ?string $studentId): int
    {
        return $this->calcQuizScoreWithTypes($quizId, $studentId)['overall'];
    }

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
            $correctIds  = $correctOpts->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->toArray();
            $correctText = $correctOpts->pluck('option_text')->implode(', ');

            $responseStr = trim($answer->response ?? '');

            if (str_starts_with($responseStr, '[')) {
                $selectedIds = collect(json_decode($responseStr, true) ?? [])
                    ->map(fn ($id) => (string) $id)->sort()->values()->toArray();

                $userText = collect($selectedIds)
                    ->map(fn ($id) => $allOptions->get($id)?->option_text ?? $id)
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

        // ── Drag & drop ──────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────
    //  LEGACY
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('playground.login');
        }

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
