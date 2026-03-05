<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Templates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TemplatesController extends Controller
{
    public function index()
    {
        $templates = Templates::withCount(['backgrounds', 'mascots'])
            ->latest()
            ->paginate(12)
            ->through(fn($t) => [
                'id'                 => $t->id,
                'name'               => $t->name,
                'backsound'          => $t->backsound ? Storage::url($t->backsound) : null,
                'backgrounds_count'  => $t->backgrounds_count,
                'mascots_count'      => $t->mascots_count,
            ]);

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'backsound'       => 'nullable|mimes:mp3,wav,ogg,aac|max:20480',
            'background'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'background_name' => 'nullable|string|max:255',
            'mascots.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mascot_pose.*'   => 'nullable|string|max:255',
        ], [
            'name.required'       => 'Nama template wajib diisi.',
            'backsound.mimes'     => 'Format audio harus mp3, wav, ogg, atau aac.',
            'background.image'    => 'Background harus berupa gambar.',
            'mascots.*.image'     => 'Maskot harus berupa gambar.',
        ]);

        $template = Templates::create([
            'name'      => $request->name,
            'backsound' => $request->hasFile('backsound')
                ? $request->file('backsound')->store('templates/backsounds', 'public')
                : null,
        ]);

        // Background — single
        if ($request->hasFile('background')) {
            $template->backgrounds()->create([
                'name' => $request->input('background_name') ?? 'Background',
                'path' => $request->file('background')->store('templates/backgrounds', 'public'),
            ]);
        }

        // Mascots
        if ($request->hasFile('mascots')) {
            foreach ($request->file('mascots') as $i => $file) {
                $template->mascots()->create([
                    'name_pose' => $request->input("mascot_pose.{$i}") ?? 'Pose',
                    'path'      => $file->store('templates/mascots', 'public'),
                ]);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil ditambahkan.');
    }

    public function show(Templates $template)
    {
        $template->load(['backgrounds', 'mascots']);

        return Inertia::render('Admin/Templates/Show', [
            'template' => [
                'id'          => $template->id,
                'name'        => $template->name,
                'backsound'   => $template->backsound ? Storage::url($template->backsound) : null,
                'backgrounds' => $template->backgrounds->map(fn($b) => [
                    'id'   => $b->id,
                    'name' => $b->name,
                    'url'  => Storage::url($b->path),
                ]),
                'mascots' => $template->mascots->map(fn($m) => [
                    'id'        => $m->id,
                    'name_pose' => $m->name_pose,
                    'url'       => Storage::url($m->path),
                ]),
            ],
        ]);
    }

    public function update(Request $request, Templates $template)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'backsound'       => 'nullable|mimes:mp3,wav,ogg,aac|max:20480',
            'background'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'background_name' => 'nullable|string|max:255',
            'mascots.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mascot_pose.*'   => 'nullable|string|max:255',
        ]);

        $template->update(['name' => $request->name]);

        // Update backsound jika ada file baru
        if ($request->hasFile('backsound')) {
            if ($template->backsound) {
                Storage::disk('public')->delete($template->backsound);
            }
            $template->update([
                'backsound' => $request->file('backsound')->store('templates/backsounds', 'public'),
            ]);
        }

        // Ganti background (hapus lama, simpan baru)
        if ($request->hasFile('background')) {
            foreach ($template->backgrounds as $old) {
                Storage::disk('public')->delete($old->path);
                $old->delete();
            }
            $template->backgrounds()->create([
                'name' => $request->input('background_name') ?? 'Background',
                'path' => $request->file('background')->store('templates/backgrounds', 'public'),
            ]);
        }

        // Tambah maskot baru
        if ($request->hasFile('mascots')) {
            foreach ($request->file('mascots') as $i => $file) {
                $template->mascots()->create([
                    'name_pose' => $request->input("mascot_pose.{$i}") ?? 'Pose',
                    'path'      => $file->store('templates/mascots', 'public'),
                ]);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Templates $template)
    {
        // Hapus file backsound
        if ($template->backsound) {
            Storage::disk('public')->delete($template->backsound);
        }

        // Hapus file background
        foreach ($template->backgrounds as $bg) {
            Storage::disk('public')->delete($bg->path);
        }

        // Hapus file mascot
        foreach ($template->mascots as $mascot) {
            Storage::disk('public')->delete($mascot->path);
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }
}
