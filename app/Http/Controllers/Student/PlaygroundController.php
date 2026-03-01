<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaygroundController extends Controller
{
    /**
     * Guard helper — cek session player.
     * Dipanggil di setiap method agar tidak duplikasi kode.
     */
    private function guardPlayer(): ?RedirectResponse
    {
        if (!session('player')) {
            return redirect()->route('playground.login');
        }

        return null;
    }

    /**
     * [GET] Detail / halaman utama sebuah modul.
     * Dipanggil saat user memilih modul di menu Index.
     */
    public function show(string $moduleId): Response|RedirectResponse
    {
        if ($guard = $this->guardPlayer()) return $guard;

        // TODO: ambil data modul dari DB berdasarkan $moduleId
        // $module = LearningModule::findOrFail($moduleId);

        return Inertia::render('Playground/Module', [
            'player'   => session('player'),
            'moduleId' => $moduleId,
        ]);
    }

    /**
     * [GET] Halaman quiz dalam sebuah modul.
     */
    public function quiz(string $moduleId): Response|RedirectResponse
    {
        if ($guard = $this->guardPlayer()) return $guard;

        // TODO: ambil quiz dari DB
        // $quiz = Quiz::where('module_id', $moduleId)->firstOrFail();

        return Inertia::render('Playground/Quiz', [
            'player'   => session('player'),
            'moduleId' => $moduleId,
        ]);
    }
}
