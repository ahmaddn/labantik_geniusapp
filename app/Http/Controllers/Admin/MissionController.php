<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionController extends Controller
{
    /**
     * Store newly created missions
     */
    public function store(Learning_modules $modules, Request $request)
    {
        $validated = $request->validate([
            'mission_count' => 'required|integer|min:1|max:20',
        ], [
            'mission_count.required' => 'Jumlah mission wajib diisi.',
            'mission_count.min' => 'Minimal harus ada 1 mission.',
            'mission_count.max' => 'Maksimal 20 mission sekaligus.',
        ]);

        $count = $validated['mission_count'];
        $lastOrder = Missions::where('module_id', $modules->id)
            ->max('order_number') ?? 0;

        for ($i = 1; $i <= $count; $i++) {
            $missionNumber = $lastOrder + $i;
            Missions::create([
                'module_id' => $modules->id,
                'name' => "Misi {$missionNumber}",
                'order_number' => $lastOrder + $i,
            ]);
        }

        return redirect()
            ->route('admin.modules.show', $modules->id)
            ->with('success', "{$count} Mission berhasil ditambahkan.");
    }

    /**
     * Show mission detail with materials and quizzes
     */
    public function show(Learning_modules $modules, Missions $missions)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        // Load materials dengan relasi
        $materials = $missions->materials()
            ->with(['createdBy', 'mascot'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'content' => $material->content,
                    'image' => $material->image,
                    'thumbnail' => $material->thumbnail,
                    'created_at' => $material->created_at,
                    'created_by' => $material->createdBy ? $material->createdBy->name : null,
                    'mascot' => $material->mascot ? [
                        'id' => $material->mascot->id,
                        'name' => $material->mascot->name,
                    ] : null,
                ];
            });

        // Load quizzes dengan count pertanyaan
        $quizzes = $missions->quizzes()
            ->withCount('questions')
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'type' => $quiz->type,
                    'time_limit' => $quiz->time_limit,
                    'category' => $quiz->category,
                    'questions_count' => $quiz->questions_count,
                    'created_at' => $quiz->created_at,
                    'created_by' => $quiz->createdBy ? $quiz->createdBy->name : null,
                ];
            });

        // Load simulations
        $sliders = $missions->simulation_sliders()->get()->map(function ($s) { 
            return ['id' => $s->id, 'type' => 'simulation_slider', 'title' => 'Simulasi Slider', 'order_number' => $s->order_number, 'created_at' => $s->created_at]; 
        });
        $comparisons = $missions->simulation_comparisons()->get()->map(function ($s) { 
            return ['id' => $s->id, 'type' => 'simulation_comparison', 'title' => 'Simulasi Perbandingan', 'order_number' => $s->order_number, 'created_at' => $s->created_at]; 
        });
        $clickables = $missions->simulation_clickable_objects()->get()->map(function ($s) { 
            return ['id' => $s->id, 'type' => 'simulation_clickable_object', 'title' => 'Simulasi Objek Klik (' . $s->name . ')', 'order_number' => $s->order_number, 'created_at' => $s->created_at]; 
        });
        $simulations = collect([])->concat($sliders)->concat($comparisons)->concat($clickables)->values();

        return Inertia::render('Admin/Modules/Missions/Show', [
            'module' => [
                'id' => $modules->id,
                'name' => $modules->name,
                'description' => $modules->description,
                'title' => $modules->name, // Untuk breadcrumb
            ],
            'mission' => [
                'id' => $missions->id,
                'name' => $missions->name,
                'order_number' => $missions->order_number,
                'description' => $missions->description ?? null,
            ],
            'materials' => $materials,
            'quizzes' => $quizzes,
            'simulations' => $simulations,
        ]);
    }

    /**
     * Update mission
     */
    public function update(Learning_modules $modules, Missions $missions, Request $request)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order_number' => 'nullable|integer|min:1',
            'conclusion_speech' => 'nullable|string|max:255',
            'conclusion_body' => 'nullable|string',
        ], [
            'name.required' => 'Nama mission wajib diisi.',
        ]);

        $missions->update([
            'name' => $validated['name'],
            'order_number' => $validated['order_number'] ?? $missions->order_number,
            'conclusion_speech' => $validated['conclusion_speech'] ?? null,
            'conclusion_body' => $validated['conclusion_body'] ?? null,
        ]);

        return redirect()
            ->route('admin.modules.show', $modules->id)
            ->with('success', 'Mission berhasil diperbarui.');
    }

    /**
     * Delete mission
     */
    public function destroy(Learning_modules $modules, Missions $missions)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $missions->delete();

        return redirect()
            ->route('admin.modules.show', $modules->id)
            ->with('success', 'Mission berhasil dihapus.');
    }

    /**
     * Reorder steps
     */
    public function reorderSteps(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*.id' => 'required|string',
            'steps.*.itemType' => 'required|string',
            'steps.*.order_number' => 'required|integer',
        ]);

        foreach ($validated['steps'] as $step) {
            $model = null;
            switch ($step['itemType']) {
                case 'material':
                    $model = \App\Models\Materials::find($step['id']);
                    break;
                case 'quiz':
                    $model = \App\Models\Quizzes::find($step['id']);
                    break;
                case 'simulation_slider':
                    $model = \App\Models\Simulation_sliders::find($step['id']);
                    break;
                case 'simulation_comparison':
                    $model = \App\Models\Simulation_comparisons::find($step['id']);
                    break;
                case 'simulation_clickable_object':
                    $model = \App\Models\Simulation_clickable_objects::find($step['id']);
                    break;
            }

            if ($model) {
                $model->update(['order_number' => $step['order_number']]);
            }
        }

        return redirect()->back()->with('success', 'Urutan berhasil disimpan.');
    }
}
