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

        $module->load([
            'missions'                  => fn($q) => $q->orderBy('order_number', 'asc'),
            'missions.quizzes',
            'missions.quizzes.questions',
            'missions.materials',
            'template.backgrounds',
        ]);

        $missions = $module->missions
            ->filter(function ($mission) {
                // Tampilkan misi hanya jika ada minimal 1 soal ATAU minimal 1 materi
                $hasQuestions = $mission->quizzes->sum(fn($q) => $q->questions->count()) > 0;
                $hasMaterials = $mission->materials->count() > 0;

                return $hasQuestions || $hasMaterials;
            })
            ->map(function ($mission) use ($player) {
                $totalQuestions = $mission->quizzes->sum(fn($q) => $q->questions->count());
                $totalQuizzes   = $mission->quizzes->count();

                $completedQuizzes = $mission->quizzes->filter(
                    fn($q) => Quiz_attempts::where('quiz_id', $q->id)
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
                    'id'                => $mission->id,
                    'name'              => $mission->name,
                    'description'       => $mission->hint ?? '',
                    'status'            => $status,
                    'total_questions'   => $totalQuestions,
                    'completed_quizzes' => $completedQuizzes,
                    'total_quizzes'     => $totalQuizzes,
                    'best_score'        => $bestScore,
                ];
            })
            ->values(); // reset index array

        $allMissionsDone = $missions->isNotEmpty() && $missions->every(fn($m) => $m['status'] === 'completed');

        // Jika posttest sudah pernah dikerjakan, sembunyikan tombol posttest
        // (supaya tidak muncul lagi saat admin tambah misi baru)
        $posttestQuiz = \App\Models\Quizzes::where('module_id', $module->id)
            ->where('category', 'posttest')
            ->first();
        $posttestDone = $posttestQuiz && Quiz_attempts::where('quiz_id', $posttestQuiz->id)
            ->where('student_id', $player['id'] ?? null)
            ->exists();

        // Kalau posttest sudah selesai, paksa all_missions_done = false
        // agar tombol "Mulai Posttest" tidak muncul lagi
        if ($posttestDone) {
            $allMissionsDone = false;
        }

        $backsound  = $module->template?->backsound
            ? asset('storage/' . $module->template->backsound)
            : null;
        $background = $module->template?->backgrounds->first()?->image
            ? asset('storage/' . $module->template->backgrounds->first()->image)
            : null;

        return Inertia::render('Playground/Missions/Index', [
            'module'            => ['id' => $module->id, 'name' => $module->name, 'description' => $module->description],
            'missions'          => $missions,
            'user'              => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'all_missions_done' => $allMissionsDone,
            'backsound'         => $backsound,
            'background'        => $background,
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
            'materials',
            'materials.mascot',
            'quizzes',
            'quizzes.questions.mascot',
            'quizzes.questions.options',
            'quizzes.questions.dragDropGroups.items',
            'module.template',
            'module.template.backgrounds',
            'simulation_clickable_objects',
            'simulation_sliders.levels',
            'simulation_comparisons',
            'simulation_decisions.options',
            'reflections.questions',
        ]);

        // Format quizzes
        $quizzes = $mission->quizzes->map(fn($quiz) => [
            'id'           => $quiz->id,
            'type'         => $quiz->type,
            'title'        => $quiz->title,
            'time_limit'   => $quiz->time_limit,
            'order_number' => $quiz->order_number ?? 0,
            'created_at'   => $quiz->created_at,
            'questions'    => $quiz->questions->map(function ($question) {
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
        ])->toArray();

        // Format materials
        $materials = $mission->materials->map(fn($material) => [
            'id'           => $material->id,
            'type'         => 'materials',
            'image'        => $material->image,
            'title'        => $material->title,
            'subtitle'     => $material->description,
            'order_number' => $material->order_number ?? 0,
            'created_at'   => $material->created_at,
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
                    'layout_type'   => $material->layout_type,
                    'youtube_link'  => $material->youtube_link,
                    'mascot'        => $material->mascot ? [
                        'id'        => $material->mascot->id,
                        'name_pose' => $material->mascot->name_pose,
                        'image'     => $material->mascot->image,
                    ] : null,
                ],
            ],
        ])->toArray();

        $backsound  = null;
        $background = null;
        if (! empty($mission->module?->template?->backsound)) {
            $backsound = asset('storage/' . $mission->module->template->backsound);
        }
        if (! empty($mission->module?->template?->backgrounds->first()?->image)) {
            $background = asset('storage/' . $mission->module->template->backgrounds->first()->image);
        }

        $clickables = [];
        if ($mission->simulation_clickable_objects->isNotEmpty()) {
            $first = $mission->simulation_clickable_objects->sortBy('order_number')->first();
            $clickables[] = [
                'id'           => 'sim_clickable_' . $mission->id,
                'type'         => 'simulation_clickable',
                'title'        => $first->title ?? 'Simulasi Objek Klik',
                'order_number' => $first->order_number ?? 0,
                'objects'      => $mission->simulation_clickable_objects->map(fn($obj) => [
                    'id'          => $obj->id,
                    'name'        => $obj->name,
                    'image'       => $obj->image,
                    'impact_text' => $obj->impact_text,
                    'is_positive' => $obj->is_positive,
                ])->toArray(),
            ];
        }

        $sliders = [];
        if ($mission->simulation_sliders->isNotEmpty()) {
            $firstSlider = $mission->simulation_sliders->sortBy('order_number')->first();
            $sliders[] = [
                'id'           => 'sim_slider_' . $mission->id,
                'type'         => 'simulation_slider',
                'title'        => $firstSlider->title ?? 'Simulasi Interaktif',
                'variables'    => $firstSlider->variables ?? [],
                'order_number' => $firstSlider->order_number ?? 0,
                'levels'       => $firstSlider->levels->map(fn($lvl) => [
                    'id'          => $lvl->id,
                    'level_name'       => $lvl->level_name,
                    'narration'        => $lvl->narration,
                    'metric_value'     => $lvl->metric_value,
                    'image'            => $lvl->image,
                    'animation_effect' => $lvl->animation_effect,
                    'image_transition' => $lvl->image_transition,
                    'status'           => $lvl->status,
                ])->toArray(),
            ];
        }

        $comparisons = [];
        if ($mission->simulation_comparisons->isNotEmpty()) {
            $firstComp = $mission->simulation_comparisons->sortBy('order_number')->first();
            $comparisons[] = [
                'id'           => 'sim_comparison_' . $mission->id,
                'type'         => 'simulation_comparison',
                'title'        => $firstComp->title ?? 'Simulasi Perbandingan',
                'order_number' => $firstComp->order_number ?? 0,
                'items'        => $mission->simulation_comparisons->sortBy('order_number')->map(fn($comp) => [
                    'id'          => $comp->id,
                    'explanation' => $comp->explanation,
                    'items'       => $comp->items ?? [],
                ])->toArray(),
            ];
        }

        $decisions = [];
        if ($mission->simulation_decisions->isNotEmpty()) {
            // Note: Since we didn't add order_number to decisions, we'll assume it defaults to 0
            $firstDec = $mission->simulation_decisions->first();
            $decisions[] = [
                'id'                  => 'sim_decision_' . $mission->id,
                'type'                => 'simulation_decision',
                'title'               => $firstDec->title ?? 'Simulasi Keputusan',
                'initial_state_title' => $firstDec->initial_state_title,
                'initial_state_image' => $firstDec->initial_state_image,
                'future_state_title'  => $firstDec->future_state_title,
                'character_image'     => $firstDec->character_image,
                'order_number'        => $firstDec->order_number ?? 0,
                'options'             => $firstDec->options->map(fn($opt) => [
                    'id'                 => $opt->id,
                    'button_label'       => $opt->button_label,
                    'button_color'       => $opt->button_color,
                    'feedback_message'   => $opt->feedback_message,
                    'future_state_image' => $opt->future_state_image,
                ])->toArray(),
            ];
        }

        $reflections = [];
        if ($mission->reflections->isNotEmpty()) {
            foreach ($mission->reflections as $reflection) {
                $reflections[] = [
                    'id'                => 'reflection_' . $reflection->id,
                    'type'              => 'reflection',
                    'title'             => $reflection->title ?? 'Refleksi Ilmiah',
                    'mascot_left_text'  => $reflection->mascot_left_text,
                    'mascot_right_text' => $reflection->mascot_right_text,
                    'flowchart_data'    => $reflection->flowchart_data,
                    'order_number'      => 999, // Karena tidak ada order_number di tabel, tempatkan di akhir
                    'questions'         => $reflection->questions->map(fn($q) => [
                        'id'            => $q->id,
                        'question_text' => $q->question_text,
                        'order_number'  => $q->order_number,
                    ])->toArray(),
                ];
            }
        }

        // Merge & sort by order_number
        $allItems = collect(array_merge($quizzes, $materials, $clickables, $sliders, $comparisons, $decisions, $reflections))
            ->sortBy('order_number')
            ->values()
            ->toArray();

        $formattedMission = [
            'id'                => $mission->id,
            'name'              => $mission->name,
            'conclusion_speech' => $mission->conclusion_speech,
            'conclusion_body'   => $mission->conclusion_body,
            'quizzes'           => $allItems,
        ];

        return Inertia::render('Playground/Mission/Template', [
            'mission'    => $formattedMission,
            'user'       => ['name' => $player['nama'] ?? 'Siswa', 'class' => $player['nama_kelas'] ?? '-'],
            'module'     => ['id' => $mission->module_id, 'name' => $mission->module?->name ?? 'Module', 'description' => $mission->module?->description ?? ''],
            'backsound'  => $backsound,
            'background' => $background,
        ]);
    }

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

        $studentId      = $player['id'] ?? null;
        $totalCorrect   = 0;
        $totalIncorrect = 0;
        $totalQuestions = 0;
        $byType         = [];
        $questionsResult = [];

        $nextMission = Missions::where('module_id', $mission->module_id)
            ->where('order_number', '>', $mission->order_number)
            ->orderBy('order_number', 'asc')
            ->first();

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

                // Tidak dijawab (timer habis / skip) → skip, tidak masuk hitungan
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

        $moduleId = $mission->module_id;
        $module   = $mission->module;

        $allMissionIds   = Missions::where('module_id', $moduleId)->pluck('id');
        $allMissionsDone = $allMissionIds->isNotEmpty() && $allMissionIds->every(function ($missionId) use ($studentId) {
            $missionQuizIds = Quizzes::where('mission_id', $missionId)
                ->where('category', 'mission')
                ->pluck('id');
            if ($missionQuizIds->isEmpty()) return false;
            return $missionQuizIds->every(
                fn($qid) => Quiz_attempts::where('quiz_id', $qid)
                    ->where('student_id', $studentId)->exists()
            );
        });

        $posttestQuiz = Quizzes::where('module_id', $moduleId)
            ->where('category', 'posttest')
            ->first();
        $posttestDone = $posttestQuiz && Quiz_attempts::where('quiz_id', $posttestQuiz->id)
            ->where('student_id', $studentId)
            ->exists();

        return Inertia::render('Playground/Mission/Result', [
            'mission'           => ['id' => $mission->id, 'name' => $mission->name],
            'next_mission'      => $nextMission ? ['id' => $nextMission->id, 'name' => $nextMission->name] : null,
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
            'posttest_done'     => $posttestDone,
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
        $answers   = $validated['answers'] ?? [];
        $quizIds   = $validated['quiz_ids'] ?? [];
        $studentId = $player['id'] ?? null;

        try {
            // 1. Save all reflections first
            foreach ($answers as $questionId => $answerValue) {
                if (\App\Models\Reflection_questions::where('id', $questionId)->exists()) {
                    \App\Models\Reflection_answers::updateOrCreate(
                        [
                            'user_id' => $studentId,
                            'reflection_question_id' => $questionId,
                        ],
                        [
                            'answer_text' => is_array($answerValue) ? json_encode($answerValue) : (string) $answerValue,
                            'score' => 0,
                        ]
                    );
                }
            }

            // 2. Save quiz answers
            foreach ($quizIds as $quizId) {
                if (str_starts_with($quizId, 'reflection_')) {
                    continue; // Skip pseudo-quizzes
                }

                $quizQuestionIds = Questions::where('quiz_id', $quizId)
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
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
                        $isUuid = \Illuminate\Support\Str::isUuid((string) $answerValue);
                        User_answers::updateOrCreate(
                            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                            [
                                'selected_option_id' => $isUuid ? $answerValue : null, 
                                'selected_group_id' => null, 
                                'response' => (string) $answerValue
                            ]
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
                    'score'                 => $scores['overall'],
                    'score_multiple_choice' => $scores['multiple_choices'],
                    'score_true_false'      => $scores['true_false'],
                    'score_case_study'      => $scores['case_study'],
                    'score_drag_drop'       => $scores['drag_drop'],
                ]);
            }

            // Misi dianggap selesai ketika submitMissionAnswers dipanggil
            if ($studentId) {
                // Get current attempt number
                $lastAttempt = \App\Models\StudentMissionLog::where('user_id', $studentId)
                    ->where('mission_id', $mission->id)
                    ->orderBy('attempt_number', 'desc')
                    ->first();
                
                $attemptNum = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

                \App\Models\StudentMissionLog::create([
                    'user_id' => $studentId,
                    'mission_id' => $mission->id,
                    'module_id' => $mission->module_id,
                    'attempt_number' => $attemptNum,
                    'completed_at' => now(),
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
            // Skip soal yang tidak dijawab (timer habis)
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
