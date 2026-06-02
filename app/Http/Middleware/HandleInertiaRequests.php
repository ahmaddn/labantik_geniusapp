<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'siswa', // Tambahkan role jika ada
                ] : null,
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'error' => fn() => $request->session()->get('error'),
                'success' => fn() => $request->session()->get('success'), // ← tambah ini
            ],
            'global_settings' => \App\Models\Settings::first() ? [
                'platform_name' => \App\Models\Settings::first()->platform_name,
                'platform_subtitle' => \App\Models\Settings::first()->platform_subtitle,
                'platform_logo' => \App\Models\Settings::first()->platform_logo ? \Illuminate\Support\Facades\Storage::url(\App\Models\Settings::first()->platform_logo) : null,
                'platform_mascot' => \App\Models\Settings::first()->platform_mascot ? \Illuminate\Support\Facades\Storage::url(\App\Models\Settings::first()->platform_mascot) : null,
                'platform_mascot_pose' => \App\Models\Settings::first()->platform_mascot_pose,
                'platform_mascot_dialog' => \App\Models\Settings::first()->platform_mascot_dialog,
                'bgm_file' => \App\Models\Settings::first()->bgm_file ? \Illuminate\Support\Facades\Storage::url(\App\Models\Settings::first()->bgm_file) : null,
                'bgm_enabled' => (bool) \App\Models\Settings::first()->bgm_enabled,
            ] : null,
        ]);
    }
}
