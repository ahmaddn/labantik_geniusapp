<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;

class PlaygroundLoginController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/PlaygroundLogin');
    }

    /**
     * Proses login dengan nama dan password
     */
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:60'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'nama.required' => 'Nama siswa wajib diisi!',
            'nama.min' => 'Nama minimal 2 karakter.',
            'nama.max' => 'Nama maksimal 60 karakter.',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Cari user berdasarkan nama
        $user = User::where('name', $validated['nama'])->first();

        // Validasi user dan password
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return redirect()->back()->withErrors([
                'nama' => 'Nama atau password salah!',
            ])->withInput();
        }

        // Simpan data user ke session
        session([
            'player' => [
                'id' => $user->id,
                'nama' => $user->name,
                'kelas' => $user->class_id,
                'role' => $user->role,
            ],
        ]);

        // Redirect ke playground index
        return redirect()->route('playground.index');
    }

    public function index(): Response
    {
        return Inertia::render('Playground/Index');
    }

    /**
     * Proses form start — validasi & simpan ke session
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:60'],
            'kelas' => ['required', 'string', 'in:SD-1,SD-2,SD-3,SD-4,SD-5,SD-6,SMP-7,SMP-8,SMP-9,SMA-10,SMA-11,SMA-12'],
        ], [
            'nama.required' => 'Nama siswa wajib diisi!',
            'nama.min' => 'Nama minimal 2 karakter.',
            'nama.max' => 'Nama maksimal 60 karakter.',
            'kelas.required' => 'Pilih kelas terlebih dahulu!',
            'kelas.in' => 'Kelas yang dipilih tidak valid.',
        ]);

        // Simpan data siswa ke session
        session([
            'player' => [
                'nama' => $validated['nama'],
                'kelas' => $validated['kelas'],
            ],
        ]);

        // Redirect ke halaman quiz
        return redirect()->route('playground.index');
    }

    /**
     * Halaman quiz — pastikan session player ada
     */
    public function quiz()
    {
        // Guard: kalau belum isi form, balik ke start
        if (! session('player')) {
            return redirect()->route('playground.index');
        }

        return Inertia::render('Admin/Playground/Quiz', [
            'player' => session('player'),
        ]);
    }
}
