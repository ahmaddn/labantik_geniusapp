<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;


class ClassesController extends Controller
{
    public function index()
    {
        $classes = Classes::with('teacher')->latest()->paginate(12);

        $usedTeacherIds = Classes::whereNotNull('teacher_id')
            ->pluck('teacher_id')
            ->toArray();

        $teachers = User::where('role', 'guru')
            ->whereNotIn('id', $usedTeacherIds)
            ->select('id', 'name')
            ->orderBy('name')->get();

        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
            'teachers' => $teachers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        Classes::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
            'teacher_id' => $request->teacher_id,

        ]);

        return redirect()->back()->with('success', 'Kelas baru berhasil ditambahkan');
    }

    public function update(Request $request, Classes $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $class->update($request->only('name', 'description', 'teacher_id'));

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(Classes $class)
    {
        if ($class->users()->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kelas karena masih ada siswa terdaftar');
        }

        $class->delete();
        return redirect()->back()->with('success', 'Kelas berhasil dihapus');
    }
}
