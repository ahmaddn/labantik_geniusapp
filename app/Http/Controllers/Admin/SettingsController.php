<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\GlobalBgm;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([
                'platform_name' => 'GENIUSS Web Education',
                'bgm_enabled' => true,
            ]);
        }
        
        $bgms = GlobalBgm::latest()->get()->map(function($bgm) {
            return [
                'id' => $bgm->id,
                'name' => $bgm->name,
                'file_path' => $bgm->file_path,
                'url' => Storage::url($bgm->file_path)
            ];
        });
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'bgms' => $bgms
        ]);
    }

    public function update(Request $request)
    {
        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([]);
        }

        $validated = $request->validate([
            'platform_name' => 'nullable|string|max:255',
            'platform_subtitle' => 'nullable|string|max:255',
            'platform_logo' => 'nullable|image|max:2048',
            'platform_mascot' => 'nullable|image|max:5120',
            'platform_mascot_pose' => 'nullable|string|max:255',
            'platform_mascot_dialog' => 'nullable|array',
            'platform_mascot_dialog.*' => 'nullable|string|max:255',
            'bgm_enabled' => 'boolean',
        ]);

        if ($request->hasFile('platform_logo')) {
            if ($settings->platform_logo) {
                Storage::disk('public')->delete($settings->platform_logo);
            }
            $validated['platform_logo'] = $request->file('platform_logo')->store('settings', 'public');
        }

        if ($request->hasFile('platform_mascot')) {
            if ($settings->platform_mascot) {
                Storage::disk('public')->delete($settings->platform_mascot);
            }
            $validated['platform_mascot'] = $request->file('platform_mascot')->store('settings/mascots', 'public');
        }

        $settings->update($validated);

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
    
    public function deleteLogo()
    {
        $settings = Settings::first();
        if ($settings && $settings->platform_logo) {
            Storage::disk('public')->delete($settings->platform_logo);
            $settings->update(['platform_logo' => null]);
        }
        return back()->with('success', 'Logo berhasil dihapus.');
    }

    public function deleteMascot()
    {
        $settings = Settings::first();
        if ($settings && $settings->platform_mascot) {
            Storage::disk('public')->delete($settings->platform_mascot);
            $settings->update(['platform_mascot' => null]);
        }
        return back()->with('success', 'Maskot berhasil dihapus.');
    }

    public function uploadBgm(Request $request)
    {
        $request->validate([
            'bgm_file' => 'required|file|mimes:mp3,ogg,wav|max:10240',
        ]);

        $file = $request->file('bgm_file');
        $name = $file->getClientOriginalName();
        $path = $file->store('settings/bgms', 'public');

        GlobalBgm::create([
            'name' => $name,
            'file_path' => $path,
        ]);

        return back()->with('success', 'BGM berhasil ditambahkan ke galeri.');
    }

    public function deleteBgm(GlobalBgm $bgm)
    {
        Storage::disk('public')->delete($bgm->file_path);
        
        $settings = Settings::first();
        if ($settings && $settings->bgm_file === $bgm->file_path) {
            $settings->update(['bgm_file' => null]);
        }
        
        $bgm->delete();

        return back()->with('success', 'BGM berhasil dihapus.');
    }

    public function setActiveBgm(GlobalBgm $bgm)
    {
        $settings = Settings::first();
        if (!$settings) {
            $settings = Settings::create([]);
        }
        $settings->update(['bgm_file' => $bgm->file_path]);

        return back()->with('success', 'BGM aktif berhasil diubah.');
    }

    public function clearActiveBgm()
    {
        $settings = Settings::first();
        if ($settings) {
            $settings->update(['bgm_file' => null]);
        }

        return back()->with('success', 'BGM aktif berhasil dikosongkan.');
    }
}
