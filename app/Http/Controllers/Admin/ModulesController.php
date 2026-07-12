<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Quizzes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ModulesController extends Controller
{
    /**
     * Display a listing of modules.
     */
    public function index()
    {
        $modules = Learning_modules::with(['createdBy:id,name', 'classes:id,name'])
            ->latest()
            ->paginate(12)
            ->through(fn($m) => [
                'id'          => $m->id,
                'name'        => $m->name,
                'description' => $m->description,
                'content'     => $m->content,
                'quotes'      => $m->quotes,
                'closing_text' => $m->closing_text ?? null,
                'thumbnail'   => $m->thumbnail ? Storage::url($m->thumbnail) : null,
                'template_id' => $m->template_id,
                'created_by'  => $m->created_by,
                'is_active'   => $m->is_active,
                'classes'     => $m->classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'template'    => $m->template,
                'createdBy'   => $m->createdBy,
                'created_at'  => $m->created_at,
            ]);

        $classes = \App\Models\Classes::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Modules/Index', [
            'modules'   => $modules,
            'classes'   => $classes,
        ]);
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'nullable|string',
            'quotes'      => 'nullable|string|max:500',
            'closing_text' => 'nullable|string',
            'template_id' => 'nullable|exists:templates,id',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5014',
            'is_active'   => 'nullable|boolean',
            'class_ids'   => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ], [
            'name.required'        => 'Nama modul wajib diisi.',
            'thumbnail.image'      => 'File harus berupa gambar.',
            'thumbnail.max'        => 'Ukuran gambar maksimal 5MB.',
            'template_id.exists'   => 'Template tidak ditemukan.',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('modules/thumbnails', 'public');
        }

        $module = Learning_modules::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? '',
            'content'     => $validated['content'] ?? '',
            'quotes'      => $validated['quotes'] ?? '',
            'closing_text' => $validated['closing_text'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'thumbnail'   => $thumbnailPath,
            'created_by'  => Auth::id(),
            'is_active'   => $validated['is_active'] ?? false,
        ]);

        if (!empty($validated['class_ids'])) {
            $module->classes()->sync($validated['class_ids']);
        }

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    /**
     * Show module detail with missions list.
     */
    public function show(Learning_modules $modules)
    {
        /*
    |--------------------------------------------------------------------------
    | MISSIONS
    |--------------------------------------------------------------------------
    */
        $missions = Missions::where('module_id', $modules->id)
            ->withCount(['materials', 'quizzes'])
            ->orderBy('order_number')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'order_number' => $m->order_number,
                'description' => $m->description ?? null,
                'duration' => $m->duration ?? null,
                'materials_count' => $m->materials_count ?? 0,
                'quizzes_count' => $m->quizzes_count ?? 0,
                'conclusion_speech' => $m->conclusion_speech ?? null,
                'conclusion_body' => $m->conclusion_body ?? null,
            ]);

        /*
    |--------------------------------------------------------------------------
    | QUIZZES (MODULE LEVEL)
    |--------------------------------------------------------------------------
    */
        $moduleQuizzes = Quizzes::where('module_id', $modules->id)
            ->withCount('questions')
            ->orderBy('created_at', 'asc')
            ->get();

        $pretest = $moduleQuizzes
            ->where('category', 'pretest')
            ->whereNull('mission_id')
            ->values()
            ->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'questions_count' => $q->questions_count,
                'time_limit' => $q->time_limit,
                'is_randomized' => $q->is_randomized,
            ]);

        $posttest = $moduleQuizzes
            ->where('category', 'posttest')
            ->whereNull('mission_id')
            ->values()
            ->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'questions_count' => $q->questions_count,
                'time_limit' => $q->time_limit,
                'is_randomized' => $q->is_randomized,
            ]);

        return Inertia::render('Admin/Modules/Show', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
                'description' => $modules->description,
                'quotes' => $modules->quotes ?? null,
                'closing_text' => $modules->closing_text ?? null,
            ],
            'pretest' => $pretest,
            'missions' => $missions,
            'posttest' => $posttest,
        ]);
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, Learning_modules $modules)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'nullable|string',
            'quotes'      => 'nullable|string|max:500',
            'closing_text' => 'nullable|string',
            'template_id' => 'nullable|exists:templates,id',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5014',
            'is_active'   => 'nullable|boolean',
            'class_ids'   => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ], [
            'name.required'        => 'Nama modul wajib diisi.',
            'thumbnail.image'      => 'File harus berupa gambar.',
            'thumbnail.max'        => 'Ukuran gambar maksimal 5MB.',
            'template_id.exists'   => 'Template tidak ditemukan.',
        ]);

        $thumbnailPath = $modules->thumbnail;

        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama jika ada
            if ($modules->thumbnail && Storage::disk('public')->exists($modules->thumbnail)) {
                Storage::disk('public')->delete($modules->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')
                ->store('modules/thumbnails', 'public');
        }

        // Jika thumbnail di-remove dari frontend (kirim thumbnail_remove=1)
        if ($request->boolean('thumbnail_remove') && $modules->thumbnail) {
            if (Storage::disk('public')->exists($modules->thumbnail)) {
                Storage::disk('public')->delete($modules->thumbnail);
            }
            $thumbnailPath = null;
        }

        $modules->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? '',
            'content'     => $validated['content'] ?? '',
            'quotes'      => $validated['quotes'] ?? '',
            'closing_text' => $validated['closing_text'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'thumbnail'   => $thumbnailPath,
            'is_active'   => $validated['is_active'] ?? $modules->is_active,
        ]);

        if (isset($validated['class_ids'])) {
            $modules->classes()->sync($validated['class_ids']);
        } else {
            $modules->classes()->sync([]);
        }

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil diperbarui.');
    }

    /**
     * Toggle module active status (quick toggle).
     */
    public function toggleActive(Learning_modules $modules)
    {
        $modules->update([
            'is_active' => !$modules->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Status modul berhasil diubah.');
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Learning_modules $modules)
    {
        // Hapus thumbnail dari storage jika ada
        if ($modules->thumbnail && Storage::disk('public')->exists($modules->thumbnail)) {
            Storage::disk('public')->delete($modules->thumbnail);
        }

        $modules->delete();

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil dihapus.');
    }
}
