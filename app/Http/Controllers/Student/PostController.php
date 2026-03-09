<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Preview PosttestQuiz dengan data dummy.
     */
    public function preview()
    {
        $quiz = [
            'id'          => 'quiz-post-001',
            'title'       => 'Posttest Penguasaan Materi',
            'description' => 'Uji pemahamanmu secara menyeluruh setelah menyelesaikan semua misi. Tunjukkan kemampuan terbaikmu!',
            'time_limit'  => 15,
            'type'        => 'posttest',
            'category'    => null,
            'module_id'   => 'mod-001',
            'mission_id'  => null,
        ];

        $mission = [
            'id'           => 'mission-001',
            'name'         => 'Misi 1: Dasar Komputer',
            'order_number' => 1,
            'module_id'    => 'mod-001',
        ];

        $mascot = [
            'id'          => 'mascot-001',
            'name_pose'   => 'pose_semangat',
            'image'       => null,
            'template_id' => 'tmpl-001',
        ];

        $background = [
            'id'          => 'bg-001',
            'name'        => 'Background Posttest',
            'image'       => null,
            'template_id' => 'tmpl-001',
        ];

        $questions = [
            [
                'id'            => 'q-post-01',
                'quiz_id'       => 'quiz-post-001',
                'question_text' => 'Apa kepanjangan dari CPU dalam ilmu komputer?',
                'image'         => null,
                'order_number'  => 1,
                'mascot_id'     => null,
                'options'       => [
                    ['id' => 'opt-01a', 'question_id' => 'q-post-01', 'option_text' => 'Central Processing Unit',   'is_correct' => true,  'feedback' => 'Benar! CPU adalah otak dari komputer.'],
                    ['id' => 'opt-01b', 'question_id' => 'q-post-01', 'option_text' => 'Computer Personal Unit',    'is_correct' => false, 'feedback' => 'Kurang tepat. Coba ingat kembali!'],
                    ['id' => 'opt-01c', 'question_id' => 'q-post-01', 'option_text' => 'Central Program Utility',   'is_correct' => false, 'feedback' => "Kurang tepat. Perhatikan kata 'Processing'."],
                    ['id' => 'opt-01d', 'question_id' => 'q-post-01', 'option_text' => 'Core Processing Utility',   'is_correct' => false, 'feedback' => 'Kurang tepat. CPU = Central Processing Unit.'],
                ],
            ],
            [
                'id'            => 'q-post-02',
                'quiz_id'       => 'quiz-post-001',
                'question_text' => 'Komponen berikut yang termasuk perangkat OUTPUT komputer adalah…',
                'image'         => null,
                'order_number'  => 2,
                'mascot_id'     => null,
                'options'       => [
                    ['id' => 'opt-02a', 'question_id' => 'q-post-02', 'option_text' => 'Keyboard',  'is_correct' => false, 'feedback' => 'Keyboard adalah perangkat INPUT.'],
                    ['id' => 'opt-02b', 'question_id' => 'q-post-02', 'option_text' => 'Mouse',     'is_correct' => false, 'feedback' => 'Mouse adalah perangkat INPUT.'],
                    ['id' => 'opt-02c', 'question_id' => 'q-post-02', 'option_text' => 'Monitor',   'is_correct' => true,  'feedback' => 'Benar! Monitor menampilkan hasil pemrosesan (output).'],
                    ['id' => 'opt-02d', 'question_id' => 'q-post-02', 'option_text' => 'Mikrofon',  'is_correct' => false, 'feedback' => 'Mikrofon adalah perangkat INPUT.'],
                ],
            ],
            [
                'id'            => 'q-post-03',
                'quiz_id'       => 'quiz-post-001',
                'question_text' => 'RAM dalam komputer berfungsi untuk…',
                'image'         => null,
                'order_number'  => 3,
                'mascot_id'     => null,
                'options'       => [
                    ['id' => 'opt-03a', 'question_id' => 'q-post-03', 'option_text' => 'Menyimpan data secara permanen',                 'is_correct' => false, 'feedback' => 'Itu adalah fungsi Harddisk / SSD.'],
                    ['id' => 'opt-03b', 'question_id' => 'q-post-03', 'option_text' => 'Menyimpan data sementara saat komputer menyala', 'is_correct' => true,  'feedback' => 'Benar! RAM = Random Access Memory, bersifat sementara (volatile).'],
                    ['id' => 'opt-03c', 'question_id' => 'q-post-03', 'option_text' => 'Memproses instruksi dari pengguna',              'is_correct' => false, 'feedback' => 'Itu adalah fungsi CPU.'],
                    ['id' => 'opt-03d', 'question_id' => 'q-post-03', 'option_text' => 'Menampilkan gambar ke layar',                    'is_correct' => false, 'feedback' => 'Itu adalah fungsi GPU / kartu grafis.'],
                ],
            ],
            [
                'id'            => 'q-post-04',
                'quiz_id'       => 'quiz-post-001',
                'question_text' => 'Sistem operasi yang dikembangkan oleh Microsoft adalah…',
                'image'         => null,
                'order_number'  => 4,
                'mascot_id'     => null,
                'options'       => [
                    ['id' => 'opt-04a', 'question_id' => 'q-post-04', 'option_text' => 'macOS',   'is_correct' => false, 'feedback' => 'macOS dikembangkan oleh Apple.'],
                    ['id' => 'opt-04b', 'question_id' => 'q-post-04', 'option_text' => 'Linux',   'is_correct' => false, 'feedback' => 'Linux dikembangkan oleh komunitas open-source.'],
                    ['id' => 'opt-04c', 'question_id' => 'q-post-04', 'option_text' => 'Windows', 'is_correct' => true,  'feedback' => 'Benar! Windows adalah produk Microsoft.'],
                    ['id' => 'opt-04d', 'question_id' => 'q-post-04', 'option_text' => 'Android', 'is_correct' => false, 'feedback' => 'Android dikembangkan oleh Google.'],
                ],
            ],
            [
                'id'            => 'q-post-05',
                'quiz_id'       => 'quiz-post-001',
                'question_text' => 'Berapa nilai biner dari angka desimal 10?',
                'image'         => null,
                'order_number'  => 5,
                'mascot_id'     => null,
                'options'       => [
                    ['id' => 'opt-05a', 'question_id' => 'q-post-05', 'option_text' => '0101', 'is_correct' => false, 'feedback' => '0101 dalam biner = 5 dalam desimal.'],
                    ['id' => 'opt-05b', 'question_id' => 'q-post-05', 'option_text' => '1010', 'is_correct' => true,  'feedback' => 'Benar! 1010 = 8+2 = 10.'],
                    ['id' => 'opt-05c', 'question_id' => 'q-post-05', 'option_text' => '1100', 'is_correct' => false, 'feedback' => '1100 dalam biner = 12 dalam desimal.'],
                    ['id' => 'opt-05d', 'question_id' => 'q-post-05', 'option_text' => '1001', 'is_correct' => false, 'feedback' => '1001 dalam biner = 9 dalam desimal.'],
                ],
            ],
        ];

        return Inertia::render('Playground/PostTest/Index', [
            'quiz'       => $quiz,
            'questions'  => $questions,
            'mission'    => $mission,
            'mascot'     => $mascot,
            'background' => $background,
            'attempt'    => null,
        ]);
    }

    /**
     * Handle submit posttest (dummy — simpan ke session atau log saja).
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'quiz_id'    => 'required|string',
            'time_taken' => 'required|integer|min:0',
            'score'      => 'required|integer|min:0|max:100',
            'answers'    => 'required|array',
            'answers.*.question_id'       => 'required|string',
            'answers.*.selected_option_id' => 'required|string',
        ]);

        // Dummy: log hasil, tidak simpan ke DB

        // Redirect balik ke preview (atau ke playground index kalau sudah ada)
        return redirect()->route('playground.index');
    }
}
