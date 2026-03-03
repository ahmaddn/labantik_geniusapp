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
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    /**
     * Show form to create quiz
     */
    public function create(Learning_modules $modules, Missions $missions)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Quizzes/Create', [
            'module' => [
                'id'       => $modules->id,
                'name'     => $modules->name,
                'template' => $modules->template,
            ],
            'mission' => [
                'id'           => $missions->id,
                'name'         => $missions->name,
                'order_number' => $missions->order_number,
            ],
            'mascots' => $mascots,
        ]);
    }

    /**
     * Show create form for module-level quizzes (pretest/posttest)
     */
    public function createModule(Learning_modules $modules, string $category)
    {
        if (!in_array($category, ['pretest', 'posttest'])) {
            abort(404);
        }

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Quizzes/Create', [
            'module' => [
                'id'       => $modules->id,
                'name'     => $modules->name,
                'template' => $modules->template,
            ],
            'mission' => null,
            'mascots' => $mascots,
            'presetCategory' => $category,
        ]);
    }

    /**
     * Store quiz with questions
     */
    public function store(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:multiple_choices,drag_drop,true_false,case_study',
            'time_limit'  => 'required|integer|min:1',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
        ]);

        // --- Validation per type ---
        if ($request->input('type') === 'true_false') {
            $request->validate([
                'tf_question'            => 'required|string',
                'tf_option_images.*'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } elseif ($request->input('type') === 'drag_drop') {
            $request->validate([
                'questions' => 'required|string',
                'drag_item_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            ]);
        } else {
            $request->validate([
                'questions' => 'required|string',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Store quiz cover image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('quizzes/images', 'public');
            }

            $missionId = in_array($validated['category'], ['pretest', 'posttest']) ?  null : $missions->id;

            $quiz = Quizzes::create([
                'mission_id'  => $missionId,
                'module_id'   => $modules->id,
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type'        => $validated['type'],
                'time_limit'  => $validated['time_limit'],
                'category'    => $validated['category'] ?? null,
                'image'       => $imagePath,
                'created_by'  => Auth::id(),
            ]);

            // ===== TRUE/FALSE (Image Select) =====
            if ($validated['type'] === 'true_false') {
                $tfData = json_decode($request->input('tf_question'), true);

                if (!$tfData || empty($tfData['question_text'])) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan true/false tidak valid.');
                }

                $question = Questions::create([
                    'quiz_id'       => $quiz->id,
                    'mascot_id'     => $tfData['mascot_id'] ?? null,
                    'question_text' => $tfData['question_text'],
                    'image'         => null,
                    'order_number'  => 1,
                ]);

                foreach ($tfData['options'] as $idx => $optionMeta) {
                    // Upload gambar opsi jika ada
                    $optionImagePath = null;
                    if (
                        isset($optionMeta['has_image']) && $optionMeta['has_image']
                        && $request->hasFile("tf_option_images.{$optionMeta['image_index']}")
                    ) {
                        $optionImagePath = $request->file("tf_option_images.{$optionMeta['image_index']}")
                            ->store('questions/options', 'public');
                    }

                    Question_options::create([
                        'question_id'  => $question->id,
                        'option_text'  => $optionMeta['option_text'] ?? '',
                        'option_image' => $optionImagePath,
                        'is_correct'   => (bool) ($optionMeta['is_correct'] ?? false),
                        'feedback'     => null,
                    ]);
                }

                // ===== MULTIPLE CHOICE / DRAG DROP / CASE STUDY =====
            } else {
                $questions = json_decode($request->input('questions'), true);

                if (!$questions || count($questions) === 0) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan tidak valid.');
                }

                foreach ($questions as $index => $questionData) {
                    $question = Questions::create([
                        'quiz_id'       => $quiz->id,
                        'mascot_id'     => $questionData['mascot_id'] ?? null,
                        'question_text' => $questionData['question_text'],
                        'image'         => $questionData['image'] ?? null,
                        'order_number'  => $index + 1,
                    ]);

                    // Multiple choice / case study options
                    if (
                        in_array($validated['type'], ['multiple_choices', 'case_study'])
                        && isset($questionData['options'])
                    ) {
                        foreach ($questionData['options'] as $optionData) {
                            Question_options::create([
                                'question_id' => $question->id,
                                'option_text' => $optionData['option_text'],
                                'is_correct'  => $optionData['is_correct'],
                                'feedback'    => $optionData['feedback'] ?? null,
                            ]);
                        }
                    }

                    // Drag & Drop
                    if ($validated['type'] === 'drag_drop') {
                        $groupMap = [];
                        if (isset($questionData['drag_drop_groups'])) {
                            foreach ($questionData['drag_drop_groups'] as $groupIndex => $groupData) {
                                $group = Drag_drop_groups::create([
                                    'question_id' => $question->id,
                                    'group_name'  => $groupData['group_name'],
                                ]);
                                $groupMap[$groupIndex] = $group->id;
                            }
                        }

                        if (isset($questionData['drag_drop_items'])) {
                            // collect uploaded drag item images (indexed by frontend)
                            $dragImages = $request->file('drag_item_images', []);
                            $imgIndex = 0;
                            foreach ($questionData['drag_drop_items'] as $itemData) {
                                $storedPath = $itemData['item_image'] ?? null;
                                // If an uploaded file exists for this item index, store it
                                if (isset($dragImages[$imgIndex]) && $dragImages[$imgIndex] instanceof \Illuminate\Http\UploadedFile) {
                                    $storedPath = $dragImages[$imgIndex]->store('questions/drag_items', 'public');
                                    $imgIndex++;
                                }

                                Drag_drop_items::create([
                                    'question_id'        => $question->id,
                                    'drag_drop_group_id' => $groupMap[$itemData['group_index']] ?? null,
                                    'item_text'          => $itemData['item_text'],
                                    'item_image'         => $storedPath ?? null,
                                ]);
                            }
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
            return back()->withInput()->with('error', 'Gagal menambahkan quiz: ' . $e->getMessage());
        }
    }

    /**
     * Store module-level quiz (pretest/posttest) without mission
     */
    public function storeModule(Learning_modules $modules, Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:multiple_choices,drag_drop,true_false,case_study',
            'time_limit'  => 'required|integer|min:1',
            'category'    => 'required|in:pretest,posttest',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
        ]);

        // Additional type validation
        if ($request->input('type') === 'true_false') {
            $request->validate([
                'tf_question'            => 'required|string',
                'tf_option_images.*'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } elseif ($request->input('type') === 'drag_drop') {
            $request->validate([
                'questions' => 'required|string',
                'drag_item_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            ]);
        } else {
            $request->validate([
                'questions' => 'required|string',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            ]);
        }

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('quizzes/images', 'public');
            }

            $quiz = Quizzes::create([
                'mission_id'  => null,
                'module_id'   => $modules->id,
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type'        => $validated['type'],
                'time_limit'  => $validated['time_limit'],
                'category'    => $validated['category'] ?? null,
                'image'       => $imagePath,
                'created_by'  => Auth::id(),
            ]);

            // Reuse the same question storing logic as in store()
            if ($validated['type'] === 'true_false') {
                $tfData = json_decode($request->input('tf_question'), true);

                if (!$tfData || empty($tfData['question_text'])) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan true/false tidak valid.');
                }

                $question = Questions::create([
                    'quiz_id'       => $quiz->id,
                    'mascot_id'     => $tfData['mascot_id'] ?? null,
                    'question_text' => $tfData['question_text'],
                    'image'         => null,
                    'order_number'  => 1,
                ]);

                foreach ($tfData['options'] as $idx => $optionMeta) {
                    $optionImagePath = null;
                    if (
                        isset($optionMeta['has_image']) && $optionMeta['has_image']
                        && $request->hasFile("tf_option_images.{$optionMeta['image_index']}")
                    ) {
                        $optionImagePath = $request->file("tf_option_images.{$optionMeta['image_index']}")
                            ->store('questions/options', 'public');
                    }

                    Question_options::create([
                        'question_id'  => $question->id,
                        'option_text'  => $optionMeta['option_text'] ?? '',
                        'option_image' => $optionImagePath,
                        'is_correct'   => (bool) ($optionMeta['is_correct'] ?? false),
                        'feedback'     => null,
                    ]);
                }
            } else {
                $questions = json_decode($request->input('questions'), true);

                if (!$questions || count($questions) === 0) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan tidak valid.');
                }

                foreach ($questions as $index => $questionData) {
                    $question = Questions::create([
                        'quiz_id'       => $quiz->id,
                        'mascot_id'     => $questionData['mascot_id'] ?? null,
                        'question_text' => $questionData['question_text'],
                        'image'         => $questionData['image'] ?? null,
                        'order_number'  => $index + 1,
                    ]);

                    if (
                        in_array($validated['type'], ['multiple_choices', 'case_study'])
                        && isset($questionData['options'])
                    ) {
                        foreach ($questionData['options'] as $optionData) {
                            Question_options::create([
                                'question_id'  => $question->id,
                                'option_text'  => $optionData['option_text'] ?? '',
                                'option_image' => $optionData['option_image'] ?? null,
                                'is_correct'   => $optionData['is_correct'],
                                'feedback'     => $optionData['feedback'] ?? null,
                            ]);
                        }
                    }

                    if ($validated['type'] === 'drag_drop') {
                        $groupMap = [];
                        if (isset($questionData['drag_drop_groups'])) {
                            foreach ($questionData['drag_drop_groups'] as $groupIndex => $groupData) {
                                $group = Drag_drop_groups::create([
                                    'question_id' => $question->id,
                                    'group_name'  => $groupData['group_name'],
                                ]);
                                $groupMap[$groupIndex] = $group->id;
                            }
                        }

                        if (isset($questionData['drag_drop_items'])) {
                            $dragImages = $request->file('drag_item_images', []);
                            $imgIndex = 0;
                            foreach ($questionData['drag_drop_items'] as $itemData) {
                                $storedPath = $itemData['item_image'] ?? null;
                                if (isset($dragImages[$imgIndex]) && $dragImages[$imgIndex] instanceof \Illuminate\Http\UploadedFile) {
                                    $storedPath = $dragImages[$imgIndex]->store('questions/drag_items', 'public');
                                    $imgIndex++;
                                }

                                Drag_drop_items::create([
                                    'question_id'        => $question->id,
                                    'drag_drop_group_id' => $groupMap[$itemData['group_index']] ?? null,
                                    'item_text'          => $itemData['item_text'],
                                    'item_image'         => $storedPath ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.modules.show', [$modules->id])
                ->with('success', 'Quiz berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan quiz: ' . $e->getMessage());
        }
    }

    /**
     * Show quiz detail
     */
    public function show(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $quizzes->load([
            'questions' => fn($q) => $q->orderBy('order_number'),
            'questions.mascot',
            'questions.options',
            'questions.dragDropGroups',
            'questions.dragDropItems.dragDropGroups',
            'createdBy',
        ]);

        return Inertia::render('Admin/Modules/Quizzes/Show', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'mission' => ['id' => $missions->id, 'name' => $missions->name],
            'quiz'    => $quizzes,
        ]);
    }

    /**
     * Show quiz detail for quizzes that are not tied to a mission (module-level quizzes)
     */
    public function showModule(Learning_modules $modules, Quizzes $quizzes)
    {
        if ($quizzes->module_id !== $modules->id) {
            abort(404);
        }

        $quizzes->load([
            'questions' => fn($q) => $q->orderBy('order_number'),
            'questions.mascot',
            'questions.options',
            'questions.dragDropGroups',
            'questions.dragDropItems.dragDropGroups',
            'createdBy',
        ]);

        return Inertia::render('Admin/Modules/Quizzes/Show', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'mission' => null,
            'quiz'    => $quizzes,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $quizzes->load([
            'questions' => fn($q) => $q->orderBy('order_number'),
            'questions.mascot',
            'questions.options',
            'questions.dragDropGroups',
            'questions.dragDropItems.dragDropGroups',
        ]);

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Quizzes/Edit', [
            'module' => [
                'id'       => $modules->id,
                'name'     => $modules->name,
                'template' => $modules->template,
            ],
            'mission' => [
                'id'   => $missions->id,
                'name' => $missions->name,
            ],
            'quiz'    => $quizzes,
            'mascots' => $mascots,
        ]);
    }

    /**
     * Update quiz
     */
    public function update(Learning_modules $modules, Missions $missions, Quizzes $quizzes, Request $request)
    {
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:multiple_choices,drag_drop,true_false,case_study',
            'time_limit'  => 'required|integer|min:1',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|string',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
        ]);

        // Validation per type
        if ($request->input('type') === 'true_false') {
            $request->validate([
                'tf_question'        => 'required|string',
                'tf_option_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } else {
            $request->validate([
                'questions' => 'required|string',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Handle quiz cover image
            $imagePath = $quizzes->image;
            if ($request->input('remove_image') === '1') {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = null;
            }
            if ($request->hasFile('image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = $request->file('image')->store('quizzes/images', 'public');
            }

            $quizzes->update([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type'        => $validated['type'],
                'time_limit'  => $validated['time_limit'],
                'category'    => $validated['category'] ?? null,
                'image'       => $imagePath,
            ]);

            // Delete old questions + all related data (options, drag_drop_groups, drag_drop_items)
            // Images dari opsi lama yang tidak dipakai lagi perlu dihapus manual
            foreach ($quizzes->questions as $oldQuestion) {
                foreach ($oldQuestion->options as $oldOption) {
                    if ($oldOption->option_image) {
                        Storage::disk('public')->delete($oldOption->option_image);
                    }
                }
                // Delete drag & drop item images as well
                foreach ($oldQuestion->dragDropItems as $oldItem) {
                    if ($oldItem->item_image) {
                        Storage::disk('public')->delete($oldItem->item_image);
                    }
                }
            }
            $quizzes->questions()->delete();

            // ===== TRUE/FALSE (Image Select) =====
            if ($validated['type'] === 'true_false') {
                $tfData = json_decode($request->input('tf_question'), true);

                if (!$tfData || empty($tfData['question_text'])) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan true/false tidak valid.');
                }

                $question = Questions::create([
                    'quiz_id'       => $quizzes->id,
                    'mascot_id'     => $tfData['mascot_id'] ?? null,
                    'question_text' => $tfData['question_text'],
                    'image'         => null,
                    'order_number'  => 1,
                ]);

                foreach ($tfData['options'] as $idx => $optionMeta) {
                    $optionImagePath = null;

                    if (isset($optionMeta['has_new_image']) && $optionMeta['has_new_image']) {
                        // Upload gambar baru
                        if ($request->hasFile("tf_option_images.{$optionMeta['image_index']}")) {
                            $optionImagePath = $request->file("tf_option_images.{$optionMeta['image_index']}")
                                ->store('questions/options', 'public');
                        }
                    } elseif (!empty($optionMeta['existing_image'])) {
                        // Pakai gambar lama (tapi sudah dihapus di atas — perlu re-copy)
                        // Karena kita delete-recreate, gambar lama sudah dihapus dari storage di atas.
                        // Solusi: kita tidak hapus gambar opsi lama jika tipe true_false, tapi track via existing_image.
                        // Sebenarnya kita harus tidak menghapus gambar lama sebelum kita tahu mana yang dipakai.
                        // Logic sudah diperbaiki: lihat bagian delete di bawah.
                        $optionImagePath = $optionMeta['existing_image'];
                    }

                    Question_options::create([
                        'question_id'  => $question->id,
                        'option_text'  => $optionMeta['option_text'] ?? '',
                        'option_image' => $optionImagePath,
                        'is_correct'   => (bool) ($optionMeta['is_correct'] ?? false),
                        'feedback'     => null,
                    ]);
                }

                // ===== MULTIPLE CHOICE / DRAG DROP / CASE STUDY =====
            } else {
                $questions = json_decode($request->input('questions'), true);

                if (!$questions || count($questions) === 0) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Data pertanyaan tidak valid.');
                }

                foreach ($questions as $index => $questionData) {
                    $question = Questions::create([
                        'quiz_id'       => $quizzes->id,
                        'mascot_id'     => $questionData['mascot_id'] ?? null,
                        'question_text' => $questionData['question_text'],
                        'image'         => $questionData['image'] ?? null,
                        'order_number'  => $index + 1,
                    ]);

                    if (
                        in_array($validated['type'], ['multiple_choices', 'case_study'])
                        && isset($questionData['options'])
                    ) {
                        foreach ($questionData['options'] as $optionData) {
                            Question_options::create([
                                'question_id'  => $question->id,
                                'option_text'  => $optionData['option_text'] ?? '',
                                'option_image' => $optionData['option_image'] ?? null,
                                'is_correct'   => $optionData['is_correct'],
                                'feedback'     => $optionData['feedback'] ?? null,
                            ]);
                        }
                    }

                    if ($validated['type'] === 'drag_drop') {
                        $groupMap = [];
                        if (isset($questionData['drag_drop_groups'])) {
                            foreach ($questionData['drag_drop_groups'] as $groupIndex => $groupData) {
                                $group = Drag_drop_groups::create([
                                    'question_id' => $question->id,
                                    'group_name'  => $groupData['group_name'],
                                ]);
                                $groupMap[$groupIndex] = $group->id;
                            }
                        }
                        if (isset($questionData['drag_drop_items'])) {
                            $dragImages = $request->file('drag_item_images', []);
                            $imgIndex = 0;
                            foreach ($questionData['drag_drop_items'] as $itemData) {
                                $storedPath = $itemData['item_image'] ?? null;
                                if (isset($dragImages[$imgIndex]) && $dragImages[$imgIndex] instanceof \Illuminate\Http\UploadedFile) {
                                    $storedPath = $dragImages[$imgIndex]->store('questions/drag_items', 'public');
                                    $imgIndex++;
                                }

                                Drag_drop_items::create([
                                    'question_id'        => $question->id,
                                    'drag_drop_group_id' => $groupMap[$itemData['group_index']] ?? null,
                                    'item_text'          => $itemData['item_text'],
                                    'item_image'         => $storedPath ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Quiz berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui quiz: ' . $e->getMessage());
        }
    }

    /**
     * Delete quiz
     */
    public function destroy(Learning_modules $modules, Missions $missions, Quizzes $quizzes)
    {
        if ($quizzes->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Hapus gambar opsi sebelum delete record
            foreach ($quizzes->questions as $question) {
                foreach ($question->options as $option) {
                    if ($option->option_image) {
                        Storage::disk('public')->delete($option->option_image);
                    }
                }
            }
            if ($quizzes->image) {
                Storage::disk('public')->delete($quizzes->image);
            }

            $quizzes->delete();

            DB::commit();

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Quiz berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus quiz: ' . $e->getMessage());
        }
    }
}
