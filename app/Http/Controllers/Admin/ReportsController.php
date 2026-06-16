<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Quizzes;
use App\Models\Quiz_attempts;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    /**
     * Helper: ambil semua quiz_id milik sebuah modul
     */
    private function getQuizIdsByModule(Learning_modules $module)
    {
        $directQuizIds = Quizzes::where('module_id', $module->id)->pluck('id');

        $missionQuizIds = Quizzes::whereHas(
            'mission',
            fn($q) =>
            $q->where('module_id', $module->id)
        )->pluck('id');

        return $directQuizIds->merge($missionQuizIds)->unique()->values();
    }

    /**
     * Dashboard index — statistik ringkas semua modul
     */
    public function index()
    {
        $totalStudents = User::where('role', 'siswa')->count();
        $totalModules  = Learning_modules::count();

        // Statistik per modul
        $modules = Learning_modules::select('id', 'name')->latest()->get();

        $modulesStats = $modules->map(function ($module) {
            $quizIds = $this->getQuizIdsByModule($module);

            $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)->get();

            // Berapa siswa unik yang sudah mengerjakan
            $studentsDone = $attempts->pluck('student_id')->unique()->count();

            // Rata-rata nilai keseluruhan
            $avgScore = $attempts->count() > 0
                ? (int) round($attempts->avg('score'))
                : null;

            // Jumlah attempt selesai (finished_at tidak null)
            $completedAttempts = $attempts->whereNotNull('finished_at')->count();

            return [
                'id'                 => $module->id,
                'name'               => $module->name,
                'students_done'      => $studentsDone,
                'avg_score'          => $avgScore,
                'completed_attempts' => $completedAttempts,
                'total_quizzes'      => $quizIds->count(),
            ];
        });

        // Statistik global
        $allAttempts    = Quiz_attempts::whereNotNull('finished_at')->get();
        $globalAvgScore = $allAttempts->count() > 0
            ? (int) round($allAttempts->avg('score'))
            : null;

        $totalAttempts = $allAttempts->count();

        return Inertia::render('Admin/Reports/Index', [
            'modules'          => $modulesStats,
            'total_students'   => $totalStudents,
            'total_modules'    => $totalModules,
            'global_avg_score' => $globalAvgScore,
            'total_attempts'   => $totalAttempts,
        ]);
    }

    /**
     * Detail per modul: daftar siswa + progress mereka
     */
    public function moduleHistory(Request $request, Learning_modules $modules)
    {
        $quizIds    = $this->getQuizIdsByModule($modules);
        $totalQuizzes = $quizIds->count();

        $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
            ->with('student')
            ->get();

        $grouped = $attempts
            ->filter(fn($a) => $a->student !== null)
            ->groupBy('student_id');

        $students = [];

        foreach ($grouped as $studentId => $studentAttempts) {
            $student = $studentAttempts->first()->student;

            $byQuiz = $studentAttempts
                ->groupBy('quiz_id')
                ->map(fn($g) => (int) $g->max('score'));

            $overall = $byQuiz->count() > 0 ? (int) round($byQuiz->avg()) : 0;

            // Persentase penyelesaian quiz
            $completion = $totalQuizzes > 0
                ? (int) round(($byQuiz->count() / $totalQuizzes) * 100)
                : 0;

            $students[] = [
                'id'             => $student->id,
                'name'           => $student->name,
                'class'          => $student->class?->name ?? '-',
                'quizzes_count'  => $byQuiz->count(),
                'quizzes_total'  => $totalQuizzes,
                'overall_score'  => $overall,
                'completion'     => $completion,
            ];
        }

        if ($request->boolean('include_all')) {
            $allStudents = User::where('role', 'siswa')->get();
            $existingIds = collect($students)->pluck('id')->toArray();
            foreach ($allStudents as $s) {
                if (in_array($s->id, $existingIds)) continue;
                $students[] = [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'class'         => $s->class?->name ?? '-',
                    'quizzes_count' => 0,
                    'quizzes_total' => $totalQuizzes,
                    'overall_score' => 0,
                    'completion'    => 0,
                ];
            }
        }

        usort($students, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        // Ringkasan modul untuk header
        $moduleAvg = collect($students)->avg('overall_score');
        $moduleSummary = [
            'total_students' => count($students),
            'avg_score'      => $students ? (int) round($moduleAvg) : null,
            'total_quizzes'  => $totalQuizzes,
        ];

        $missionLogsRaw = \App\Models\StudentMissionLog::where('module_id', $modules->id)
            ->with(['user', 'mission'])
            ->orderBy('completed_at', 'desc')
            ->get();

        $missionLogs = $missionLogsRaw->map(function ($log) {
            return [
                'id' => $log->id,
                'student_name' => $log->user ? $log->user->name : '-',
                'mission_name' => $log->mission ? $log->mission->name : '-',
                'attempt_number' => $log->attempt_number,
                'completed_at' => $log->completed_at ? $log->completed_at->format('d M Y H:i') : '-',
            ];
        });

        return Inertia::render('Admin/Reports/ModuleHistory', [
            'module'        => ['id' => $modules->id, 'name' => $modules->name],
            'students'      => $students,
            'module_summary' => $moduleSummary,
            'mission_logs'  => $missionLogs,
        ]);
    }

    /**
     * Laporan detail seorang siswa di satu modul
     */
    public function studentReport(Learning_modules $modules, User $student)
    {
        $quizIds = $this->getQuizIdsByModule($modules);

        $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
            ->where('student_id', $student->id)
            ->with([
                'quiz', 
                'answers.question.options', 
                'answers.question.dragDropGroups.items',
                'answers.selectedOption'
            ])
            ->orderBy('finished_at')
            ->get();

        $quizzes = $attempts->map(fn($a) => [
            'attempt_id'  => $a->id,
            'quiz_id'     => $a->quiz_id,
            'quiz_title'  => $a->quiz?->title ?? 'Quiz',
            'quiz_type'   => $a->quiz?->type,
            'score'       => (int) $a->score,
            'started_at'  => $a->started_at,
            'finished_at' => $a->finished_at,
            'answers'     => $a->answers->map(fn($ans) => [
                'question_id'   => $ans->question_id,
                'question_text' => $ans->question?->question_text,
                'response'      => $ans->response,
                'selected_option' => $ans->selectedOption ? $ans->selectedOption->option_text : null,
                'is_correct'    => $ans->selectedOption ? (bool)$ans->selectedOption->is_correct : null,
                // Include options or groups if needed for more complex rendering
                'options'       => $ans->question?->options,
                'dragDropGroups'=> $ans->question?->dragDropGroups,
            ]),
        ])->values();

        $overall = $quizzes->count() > 0 ? (int) round($quizzes->avg('score')) : 0;

        $chartLabels = $quizzes->pluck('quiz_title')->toArray();
        $chartScores = $quizzes->pluck('score')->toArray();

        $scoreDistribution = [
            'low'    => $quizzes->filter(fn($q) => $q['score'] < 60)->count(),
            'medium' => $quizzes->filter(fn($q) => $q['score'] >= 60 && $q['score'] < 80)->count(),
            'high'   => $quizzes->filter(fn($q) => $q['score'] >= 80)->count(),
        ];

        // Fetch Reflection Answers
        $reflectionAnswers = \App\Models\Reflection_answers::where('user_id', $student->id)
            ->whereHas('question.reflection.mission', function($q) use ($modules) {
                $q->where('module_id', $modules->id);
            })
            ->with(['question.reflection'])
            ->get();

        $reflections = $reflectionAnswers->groupBy('question.reflection_id')->map(function($answers, $reflectionId) {
            $reflection = $answers->first()->question->reflection;
            return [
                'reflection_id' => $reflectionId,
                'title' => $reflection->title ?? 'Refleksi',
                'overall_score' => (int) round($answers->avg('score')),
                'answers' => $answers->map(fn($ans) => [
                    'answer_id' => $ans->id,
                    'question_text' => $ans->question->question_text,
                    'answer_text' => $ans->answer_text,
                    'score' => $ans->score,
                ])->values()
            ];
        })->values();

        return Inertia::render('Admin/Reports/StudentReport', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'student' => [
                'id'    => $student->id,
                'name'  => $student->name,
                'class' => $student->class?->name ?? null,
            ],
            'quizzes'           => $quizzes,
            'reflections'       => $reflections,
            'overall'           => $overall,
            'chartLabels'       => $chartLabels,
            'chartScores'       => $chartScores,
            'scoreDistribution' => $scoreDistribution,
        ]);
    }

    /**
     * Memperbarui skor secara manual oleh admin
     */
    public function updateScore(Request $request, Learning_modules $modules, User $student)
    {
        $request->validate([
            'type' => 'required|in:quiz,reflection_answer',
            'id' => 'required|string',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        if ($request->type === 'quiz') {
            $attempt = Quiz_attempts::findOrFail($request->id);
            $attempt->score = $request->score;
            $attempt->save();
        } elseif ($request->type === 'reflection_answer') {
            $answer = \App\Models\Reflection_answers::findOrFail($request->id);
            $answer->score = $request->score;
            $answer->save();
        }

        return redirect()->back()->with('success', 'Nilai berhasil diperbarui');
    }

    /**
     * Export laporan modul ke XLSX
     */
    public function exportModuleXlsx(Request $request, Learning_modules $modules)
    {
        $quizIds    = $this->getQuizIdsByModule($modules);
        $totalQuizzes = $quizIds->count();

        $attempts = Quiz_attempts::whereIn('quiz_id', $quizIds)
            ->with('student.class', 'quiz')
            ->get();

        $grouped = $attempts
            ->filter(fn($a) => $a->student !== null)
            ->groupBy('student_id');

        // Extract all unique quiz titles to make column headers
        $allQuizTitles = [];
        foreach ($attempts as $att) {
            if ($att->quiz) {
                $allQuizTitles[$att->quiz_id] = $att->quiz->title;
            }
        }

        $students = [];
        foreach ($grouped as $studentId => $studentAttempts) {
            $student = $studentAttempts->first()->student;
            $byQuiz = $studentAttempts->groupBy('quiz_id')->map(fn($g) => (int) $g->max('score'));
            $overall = $byQuiz->count() > 0 ? (int) round($byQuiz->avg()) : 0;
            $completion = $totalQuizzes > 0 ? (int) round(($byQuiz->count() / $totalQuizzes) * 100) : 0;

            // Detail per kuis
            $detailQuizzes = [];
            foreach ($studentAttempts as $att) {
                if (!isset($detailQuizzes[$att->quiz_id]) || $detailQuizzes[$att->quiz_id]['score'] < $att->score) {
                    $detailQuizzes[$att->quiz_id] = [
                        'title' => $att->quiz->title ?? 'Quiz',
                        'score' => $att->score,
                        'finished_at' => $att->finished_at ? \Carbon\Carbon::parse($att->finished_at)->format('d/m/Y H:i') : 'Belum selesai'
                    ];
                }
            }

            $students[] = [
                'name' => $student->name,
                'class' => $student->class?->name ?? '-',
                'quizzes_count' => $byQuiz->count(),
                'overall_score' => $overall,
                'completion_percent' => $completion,
                'details' => $detailQuizzes
            ];
        }

        // Add students with 0 completion if requested
        if ($request->boolean('include_all')) {
            $allStudents = User::where('role', 'siswa')->with('class')->get();
            $existingNames = collect($students)->pluck('name')->toArray();
            foreach ($allStudents as $s) {
                if (in_array($s->name, $existingNames)) continue;
                $students[] = [
                    'name' => $s->name,
                    'class' => $s->class?->name ?? '-',
                    'quizzes_count' => 0,
                    'overall_score' => 0,
                    'completion_percent' => 0,
                    'details' => []
                ];
            }
        }

        usort($students, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        $filename = "Laporan_Modul_" . \Illuminate\Support\Str::slug($modules->name) . "_" . date('Ymd_His') . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Nilai');

        // Report Title Header
        $sheet->setCellValue('A1', 'Laporan Nilai Modul: ' . $modules->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        // Merge title cell across dynamic columns
        $lastColIndex = 5 + count($allQuizTitles); // A=1, B=2, C=3, D=4, E=5, then quizzes
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max($lastColIndex, 5));
        $sheet->mergeCells('A1:' . $lastColLetter . '1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers at Row 3
        $sheet->setCellValue('A3', 'Nama Siswa');
        $sheet->setCellValue('B3', 'Kelas / Sekolah');
        $sheet->setCellValue('C3', "Kuis Selesai (dari {$totalQuizzes})");
        $sheet->setCellValue('D3', 'Skor Rata-rata');
        $sheet->setCellValue('E3', 'Progres (%)');
        
        $colIdx = 6;
        foreach ($allQuizTitles as $qId => $qTitle) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($colLetter . '3', 'Nilai: ' . $qTitle);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $colIdx++;
        }

        // Styling headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A3:' . $lastColLetter . '3')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $rowNum = 4;
        foreach ($students as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['name']);
            $sheet->setCellValue('B' . $rowNum, $row['class']);
            $sheet->setCellValue('C' . $rowNum, $row['quizzes_count']);
            $sheet->setCellValue('D' . $rowNum, $row['overall_score']);
            $sheet->setCellValue('E' . $rowNum, $row['completion_percent']);
            
            $colIdx = 6;
            foreach ($allQuizTitles as $qId => $qTitle) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                if (isset($row['details'][$qId])) {
                    $sheet->setCellValue($colLetter . $rowNum, $row['details'][$qId]['score']);
                } else {
                    $sheet->setCellValue($colLetter . $rowNum, '-');
                }
                $sheet->getStyle($colLetter . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIdx++;
            }

            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
