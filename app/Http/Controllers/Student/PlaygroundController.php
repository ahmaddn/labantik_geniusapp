<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PlaygroundController extends Controller
{
    /**
     * Halaman index – daftar semua modul.
     * User didapat dari SESSION (bukan Auth::user())
     * karena login pakai PlaygroundLogin (nama + kelas), bukan akun Laravel.
     *
     * Route: GET /player/playground  →  student.index
     */
    public function index(): Response
    {
        // ── Ambil data siswa dari session ─────────────────────────────────
        $siswa = [
            'name'  => session('player_name',  'Siswa'),
            'kelas' => session('player_kelas', ''),
            'xp'    => session('player_xp',    0),
            'streak'=> session('player_streak', 0),
        ];

        // ── Dummy modul (ganti dengan DB query saat siap) ─────────────────
        $modules = $this->dummyModules();

        $completedCount = collect($modules)->where('status', 'selesai')->count();
        $totalStars     = collect($modules)->sum('stars');

        return Inertia::render('Student/Index', [
            'siswa'          => $siswa,          // ← dikirim ke Vue
            'modules'        => $modules,
            'completedCount' => $completedCount,
            'totalStars'     => $totalStars,
            'streak'         => $siswa['streak'],
        ]);
    }

    // ── Dummy data ────────────────────────────────────────────────────────
    private function dummyModules(): array
    {
        return [
            [
                'id'          => 1,
                'slug'        => 'petualangan-si-air',
                'title'       => 'Petualangan Si Air: Mengapa Sungai Bisa Marah & Banjir',
                'description' => 'Pelajari ekosistem sungai, penyebab banjir, dan cara menjaga keseimbangan lingkungan.',
                'subject'     => 'IPA',
                'icon'        => '🌊',
                'color'       => 'linear-gradient(135deg, #4ec9ff 0%, #1a7fd4 100%)',
                'duration'    => '45 menit',
                'grade'       => '4–6',
                'status'      => 'aktif',
                'progress'    => 60,
                'stars'       => 0,
            ],
            [
                'id'          => 2,
                'slug'        => 'hutan-penjaga-udara',
                'title'       => 'Hutan Kita: Penjaga Udara Bersih',
                'description' => 'Mengenal peran hutan dalam menjaga kualitas udara dan kehidupan makhluk hidup.',
                'subject'     => 'IPA',
                'icon'        => '🌿',
                'color'       => 'linear-gradient(135deg, #78d878 0%, #2eaa2e 100%)',
                'duration'    => '40 menit',
                'grade'       => '4–6',
                'status'      => 'aktif',
                'progress'    => 0,
                'stars'       => 0,
            ],
            [
                'id'          => 3,
                'slug'        => 'energi-matahari',
                'title'       => 'Energi Matahari: Sumber Kehidupan',
                'description' => 'Memahami bagaimana energi matahari mendukung kehidupan di bumi.',
                'subject'     => 'IPA',
                'icon'        => '☀️',
                'color'       => 'linear-gradient(135deg, #ffd966 0%, #f0a500 100%)',
                'duration'    => '35 menit',
                'grade'       => '4–6',
                'status'      => 'selesai',
                'progress'    => 100,
                'stars'       => 3,
            ],
            [
                'id'          => 4,
                'slug'        => 'siklus-hidup',
                'title'       => 'Siklus Hidup Makhluk Hidup',
                'description' => 'Mengamati siklus hidup hewan dan tumbuhan dari telur hingga dewasa.',
                'subject'     => 'IPA',
                'icon'        => '🦋',
                'color'       => 'linear-gradient(135deg, #f0a8e0 0%, #c020a0 100%)',
                'duration'    => '30 menit',
                'grade'       => '3–5',
                'status'      => 'selesai',
                'progress'    => 100,
                'stars'       => 2,
            ],
            [
                'id'          => 5,
                'slug'        => 'perubahan-iklim',
                'title'       => 'Perubahan Iklim & Kita',
                'description' => 'Memahami dampak perubahan iklim dan apa yang bisa kita lakukan bersama.',
                'subject'     => 'IPS',
                'icon'        => '🌏',
                'color'       => 'linear-gradient(135deg, #aaccff 0%, #5580cc 100%)',
                'duration'    => '50 menit',
                'grade'       => '5–6',
                'status'      => 'segera',
                'progress'    => 0,
                'stars'       => 0,
            ],
            [
                'id'          => 6,
                'slug'        => 'dunia-mikroskopis',
                'title'       => 'Dunia Mikroskopis: Bakteri & Virus',
                'description' => 'Mengenal makhluk hidup yang tidak bisa dilihat dengan mata telanjang.',
                'subject'     => 'IPA',
                'icon'        => '🔬',
                'color'       => 'linear-gradient(135deg, #ffb08a 0%, #e05020 100%)',
                'duration'    => '45 menit',
                'grade'       => '5–6',
                'status'      => 'segera',
                'progress'    => 0,
                'stars'       => 0,
            ],
        ];
    }
}
