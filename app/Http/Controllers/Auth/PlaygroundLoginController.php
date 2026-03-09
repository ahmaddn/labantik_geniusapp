<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Templates;
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

    // Ambil template yang aktif / sesuai kebutuhan
$template   = Templates::with('backgrounds')->orderBy('created_at')->first();
$backsound  = $template?->backsound  ? asset('storage/' . $template->backsound)  : null;
$background = $template?->backgrounds->first()?->image
              ? asset('storage/' . $template->backgrounds->first()->image)
              : null;

    return Inertia::render('Auth/PlaygroundLogin', [
        'backsound' => $backsound,
        'background' => $background,
    ]);
}

    /**
     * Proses login dengan nama dan password
     */
    public function authenticate(Request $request)
{
    $validated = $request->validate([
        'nama' => ['required', 'string', 'max:60'],
    ], [
        'nama.required' => 'Username wajib diisi!',
        'nama.max'      => 'Username maksimal 60 karakter.',
    ]);

    // Cari user berdasarkan username
    $user = User::where('username', $validated['nama'])->first();

    if (!$user) {
        return redirect()->back()->withErrors([
            'nama' => 'Username tidak ditemukan!',
        ])->withInput();
    }

    session([
        'player' => [
            'id'         => $user->id,
            'nama'       => $user->name,
            'kelas'      => $user->class_id ?? 'SD-4',
            'role'       => $user->role,
            'email'      => $user->email,
            'nama_kelas' => $user->class?->name ?? 'SD-4',
        ],
    ]);

    return redirect()->route('playground.index');
}

    /**
     * Handle logout from playground (menggunakan session player)
     */
    public function logout(Request $request)
    {
        $request->session()->forget('player');
        return redirect('/');
    }
}
