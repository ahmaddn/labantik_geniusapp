<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Materials;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Show form to create material
     */
    public function create(Learning_modules $modules, Missions $missions)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        // Load template mascots if exists
        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Materials/Create', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
                'order_number' => $missions->order_number,
            ],
            'mascots' => $mascots,
        ]);
    }

    /**
     * Store material
     */
    public function store(Learning_modules $modules, Missions $missions, Request $request)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'mascot_id' => 'nullable|exists:mascots,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'title.required' => 'Judul material wajib diisi.',
            'content.required' => 'Konten material wajib diisi.',
        ]);

        $data = [
            'mission_id' => $missions->id,
            'module_id' => $modules->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'mascot_id' => $validated['mascot_id'],
            'created_by' => Auth::id(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('materials/images', 'public');
            $data['image'] = $path;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('materials/thumbnails', 'public');
            $data['thumbnail'] = $path;
        }

        Materials::create($data);

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil ditambahkan.');
    }

    /**
     * Show material detail
     */
    public function show(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        // Pastikan material milik mission yang benar
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot', 'createdBy']);

        return Inertia::render('Admin/Modules/Missions/Materials/Show', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
            ],
            'material' => $materials,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        // Pastikan material milik mission yang benar
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot']);
        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Missions/Materials/Edit', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
            ],
            'material' => $materials,
            'mascots' => $mascots,
        ]);
    }

    /**
     * Update material
     */
    public function update(Learning_modules $modules, Missions $missions, Materials $materials, Request $request)
    {
        // Pastikan material milik mission yang benar
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'mascot_id' => 'nullable|exists:mascots,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|boolean',
            'remove_thumbnail' => 'nullable|boolean',
        ], [
            'title.required' => 'Judul material wajib diisi.',
            'content.required' => 'Konten material wajib diisi.',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'mascot_id' => $validated['mascot_id'],
        ];

        // Handle remove image
        if ($request->input('remove_image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
                $data['image'] = null;
            }
        }

        // Handle remove thumbnail
        if ($request->input('remove_thumbnail')) {
            if ($materials->thumbnail) {
                Storage::disk('public')->delete($materials->thumbnail);
                $data['thumbnail'] = null;
            }
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
            }
            $path = $request->file('image')->store('materials/images', 'public');
            $data['image'] = $path;
        }

        // Handle new thumbnail upload
        if ($request->hasFile('thumbnail')) {
            if ($materials->thumbnail) {
                Storage::disk('public')->delete($materials->thumbnail);
            }
            $path = $request->file('thumbnail')->store('materials/thumbnails', 'public');
            $data['thumbnail'] = $path;
        }

        $materials->update($data);

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil diperbarui.');
    }

    /**
     * Delete material
     */
    public function destroy(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        // Pastikan material milik mission yang benar
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        // Delete images if exists
        if ($materials->image) {
            Storage::disk('public')->delete($materials->image);
        }
        if ($materials->thumbnail) {
            Storage::disk('public')->delete($materials->thumbnail);
        }

        $materials->delete();

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil dihapus.');
    }
}
