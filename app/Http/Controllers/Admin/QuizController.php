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
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    /**
     * Safely combine header and row values even when counts differ.
     * Pads missing values with empty strings or trims extra values.
     *
     * @param array $header
     * @param array $row
     * @return array
     */
    protected function safeCombine(array $header, array $row): array
    {
        $h = array_values($header);
        $r = array_values($row);
        $hCount = count($h);
        $rCount = count($r);

        if ($rCount < $hCount) {
            $r = array_merge($r, array_fill(0, $hCount - $rCount, ''));
        } elseif ($rCount > $hCount) {
            $r = array_slice($r, 0, $hCount);
        }

        $combined = @array_combine($h, $r);
        return $combined === false ? [] : $combined;
    }

    /**
     * Parse CSV/XLSX file and return array of rows keyed by header.
     * Returns ['error' => string] on failure, or ['rows' => array] on success.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    protected function parseImportFile($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            // Buka dengan mode binary untuk hindari masalah encoding
            $handle = fopen($file->getRealPath(), 'rb');
            if ($handle === false) {
                return ['error' => 'Gagal membaca file.'];
            }

            // Strip UTF-8 BOM jika ada
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = null;
            $rowIndex = 0;
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                // Skip baris kosong
                if (count($row) === 1 && trim($row[0]) === '') {
                    continue;
                }
                if ($header === null) {
                    $header = array_map(fn($h) => trim(strtolower(str_replace(["\xEF\xBB\xBF", "\r", "\n"], '', $h))), $row);
                    continue;
                }
                $combined = $this->safeCombine($header, $row);
                if (!empty($combined)) {
                    $rows[] = $combined;
                }
                $rowIndex++;
            }
            fclose($handle);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                return ['error' => 'Dukungan XLSX tidak ditemukan. Jalankan: composer require phpoffice/phpspreadsheet'];
            }
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);
                $header = null;
                foreach ($data as $r) {
                    $values = array_values($r);
                    // Skip baris kosong
                    if (empty(array_filter($values, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                        continue;
                    }
                    if ($header === null) {
                        $header = array_map(fn($h) => trim(strtolower((string)$h)), $values);
                        continue;
                    }
                    $combined = $this->safeCombine($header, $values);
                    if (!empty($combined)) {
                        $rows[] = $combined;
                    }
                }
            } catch (\Exception $e) {
                return ['error' => 'Gagal membaca file XLSX: ' . $e->getMessage()];
            }
        } else {
            return ['error' => 'Silakan unggah file CSV atau XLSX.'];
        }

        if (empty($rows)) {
            return ['error' => 'File CSV/XLSX kosong atau tidak memiliki data selain header.'];
        }

        return ['rows' => $rows];
    }

    /**
     * Validate and build quiz groups from parsed rows.
     * Returns ['error' => string] or ['groups' => array].
     *
     * @param array $rows
     * @return array
     */
    protected function buildQuizGroups(array $rows): array
    {
        $groups = [];
        foreach ($rows as $idx => $r) {
            // Pastikan ada quiz_title
            if (!$r || !isset($r['quiz_title']) || trim((string)$r['quiz_title']) === '') {
                return ['error' => "Baris ke-" . ($idx + 2) . " tidak memiliki kolom quiz_title."];
            }
            // Pastikan ada question_text
            if (!isset($r['question_text']) || trim((string)$r['question_text']) === '') {
                return ['error' => "Baris ke-" . ($idx + 2) . " tidak memiliki kolom question_text."];
            }

            $title = trim((string)$r['quiz_title']);
            $groups[$title][] = $r;
        }

        if (empty($groups)) {
            return ['error' => 'Tidak ada data quiz yang valid di file.'];
        }

        return ['groups' => $groups];
    }

    /**
     * Persist quiz groups to DB. Reusable by importMission and importModule.
     *
     * @param array  $groups
     * @param int    $moduleId
     * @param int|null $missionId
     * @param string|null $defaultCategory
     * @param bool   $requireCategory  Whether category must be pretest/posttest
     * @return array ['success' => true] or ['error' => string]
     */
    protected function persistQuizGroups(
        array $groups,
        int|string $moduleId,
        int|string|null $missionId,
        ?string $defaultCategory,
        bool $requireCategory = false
    ): array {
        foreach ($groups as $quizTitle => $groupRows) {
            $first = $groupRows[0];
            $timeLimit = isset($first['time_limit']) && is_numeric($first['time_limit'])
                ? (int) $first['time_limit']
                : 10;

            // Tentukan kategori
            $category = isset($first['category']) && trim((string)$first['category']) !== ''
                ? trim((string)$first['category'])
                : $defaultCategory;

            if ($requireCategory && !in_array($category, ['pretest', 'posttest'], true)) {
                return ['error' => "Kategori quiz '{$quizTitle}' harus 'pretest' atau 'posttest'. Ditemukan: '{$category}'."];
            }

            $quiz = Quizzes::create([
                'mission_id'  => $missionId,
                'module_id'   => $moduleId,
                'title'       => $quizTitle,
                'description' => isset($first['quiz_description']) ? trim((string)$first['quiz_description']) : null,
                'type'        => 'multiple_choices',
                'time_limit'  => $timeLimit,
                'category'    => $category ?: null,
                'created_by'  => Auth::id(),
            ]);

            Log::info('Quiz import created', [
                'quiz_id'    => $quiz->id,
                'title'      => $quiz->title,
                'module_id'  => $moduleId,
                'mission_id' => $missionId,
                'category'   => $category,
            ]);

            foreach ($groupRows as $qIdx => $qr) {
                // Resolve mascot
                $rawMascot = isset($qr['mascot_id']) ? trim((string)$qr['mascot_id']) : '';
                $resolvedMascot = null;
                if ($rawMascot !== '') {
                    $m = \App\Models\Mascots::where('id', $rawMascot)->first();
                    if (!$m) {
                        $basename = pathinfo($rawMascot, PATHINFO_BASENAME);
                        $m = \App\Models\Mascots::where('image', 'like', "%{$basename}%")->first();
                    }
                    if ($m) $resolvedMascot = $m->id;
                }

                $question = Questions::create([
                    'quiz_id'       => $quiz->id,
                    'mascot_id'     => $resolvedMascot,
                    'question_text' => trim((string)$qr['question_text']),
                    'order_number'  => $qIdx + 1,
                ]);

                // Kumpulkan opsi (option_1 s/d option_10)
                $options = [];
                for ($i = 1; $i <= 10; $i++) {
                    $optKey  = 'option_' . $i;
                    $corrKey = 'option_' . $i . '_is_correct';
                    // Hentikan jika kolom option_N tidak ada sama sekali di header
                    if (!array_key_exists($optKey, $qr)) {
                        break;
                    }
                    $optText = $qr[$optKey];
                    if ($optText === null || $optText === '' || trim((string)$optText) === '') {
                        continue; // Lewati opsi kosong
                    }
                    // Ambil is_correct — CSV tanpa quotes bisa menghasilkan int 1/0 langsung
                    $rawCorrect = array_key_exists($corrKey, $qr) ? $qr[$corrKey] : 0;
                    $isCorrect  = ($rawCorrect === 1 || $rawCorrect === true || $rawCorrect === '1')
                        ? 1
                        : (in_array(strtolower(trim((string)$rawCorrect)), ['true', 'yes', 'y']) ? 1 : 0);
                    $options[] = [
                        'option_text' => trim((string)$optText),
                        'is_correct'  => $isCorrect,
                    ];
                }

                if (count($options) < 2) {
                    return ['error' => "Pertanyaan \"{$qr['question_text']}\" pada quiz \"{$quizTitle}\" harus memiliki minimal 2 opsi. Ditemukan: " . count($options) . " opsi."];
                }

                $hasCorrect = collect($options)->pluck('is_correct')->contains(1);
                if (!$hasCorrect) {
                    return ['error' => "Pertanyaan \"{$qr['question_text']}\" pada quiz \"{$quizTitle}\" harus memiliki minimal 1 jawaban benar (is_correct = 1/true/yes)."];
                }

                foreach ($options as $opt) {
                    Question_options::create([
                        'question_id' => $question->id,
                        'option_text' => $opt['option_text'],
                        'is_correct'  => $opt['is_correct'],
                    ]);
                }

                Log::info('Quiz import question+options created', [
                    'question_id'   => $question->id,
                    'quiz_id'       => $quiz->id,
                    'options_count' => count($options),
                ]);
            }
        }

        return ['success' => true];
    }

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
            'mission'        => null,
            'mascots'        => $mascots,
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

        if ($request->input('type') === 'true_false') {
            $request->validate([
                'tf_question'        => 'required|string',
                'tf_option_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } elseif ($request->input('type') === 'drag_drop') {
            $request->validate([
                'questions'          => 'required|string',
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

            $missionId = in_array($validated['category'], ['pretest', 'posttest']) ? null : $missions->id;

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
                                'question_id' => $question->id,
                                'option_text' => $optionData['option_text'],
                                'is_correct'  => $optionData['is_correct'],
                                'feedback'    => $optionData['feedback'] ?? null,
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

        if ($request->input('type') === 'true_false') {
            $request->validate([
                'tf_question'        => 'required|string',
                'tf_option_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } elseif ($request->input('type') === 'drag_drop') {
            $request->validate([
                'questions'          => 'required|string',
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
     * Import mission-level quizzes (CSV/XLSX).
     *
     * Expected columns (per row):
     *   quiz_title, quiz_description, time_limit, category, question_text,
     *   mascot_id (optional), option_1, option_1_is_correct, option_2, option_2_is_correct,
     *   option_3, option_3_is_correct, option_4, option_4_is_correct
     *
     * Multiple rows with same quiz_title are grouped into one quiz.
     */
    public function importMission(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes'    => 'Format file harus CSV atau XLSX.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        // 1. Parse file
        $parsed = $this->parseImportFile($request->file('file'));
        if (isset($parsed['error'])) {
            return back()->with('error', $parsed['error']);
        }

        // 2. Build groups
        $built = $this->buildQuizGroups($parsed['rows']);
        if (isset($built['error'])) {
            return back()->with('error', $built['error']);
        }

        // 3. Persist
        DB::beginTransaction();
        try {
            $result = $this->persistQuizGroups(
                $built['groups'],
                $modules->id,
                $missions->id,
                null,  // defaultCategory — ambil dari CSV kolom "category"
                false  // tidak wajib pretest/posttest untuk mission-level
            );

            if (isset($result['error'])) {
                DB::rollBack();
                return back()->with('error', $result['error']);
            }

            DB::commit();
            Log::info('Quiz mission import committed', ['module_id' => $modules->id, 'mission_id' => $missions->id]);

            return redirect()
                ->route('admin.modules.missions.show', [$modules->id, $missions->id])
                ->with('success', 'Import quiz berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz mission import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Import module-level quizzes (pretest/posttest). CSV same format as importMission.
     * If category not provided in CSV, pass `category` form field.
     */
    public function importModule(Learning_modules $modules, Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'category' => 'nullable|string',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes'    => 'Format file harus CSV atau XLSX.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        // 1. Parse file
        $parsed = $this->parseImportFile($request->file('file'));
        if (isset($parsed['error'])) {
            return back()->with('error', $parsed['error']);
        }

        // 2. Build groups
        $built = $this->buildQuizGroups($parsed['rows']);
        if (isset($built['error'])) {
            return back()->with('error', $built['error']);
        }

        // 3. Persist
        DB::beginTransaction();
        try {
            $result = $this->persistQuizGroups(
                $built['groups'],
                $modules->id,
                null,                          // module-level: tidak ada mission
                $request->input('category'),   // fallback category dari form
                true                           // wajib pretest/posttest
            );

            if (isset($result['error'])) {
                DB::rollBack();
                return back()->with('error', $result['error']);
            }

            DB::commit();
            Log::info('Quiz module import committed', ['module_id' => $modules->id]);

            return redirect()
                ->route('admin.modules.show', [$modules->id])
                ->with('success', 'Import quiz module berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz module import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
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
     * Show quiz detail for module-level quizzes (no mission)
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
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:multiple_choices,drag_drop,true_false,case_study',
            'time_limit'   => 'required|integer|min:1',
            'category'     => 'nullable|string|max:100',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|string',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
        ]);

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

            foreach ($quizzes->questions as $oldQuestion) {
                foreach ($oldQuestion->options as $oldOption) {
                    if ($oldOption->option_image) {
                        Storage::disk('public')->delete($oldOption->option_image);
                    }
                }
                foreach ($oldQuestion->dragDropItems as $oldItem) {
                    if ($oldItem->item_image) {
                        Storage::disk('public')->delete($oldItem->item_image);
                    }
                }
                $oldQuestion->delete();
            }

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
                        if ($request->hasFile("tf_option_images.{$optionMeta['image_index']}")) {
                            $optionImagePath = $request->file("tf_option_images.{$optionMeta['image_index']}")
                                ->store('questions/options', 'public');
                        }
                    } elseif (!empty($optionMeta['existing_image'])) {
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
