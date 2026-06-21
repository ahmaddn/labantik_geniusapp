<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mascots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MascotsController extends Controller
{
    public function index()
    {
        $mascots = Mascots::latest()->get()->map(fn($m) => [
            'id' => $m->id,
            'name_pose' => $m->name_pose,
            'image_url' => $m->image ? Storage::url($m->image) : null,
            'created_at' => $m->created_at,
        ]);

        return Inertia::render('Admin/Mascots/Index', [
            'mascots' => $mascots
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_pose' => 'required|string|max:255',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp,gif,svg|max:5048'
        ], [
            'name_pose.required' => 'Nama pose maskot wajib diisi.',
            'image.required' => 'Gambar maskot wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
        ]);

        $imagePath = $request->file('image')->store('mascots', 'public');

        Mascots::create([
            'name_pose' => $validated['name_pose'],
            'image' => $imagePath
        ]);

        return redirect()->back()->with('success', 'Maskot berhasil ditambahkan.');
    }

    public function update(Request $request, Mascots $mascot)
    {
        $validated = $request->validate([
            'name_pose' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp,gif,svg|max:5048'
        ], [
            'name_pose.required' => 'Nama pose maskot wajib diisi.',
            'image.image' => 'File harus berupa gambar.',
        ]);

        $imagePath = $mascot->image;

        if ($request->hasFile('image')) {
            if ($mascot->image && Storage::disk('public')->exists($mascot->image)) {
                Storage::disk('public')->delete($mascot->image);
            }
            $imagePath = $request->file('image')->store('mascots', 'public');
        }

        $mascot->update([
            'name_pose' => $validated['name_pose'],
            'image' => $imagePath
        ]);

        return redirect()->back()->with('success', 'Maskot berhasil diperbarui.');
    }

    public function destroy(Mascots $mascot)
    {
        // Image deletion is handled by boot deleting event in Mascots.php
        $mascot->delete();

        return redirect()->back()->with('success', 'Maskot berhasil dihapus.');
    }
}
