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
        $module->load('template','template.backgrounds');
        $background = $module->template?->backgrounds->first()?->image
    ? asset('storage/' . $module->template->backgrounds->first()->image)
    : null;
$backsound = null;
if (!empty($module->template?->backsound)) {
    $backsound = asset('storage/' . $module->template->backsound);
}

        // Kalau tidak ada posttest → kembali ke beranda
        if (! $quiz) {
            return redirect()->route('playground.index');
        }

        // Format sama persis dengan MissionController
        $formattedQuiz = [
            'id'         => $quiz->id,
            'type'       => $quiz->type,
            'title'      => $quiz->title,
            'time_limit' => $quiz->time_limit,
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
        ];

        return Inertia::render('Playground/Mission/TemplatePosttest', [
            'quiz'   => $formattedQuiz,
            'module' => [
                'id'          => $module->id,
                'name'        => $module->name,
                'description' => $module->description,
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
            ->map(fn ($id) => (string) $id)
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

        // Setelah posttest → kembali ke beranda
        return redirect()->route('playground.index');
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

        // Options-based (multiple_choices, true_false, case_study)
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
