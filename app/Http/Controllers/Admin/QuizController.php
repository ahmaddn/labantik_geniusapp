<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Quizzes;
use App\Models\Questions;
use App\Models\Question_options;
use App\Models\Drag_drop_items;
use App\Models\Drag_drop_groups;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Show form to create quiz
     */
    public function create(Learning_modules $modules, Missions $missions)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        // Get template mascots if exists
        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Quizzes/Create', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
                'template' => $modules->template,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
                'order_number' => $missions->order_number,
            ],
            'mascots' => $mascots,
        ]);
    }

    /**
     * Store quiz with questions
     */
    public function store(Learning_modules $modules, Missions $missions, Request $request)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:multiple_choice,drag_drop',
            'time_limit' => 'required|integer|min:1',
            'category' => 'nullable|string|max:100',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.mascot_id' => 'nullable|exists:mascots,id',
            'questions.*.image' => 'nullable|string',

            // For multiple choice
            'questions.*.options' => 'nullable|array|min:2',
            'questions.*.options.*.option_text' => 'required_with:questions.*.options|string',
            'questions.*.options.*.is_correct' => 'required_with:questions.*.options|boolean',
            'questions.*.options.*.feedback' => 'nullable|string',

            // For drag drop
            'questions.*.drag_drop_groups' => 'nullable|array',
            'questions.*.drag_drop_groups.*.group_name' => 'required_with:questions.*.drag_drop_groups|string',
            'questions.*.drag_drop_items' => 'nullable|array',
            'questions.*.drag_drop_items.*.item_text' => 'required_with:questions.*.drag_drop_items|string',
            'questions.*.drag_drop_items.*.item_image' => 'nullable|string',
            'questions.*.drag_drop_items.*.group_index' => 'required_with:questions.*.drag_drop_items|integer',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
            'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            'questions.*.question_text.required' => 'Teks pertanyaan wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            // Create quiz
            $quiz = Quizzes::create([
                'mission_id' => $missions->id,
                'module_id' => $modules->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'time_limit' => $validated['time_limit'],
                'category' => $validated['category'],
                'created_by' => Auth::id(),
            ]);

            // Create questions
            foreach ($validated['questions'] as $index => $questionData) {
                $question = Questions::create([
                    'quiz_id' => $quiz->id,
                    'mascot_id' => $questionData['mascot_id'] ?? null,
                    'question_text' => $questionData['question_text'],
                    'image' => $questionData['image'] ?? null,
                    'order_number' => $index + 1,
                ]);

                // Handle multiple choice options
                if ($validated['type'] === 'multiple_choice' && isset($questionData['options'])) {
                    foreach ($questionData['options'] as $optionData) {
                        Question_options::create([
                            'question_id' => $question->id,
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'feedback' => $optionData['feedback'] ?? null,
                        ]);
                    }
                }

                // Handle drag drop
                if ($validated['type'] === 'drag_drop') {
                    // Create groups first
                    $groupMap = [];
                    if (isset($questionData['drag_drop_groups'])) {
                        foreach ($questionData['drag_drop_groups'] as $groupIndex => $groupData) {
                            $group = Drag_drop_groups::create([
                                'question_id' => $question->id,
                                'group_name' => $groupData['group_name'],
                            ]);
                            $groupMap[$groupIndex] = $group->id;
                        }
                    }

                    // Create items with group reference
                    if (isset($questionData['drag_drop_items'])) {
                        foreach ($questionData['drag_drop_items'] as $itemData) {
                            $groupId = $groupMap[$itemData['group_index']] ?? null;

                            Drag_drop_items::create([
                                'question_id' => $question->id,
                                'drag_drop_group_id' => $groupId,
                                'item_text' => $itemData['item_text'],
                                'item_image' => $itemData['item_image'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Quiz berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan quiz: ' . $e->getMessage());
        }
    }

    /**
     * Show quiz detail
     */
    public function show(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        // Pastikan quiz milik mission yang benar
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        // Load relations
        $quizzes->load([
            'questions' => function ($query) {
                $query->orderBy('order_number');
            },
            'questions.mascot',
            'questions.options',
            'questions.dragDropGroups',
            'questions.dragDropItems.dragDropGroups',
            'createdBy'
        ]);

        return Inertia::render('Admin/Modules/Missions/Quizzes/Show', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
            ],
            'quiz' => $quizzes,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        // Pastikan quiz milik mission yang benar
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $quizzes->load([
            'questions' => function ($query) {
                $query->orderBy('order_number');
            },
            'questions.mascot',
            'questions.options',
            'questions.dragDropGroups',
            'questions.dragDropItems.dragDropGroups',
        ]);

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Missions/Quizzes/Edit', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
                'template' => $modules->template,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
            ],
            'quiz' => $quizzes,
            'mascots' => $mascots,
        ]);
    }

    /**
     * Update quiz
     */
    public function update(Learning_modules $modules, Missions $missions, Quizzes $quizzes, Request $request)
    {
        // Pastikan quiz milik mission yang benar
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:multiple_choice,drag_drop',
            'time_limit' => 'required|integer|min:1',
            'category' => 'nullable|string|max:100',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.mascot_id' => 'nullable|exists:mascots,id',
            'questions.*.image' => 'nullable|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.option_text' => 'required_with:questions.*.options|string',
            'questions.*.options.*.is_correct' => 'required_with:questions.*.options|boolean',
            'questions.*.options.*.feedback' => 'nullable|string',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
            'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
        ]);

        DB::beginTransaction();
        try {
            // Update quiz
            $quizzes->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'time_limit' => $validated['time_limit'],
                'category' => $validated['category'],
            ]);

            // Delete old questions (cascade will delete options and drag drop items)
            $quizzes->questions()->delete();

            // Create new questions
            foreach ($validated['questions'] as $index => $questionData) {
                $question = Questions::create([
                    'quiz_id' => $quizzes->id,
                    'mascot_id' => $questionData['mascot_id'] ?? null,
                    'question_text' => $questionData['question_text'],
                    'image' => $questionData['image'] ?? null,
                    'order_number' => $index + 1,
                ]);

                // Create options for multiple choice
                if ($validated['type'] === 'multiple_choice' && isset($questionData['options'])) {
                    foreach ($questionData['options'] as $optionData) {
                        Question_options::create([
                            'question_id' => $question->id,
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'feedback' => $optionData['feedback'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Quiz berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui quiz: ' . $e->getMessage());
        }
    }

    /**
     * Delete quiz
     */
    public function destroy(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        // Pastikan quiz milik mission yang benar
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Delete quiz (cascade will delete questions, options, and drag drop items)
            $quizzes->delete();

            DB::commit();

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Quiz berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus quiz: ' . $e->getMessage());
        }
    }
}
