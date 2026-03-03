<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PlaygroundLoginController extends Controller
{
    /**
     * Tampilkan halaman form login playground
     */
    public function login()
    {
        // Jika sudah login, redirect ke playground
        if (session()->has('player')) {
            return redirect()->route('playground.index');
        }

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
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return redirect()->back()->withErrors([
                'nama' => 'Nama atau password salah!',
            ])->withInput();
        }

        // Simpan data user ke session
        session([
            'player' => [
                'id' => $user->id,
                'nama' => $user->name,
                'kelas' => $user->class_id ?? 'SD-4',
                'role' => $user->role,
                'email' => $user->email,
                'nama_kelas' => $user->class?->name ?? 'SD-4',
            ],
        ]);

        // Redirect ke playground index (akan otomatis ke /player/playground)
        return redirect()->route('playground.index');
    }

    /**
     * Handle logout from playground (menggunakan session player)
     */
    public function logout(Request $request)
    {
        $request->session()->forget('player');
        return redirect()->route('playground.login');
    }
}
