<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backgrounds;
use App\Models\Mascots;
use App\Models\Templates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TemplatesController extends Controller
{
    public function index()
    {
        $templates = Templates::withCount(['backgrounds', 'mascots'])->latest()->get();
        return Inertia::render('Admin/Templates/Index', [
            'templates'  => $templates,
        ]);
    }

    public function show(Templates $templates)
    {
        $templates->load(['backgrounds', 'mascots']);

        return Inertia::render('Admin/Templates/Show', [
            'template' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'backsound'          => 'nullable|file|mimes:mp3,wav,m4a|max:10240',
            'backgrounds'        => 'nullable|array',
            'backgrounds.*'      => 'file|mimes:jpg,jpeg,png|max:10240',
            'background_names'   => 'nullable|array',
            'background_names.*' => 'nullable|string|max:255',
            'mascots'            => 'nullable|array',
            'mascots.*'          => 'file|mimes:jpg,jpeg,png|max:10240',
            'mascot_pose'        => 'nullable|array',
            'mascot_pose.*'      => 'nullable|string|max:255',
        ]);

        // Kumpulkan path yang berhasil diupload untuk cleanup kalau transaction gagal
        $uploadedFiles = [];

        try {
            DB::transaction(function () use ($request, &$uploadedFiles) {
                $backsoundPath = null;
                if ($request->hasFile('backsound')) {
                    $backsoundPath = $request->file('backsound')
                        ->store('templates/backsounds', 'public');
                    $uploadedFiles[] = $backsoundPath;
                }

                $template = Templates::create([
                    'id' => Str::uuid(),
                    'name'       => $request->name,
                    'backsound'  => $backsoundPath,
                    'created_by' => Auth::id(),
                ]);

                if ($request->hasFile('backgrounds')) {
                    $bgNames = $request->input('background_names', []);
                    foreach ($request->file('backgrounds') as $i => $file) {
                        $path = $file->store('templates/backgrounds', 'public');
                        $uploadedFiles[] = $path;
                        $template->backgrounds()->create([
                            'name'  => $bgNames[$i],
                            'image' => $path,
                        ]);
                    }
                }

                if ($request->hasFile('mascots')) {
                    $mascotPoses = $request->input('mascot_pose', []);
                    foreach ($request->file('mascots') as $i => $file) {
                        $path = $file->store('templates/mascots', 'public');
                        $uploadedFiles[] = $path;
                        $template->mascots()->create([
                            'name_pose' => $mascotPoses[$i],
                            'image'     => $path,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            // Hapus semua file yang sudah terupload karena DB gagal
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan template: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Template Berhasil Ditambahkan!');
    }

    public function update(Templates $templates, Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'backsound'          => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:10240',
            'backgrounds'        => 'nullable|array',
            'backgrounds.*'      => 'file|mimes:jpg,jpeg,png|max:10240',
            'background_names'   => 'nullable|array',
            'background_names.*' => 'nullable|string|max:255',
            'mascots'            => 'nullable|array',
            'mascots.*'          => 'file|mimes:jpg,jpeg,png|max:10240',
            'mascot_pose'        => 'nullable|array',
            'mascot_pose.*'      => 'nullable|string|max:255',
        ]);

        $uploadedFiles = [];
        $oldBacksound  = $templates->backsound; // simpan path lama untuk dihapus setelah sukses

        try {
            DB::transaction(function () use ($request, $templates, &$uploadedFiles) {
                $backsoundPath = $templates->backsound;
                if ($request->hasFile('backsound')) {
                    $backsoundPath = $request->file('backsound')
                        ->store('templates/backsounds', 'public');
                    $uploadedFiles[] = $backsoundPath;
                }

                $templates->update([
                    'name'      => $request->name,
                    'backsound' => $backsoundPath,
                ]);

                if ($request->hasFile('backgrounds')) {
                    $bgNames = $request->input('background_names', []);
                    foreach ($request->file('backgrounds') as $i => $file) {
                        $path = $file->store('templates/backgrounds', 'public');
                        $uploadedFiles[] = $path;
                        $templates->backgrounds()->create([
                            'name'  => $bgNames[$i] ?? $file->getClientOriginalName(),
                            'image' => $path,
                        ]);
                    }
                }

                if ($request->hasFile('mascots')) {
                    $mascotPoses = $request->input('mascot_pose', []);
                    foreach ($request->file('mascots') as $i => $file) {
                        $path = $file->store('templates/mascots', 'public');
                        $uploadedFiles[] = $path;
                        $templates->mascots()->create([
                            'name_pose' => $mascotPoses[$i] ?? $file->getClientOriginalName(),
                            'image'     => $path,
                        ]);
                    }
                }
            });

            // Transaction sukses → hapus backsound lama kalau diganti
            if ($request->hasFile('backsound') && $oldBacksound) {
                Storage::disk('public')->delete($oldBacksound);
            }
        } catch (\Throwable $e) {
            // Transaction gagal → hapus file baru yang sudah terupload
            foreach ($uploadedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui template: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Templates $templates)
    {
        if ($templates->backsound) Storage::disk('public')->delete($templates->backsound);
        foreach ($templates->backgrounds as $bg) Storage::disk('public')->delete($bg->image);
        foreach ($templates->mascots as $mascot) Storage::disk('public')->delete($mascot->image);

        $templates->delete();

        return redirect()->back()->with('success', 'Template berhasil dihapus.');
    }

    public function destroyBackground(Backgrounds $backgrounds)
    {
        Storage::disk('public')->delete($backgrounds->image);
        $backgrounds->delete();

        return redirect()->back()->with('success', 'Background berhasil dihapus.');
    }

    public function destroyMascot(Mascots $mascots)
    {
        Storage::disk('public')->delete($mascots->image);
        $mascots->delete();

        return redirect()->back()->with('success', 'Maskot berhasil dihapus.');
    }
}
