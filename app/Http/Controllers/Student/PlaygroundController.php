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
     * Ambil semua modul aktif dari database
     */
    private function getLearningModules(): array
    {
        return Learning_modules::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($module) => [
                'id'          => $module->id,
                'name'        => $module->name,
                'description' => $module->description,
                'thumbnail'   => $module->thumbnail
                                    ? asset('storage/' . $module->thumbnail)
                                    : null,
                'best_score'  => 0,
                'has_attempt' => false,
                'finished'    => false,
            ])
            ->toArray();
    }
}
