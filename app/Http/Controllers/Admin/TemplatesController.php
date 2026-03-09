<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Templates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'backsound'       => 'nullable|file|extensions:mp3,wav,ogg,aac|max:5120 ',
            'background'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'background_name' => 'nullable|string|max:255',
            'mascots.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mascot_pose.*'   => 'nullable|string|max:255',
        ], [
            'name.required'        => 'Nama template wajib diisi.',
            'backsound.extensions' => 'Format audio harus mp3, wav, ogg, atau aac.',
            'background.image'     => 'Background harus berupa gambar.',
            'mascots.*.image'      => 'Maskot harus berupa gambar.',
        ]);
        $templates = Templates::create([
            'name'      => $request->name,
            'backsound' => $request->hasFile('backsound')
                ? $request->file('backsound')->store('templates/backsounds', 'public')
                : null,
            'created_by' => Auth::id()
        ]);


        // Background — single
        if ($request->hasFile('background')) {
            $templates->backgrounds()->create([
                'name' => $request->input('background_name') ?? 'Background',
                'image' => $request->file('background')->store('templates/backgrounds', 'public'),
            ]);
        }

        // Mascots
        if ($request->hasFile('mascots')) {
            foreach ($request->file('mascots') as $i => $file) {
                $templates->mascots()->create([
                    'name_pose' => $request->input("mascot_pose.{$i}") ?? 'Pose',
                    'image'      => $file->store('templates/mascots', 'public'),
                ]);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil ditambahkan.');
    }

    public function show(Templates $templates)
    {
        $templates->load(['backgrounds', 'mascots']);

        return Inertia::render('Admin/Templates/Show', [
            'template' => [
                'id'          => $templates->id,
                'name'        => $templates->name,
                'backsound'   => $templates->backsound, // ← hapus Storage::url()
                'backgrounds' => $templates->backgrounds->map(fn($b) => [
                    'id'    => $b->id,
                    'name'  => $b->name,
                    'image' => $b->image, // ← hapus Storage::url(), kirim path mentah
                ]),
                'mascots' => $templates->mascots->map(fn($m) => [
                    'id'        => $m->id,
                    'name_pose' => $m->name_pose,
                    'image'     => $m->image, // ← hapus Storage::url()
                ]),
            ],
        ]);
    }

    public function update(Request $request, Templates $templates)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'backsound'       => 'nullable|file|extensions:mp3,wav,ogg,aac|max:5120 ',
            'background'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'background_name' => 'nullable|string|max:255',
            'mascots.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mascot_pose.*'   => 'nullable|string|max:255',
        ]);

        $templates->update(['name' => $request->name]);


        // Update backsound jika ada file baru
        if ($request->hasFile('backsound')) {
            if ($templates->backsound) {
                Storage::disk('public')->delete($templates->backsound);
            }
            $templates->update([
                'backsound' => $request->file('backsound')->store('templates/backsounds', 'public'),
            ]);
        }

        // Ganti background (hapus lama, simpan baru)
        if ($request->hasFile('background')) {
            foreach ($templates->backgrounds as $old) {
                Storage::disk('public')->delete($old->image);
                $old->delete();
            }
            $templates->backgrounds()->create([
                'name' => $request->input('background_name') ?? 'Background',
                'image' => $request->file('background')->store('templates/backgrounds', 'public'),
            ]);
        }

        // Tambah maskot baru
        if ($request->hasFile('mascots')) {
            foreach ($request->file('mascots') as $i => $file) {
                $templates->mascots()->create([
                    'name_pose' => $request->input("mascot_pose.{$i}") ?? 'Pose',
                    'image'      => $file->store('templates/mascots', 'public'),
                ]);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Templates $templates)
    {

        // Hapus file backsound
        if ($templates->backsound) {
            Storage::disk('public')->delete($templates->backsound);
        }

        // Hapus file background
        foreach ($templates->backgrounds as $bg) {
            Storage::disk('public')->delete($bg->image);
        }

        // Hapus file mascot
        foreach ($templates->mascots as $mascot) {
            Storage::disk('public')->delete($mascot->image);
        }

        $templates->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }
}
