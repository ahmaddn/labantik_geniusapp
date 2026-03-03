<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Learning_modules;
use App\Http\Controllers\Controller;

class PlaygroundController extends Controller
{
    /**
     * Display the playground index page
     */
    public function index()
    {
        if (!session()->has('player')) {
            return redirect()->route('playground.login');
        }

        $player = session('player');

        $userData = [
            'name'  => $player['nama']  ?? 'Siswa',
            'email' => $player['email'] ?? ($player['nama'] ?? 'siswa') . '@example.com',
            'class' => [
                'name' => $player['nama_kelas'] ?? '-',
            ],
        ];

        $learningModules = $this->getLearningModules();

        return Inertia::render('Playground/Index', [
            'user'            => $userData,
            'learningModules' => $learningModules,
        ]);
    }

    /**
     * Ambil modul dari database beserta kategori quiz yang dimiliki
     */
    private function getLearningModules(): array
    {
        $modules = Learning_modules::where('is_active', true)
            ->with(['quizzes:id,module_id,category'])   // eager load quiz, hanya kolom yang dibutuhkan
            ->orderBy('name')
            ->get();

        return $modules->map(function ($module) {
            // Kumpulkan semua kategori quiz yang ada di modul ini (unik)
            $quizCategories = $module->quizzes
                ->pluck('category')
                ->unique()
                ->values()
                ->toArray();

            // Ambil kategori utama berdasarkan prioritas: pretest > mission > posttest
            $primaryCategory = $this->resolvePrimaryCategory($quizCategories);

            return [
                'id'               => $module->id,
                'name'             => $module->name,
                'description'      => $module->description,
                'thumbnail'        => $module->thumbnail
                                        ? asset('storage/' . $module->thumbnail)
                                        : null,
                // Semua kategori quiz yang dimiliki modul ini (dipakai untuk filter di frontend)
                'quiz_categories'  => $quizCategories,
                // Kategori utama untuk warna aksen & badge
                'primary_category' => $primaryCategory,
                'accent'           => $this->getCategoryAccent($primaryCategory),

                'quizzes_count' => $module->quizzes->count(),
                'best_score'    => 0,
                'has_attempt'   => false,
                'finished'      => false,
            ];
        })->toArray();
    }

    /**
     * Tentukan kategori utama dari daftar kategori quiz.
     * Prioritas: pretest → mission → posttest
     */
    private function resolvePrimaryCategory(array $categories): string
    {
        foreach (['pretest', 'mission', 'posttest'] as $cat) {
            if (in_array($cat, $categories)) return $cat;
        }
        return 'mission';
    }

    /**
     * Warna aksen berdasarkan kategori quiz
     */
    private function getCategoryAccent(string $category): string
    {
        return match ($category) {
            'pretest'  => '#8B5CF6',
            'mission'  => '#FF5500',
            'posttest' => '#0891B2',
            default    => '#34C45A',
        };
    }
}
