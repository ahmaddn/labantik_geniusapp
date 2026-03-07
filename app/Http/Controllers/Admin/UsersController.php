<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with(['class', 'createdBy'])->latest()->get();
        $classes = Classes::latest()->get();
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'classes' => $classes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        // If current user is a teacher, they may only create student accounts
        $current = Auth::user();
        if ($current && $current->role === 'guru' && $request->role !== 'siswa') {
            return redirect()->back()->with('error', 'Role guru hanya dapat membuat akun siswa.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password ?? '11223344'),
            'role' => $request->role,
            'class_id' => $request->class_id,
            'created_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $current = Auth::user();

        // If current user is a teacher, disallow updating admin accounts or assigning non-student roles
        if ($current && $current->role === 'guru') {
            if ($user->role === 'admin') {
                return redirect()->back()->with('error', 'Anda tidak diizinkan mengubah akun admin.');
            }
            if ($request->role !== 'siswa') {
                return redirect()->back()->with('error', 'Role guru hanya dapat mengatur akun siswa.');
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role,
            'class_id' => $request->class_id,
        ]);

        return redirect()->back()->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $current = Auth::user();

        // Prevent deleting self
        if ($current && $current->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // If current user is a teacher, they may only delete student accounts
        if ($current && $current->role === 'guru' && $user->role !== 'siswa') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menghapus akun non-siswa.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus');
    }
}
