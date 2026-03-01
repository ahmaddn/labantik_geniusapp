<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaygroundLoginController extends Controller
{
    /**
     * [GET] Tampilkan halaman form login playground.
     * Jika session player sudah ada, langsung redirect ke menu modul.
     */
    public function login(): Response|RedirectResponse
    {
        if (session('player')) {
            return redirect()->route('playground.index');
        }

        return Inertia::render('Auth/PlaygroundLogin');
    }

    /**
     * [POST] Proses form login — validasi nama & kelas, simpan ke session.
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'  => ['required', 'string', 'min:2', 'max:60'],
            'kelas' => ['required', 'string', 'in:SD-1,SD-2,SD-3,SD-4,SD-5,SD-6,SMP-7,SMP-8,SMP-9,SMA-10,SMA-11,SMA-12'],
        ], [
            'nama.required'  => 'Nama siswa wajib diisi!',
            'nama.min'       => 'Nama minimal 2 karakter.',
            'nama.max'       => 'Nama maksimal 60 karakter.',
            'kelas.required' => 'Pilih kelas terlebih dahulu!',
            'kelas.in'       => 'Kelas yang dipilih tidak valid.',
        ]);

        session([
            'player' => [
                'nama'  => $validated['nama'],
                'kelas' => $validated['kelas'],
            ],
        ]);

        return redirect()->route('playground.index');
    }

    /**
     * [GET] Menu pemilihan modul — halaman Index yang sudah kita buat.
     * Guard: jika belum login (session kosong), kembalikan ke form login.
     */
    public function index(): Response|RedirectResponse
    {
        if (!session('player')) {
            return redirect()->route('playground.login');
        }

        return Inertia::render('Playground/Index', [
            'user' => [
                'name'  => session('player.nama'),
                'email' => null,             // guest, tidak punya email
                'class' => [
                    'name' => session('player.kelas'),
                ],
            ],
        ]);
    }

    /**
     * [POST] Logout — hapus session player dan kembali ke form login.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('player');

        return redirect()->route('playground.login');
    }
}
