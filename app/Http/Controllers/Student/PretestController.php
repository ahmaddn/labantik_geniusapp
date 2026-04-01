<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PretestController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  GUARD
    // ──────────────────────────────────────────────────────────────

    private function guardPlayer(): ?RedirectResponse
    {
        if (!session()->has('player')) {
            return redirect()->route('playground.login');
        }
        return null;
    }

    private function playerData(): array
    {
        $player = session('player', []);
        return [
            'id'    => $player['id']        ?? null,
            'name'  => $player['nama']       ?? 'Siswa',
            'class' => ['name' => $player['nama_kelas'] ?? '-'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  SHOW  —  GET /player/playground/pretest
    //  name: playground.pretest.show
    // ──────────────────────────────────────────────────────────────

    public function show(): Response|RedirectResponse
    {
        if ($guard = $this->guardPlayer()) return $guard;

        $data         = $this->dummyData();
        $data['user'] = $this->playerData();

        return Inertia::render('Playground/Pretest/index', $data);
    }

    // ──────────────────────────────────────────────────────────────
    //  PREVIEW  —  GET /playground/pretest/preview
    //  name: playground.pretest.preview
    //  Tanpa auth — khusus development / preview UI
    // ──────────────────────────────────────────────────────────────

    public function preview(): Response
    {
        return Inertia::render('Playground/Pretest/index', $this->dummyData());
    }

    // ──────────────────────────────────────────────────────────────
    //  SUBMIT  —  POST /player/playground/pretest/submit
    //  name: playground.pretest.submit
    //  Data dummy — tidak menyimpan ke DB, langsung redirect ke index
    // ──────────────────────────────────────────────────────────────

    public function submit(Request $request): RedirectResponse
    {
        if ($guard = $this->guardPlayer()) return $guard;

        // Dummy: abaikan payload, langsung balik ke playground
        return redirect()->route('playground.pretest.index');
    }

    // ──────────────────────────────────────────────────────────────
    //  DUMMY DATA
    // ──────────────────────────────────────────────────────────────

    private function dummyData(): array
    {
        return [
            'quiz' => [
                'id'          => 'dummy-quiz-001',
                'title'       => 'Pretest: Pengenalan Materi',
                'description' => 'Jawab semua soal berikut dengan sebaik-baiknya untuk mengukur pemahamanmu!',
                'time_limit'  => 10,
                'type'        => 'pretest',
                'category'    => null,
            ],

            'questions' => [
                [
                    'id'            => 'q-001',
                    'question_text' => 'Apa kepanjangan dari CPU dalam ilmu komputer?',
                    'image'         => null,
                    'order_number'  => 1,
                    'options'       => [
                        ['id' => 'q1-a', 'option_text' => 'Central Processing Unit', 'feedback' => 'Benar! CPU adalah otak dari komputer.'],
                        ['id' => 'q1-b', 'option_text' => 'Computer Personal Unit',  'feedback' => 'Kurang tepat, coba lagi!'],
                        ['id' => 'q1-c', 'option_text' => 'Central Program Utility', 'feedback' => 'Kurang tepat, coba lagi!'],
                        ['id' => 'q1-d', 'option_text' => 'Core Processing Utility', 'feedback' => 'Kurang tepat, coba lagi!'],
                    ],
                ],
                [
                    'id'            => 'q-002',
                    'question_text' => 'Manakah yang merupakan contoh perangkat output komputer?',
                    'image'         => null,
                    'order_number'  => 2,
                    'options'       => [
                        ['id' => 'q2-a', 'option_text' => 'Keyboard',  'feedback' => 'Keyboard adalah perangkat input.'],
                        ['id' => 'q2-b', 'option_text' => 'Mouse',     'feedback' => 'Mouse adalah perangkat input.'],
                        ['id' => 'q2-c', 'option_text' => 'Monitor',   'feedback' => 'Benar! Monitor menampilkan hasil keluaran.'],
                        ['id' => 'q2-d', 'option_text' => 'Flashdisk', 'feedback' => 'Flashdisk adalah perangkat penyimpanan.'],
                    ],
                ],
                [
                    'id'            => 'q-003',
                    'question_text' => 'Satuan terkecil dari data dalam komputer disebut …',
                    'image'         => null,
                    'order_number'  => 3,
                    'options'       => [
                        ['id' => 'q3-a', 'option_text' => 'Byte',     'feedback' => '1 byte = 8 bit, bukan terkecil.'],
                        ['id' => 'q3-b', 'option_text' => 'Kilobyte', 'feedback' => 'Kilobyte jauh lebih besar.'],
                        ['id' => 'q3-c', 'option_text' => 'Bit',      'feedback' => 'Benar! Bit adalah satuan data terkecil.'],
                        ['id' => 'q3-d', 'option_text' => 'Megabyte', 'feedback' => 'Megabyte jauh lebih besar.'],
                    ],
                ],
                [
                    'id'            => 'q-004',
                    'question_text' => 'Sistem operasi yang dikembangkan oleh Microsoft adalah …',
                    'image'         => null,
                    'order_number'  => 4,
                    'options'       => [
                        ['id' => 'q4-a', 'option_text' => 'macOS',   'feedback' => 'macOS dikembangkan oleh Apple.'],
                        ['id' => 'q4-b', 'option_text' => 'Windows', 'feedback' => 'Benar! Windows adalah produk Microsoft.'],
                        ['id' => 'q4-c', 'option_text' => 'Ubuntu',  'feedback' => 'Ubuntu adalah distro Linux.'],
                        ['id' => 'q4-d', 'option_text' => 'Android', 'feedback' => 'Android dikembangkan oleh Google.'],
                    ],
                ],
                [
                    'id'            => 'q-005',
                    'question_text' => 'Berapa jumlah bit dalam 1 byte?',
                    'image'         => null,
                    'order_number'  => 5,
                    'options'       => [
                        ['id' => 'q5-a', 'option_text' => '4 bit',  'feedback' => 'Kurang tepat.'],
                        ['id' => 'q5-b', 'option_text' => '16 bit', 'feedback' => 'Terlalu banyak.'],
                        ['id' => 'q5-c', 'option_text' => '2 bit',  'feedback' => 'Kurang tepat.'],
                        ['id' => 'q5-d', 'option_text' => '8 bit',  'feedback' => 'Benar! 1 byte = 8 bit.'],
                    ],
                ],
            ],

            'mission' => [
                'id'           => 'mission-001',
                'name'         => 'Misi 1: Dasar Komputer',
                'order_number' => 1,
                'module_id'    => 'module-001',
            ],

            'mascot'     => null,
            'background' => null,
            'attempt'    => null,

            'user' => [
                'id'    => null,
                'name'  => 'Siswa Preview',
                'class' => ['name' => 'Kelas Demo'],
            ],
        ];
    }
}
