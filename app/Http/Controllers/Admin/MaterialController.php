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
    public function create(Learning_modules $modules, Missions $missions)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Materials/Create', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'mission' => ['id' => $missions->id, 'name' => $missions->name, 'order_number' => $missions->order_number],
            'mascots' => $mascots,
        ]);
    }

    public function store(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'materials'               => 'required|array|min:1',
            'materials.*.title'       => 'required|string|max:255',
            'materials.*.description' => 'nullable|string',
            'materials.*.content'     => 'required|string',
            'materials.*.mascot_id'   => 'nullable|exists:mascots,id',
            'materials.*.image'       => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv,webm|max:51200',
        ], [
            'materials.*.title.required'   => 'Judul material wajib diisi.',
            'materials.*.content.required' => 'Konten material wajib diisi.',
            'materials.*.image.mimes'      => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, MP4, MOV, AVI, atau WebM.',
            'materials.*.image.max'        => 'Ukuran file maksimal 50MB.',
        ]);

        foreach ($validated['materials'] as $index => $material) {
            $data = [
                'mission_id'  => $missions->id,
                'module_id'   => $modules->id,
                'title'       => $material['title'],
                'description' => $material['description'] ?? null,
                'content'     => $material['content'],
                'mascot_id'   => $material['mascot_id'] ?? null,
                'created_by'  => Auth::id(),
            ];

            // Upload gambar atau video ke kolom image
            if ($request->hasFile("materials.{$index}.image")) {
                $file    = $request->file("materials.{$index}.image");
                $isVideo = in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'wmv', 'webm']);
                $folder  = $isVideo ? 'materials/videos' : 'materials/images';
                $data['image'] = $file->store($folder, 'public');
            }

            Materials::create($data);
        }

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Semua material berhasil ditambahkan.');
    }

    public function show(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot', 'createdBy']);

        return Inertia::render('Admin/Modules/Materials/Show', [
            'module'   => ['id' => $modules->id, 'name' => $modules->name],
            'mission'  => ['id' => $missions->id, 'name' => $missions->name],
            'material' => $materials,
        ]);
    }

    public function edit(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot']);
        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Materials/Edit', [
            'module'   => ['id' => $modules->id, 'name' => $modules->name],
            'mission'  => ['id' => $missions->id, 'name' => $missions->name],
            'material' => $materials,
            'mascots'  => $mascots,
        ]);
    }

    public function update(Learning_modules $modules, Missions $missions, Materials $materials, Request $request)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'required|string',
            'mascot_id'    => 'nullable|exists:mascots,id',
            'image'        => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv,webm|max:51200',
            'remove_image' => 'nullable|boolean',
        ], [
            'title.required'   => 'Judul material wajib diisi.',
            'content.required' => 'Konten material wajib diisi.',
            'image.mimes'      => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, MP4, MOV, AVI, atau WebM.',
            'image.max'        => 'Ukuran file maksimal 50MB.',
        ]);

        $data = [
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'content'     => $validated['content'],
            'mascot_id'   => $validated['mascot_id'],
        ];

        // Handle remove / replace image or video
        if ($request->input('remove_image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
            }
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
            }
            $file    = $request->file('image');
            $isVideo = in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'wmv', 'webm']);
            $folder  = $isVideo ? 'materials/videos' : 'materials/images';
            $data['image'] = $file->store($folder, 'public');
        }

        $materials->update($data);

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        if ($materials->image) {
            Storage::disk('public')->delete($materials->image);
        }

        $materials->delete();

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil dihapus.');
    }
}
