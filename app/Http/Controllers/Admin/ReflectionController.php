<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Scientific_reflections;
use App\Models\Reflection_questions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReflectionController extends Controller
{
    public function create(Learning_modules $modules, Missions $missions)
    {
        return Inertia::render('Admin/Modules/Reflections/Create', [
            'module' => $modules,
            'mission' => $missions,
        ]);
    }

    public function store(Request $request, Learning_modules $modules, Missions $missions)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'mascot_left_text' => 'required|string',
            'mascot_right_text' => 'required|string',
            'flowchart_data' => 'nullable|array',
            'flowchart_data.*.title' => 'required|string',
            'flowchart_data.*.fallback_icon' => 'nullable|string',
            'flowchart_data.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
        ]);

        $flowchartData = $request->input('flowchart_data', []);

        if ($request->hasFile('flowchart_data')) {
            foreach ($request->file('flowchart_data', []) as $index => $fileData) {
                if (isset($fileData['image']) && $fileData['image']->isValid()) {
                    $path = $fileData['image']->store('reflections', 'public');
                    $flowchartData[$index]['image'] = $path;
                }
            }
        }

        DB::transaction(function () use ($missions, $validated, $flowchartData) {
            $reflection = Scientific_reflections::create([
                'mission_id' => $missions->id,
                'title' => $validated['title'],
                'mascot_left_text' => $validated['mascot_left_text'],
                'mascot_right_text' => $validated['mascot_right_text'],
                'flowchart_data' => $flowchartData,
            ]);

            if (!empty($validated['questions'])) {
                foreach ($validated['questions'] as $index => $q) {
                    Reflection_questions::create([
                        'reflection_id' => $reflection->id,
                        'question_text' => $q['question_text'],
                        'order_number' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Refleksi berhasil ditambahkan.');
    }

    public function show(Learning_modules $modules, Missions $missions, Scientific_reflections $reflections)
    {
        $reflections->load('questions');
        return Inertia::render('Admin/Modules/Reflections/Show', [
            'module' => $modules,
            'mission' => $missions,
            'reflection' => $reflections,
        ]);
    }

    public function edit(Learning_modules $modules, Missions $missions, Scientific_reflections $reflections)
    {
        $reflections->load('questions');
        return Inertia::render('Admin/Modules/Reflections/Edit', [
            'module' => $modules,
            'mission' => $missions,
            'reflection' => $reflections,
        ]);
    }

    public function update(Request $request, Learning_modules $modules, Missions $missions, Scientific_reflections $reflections)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'mascot_left_text' => 'required|string',
            'mascot_right_text' => 'required|string',
            'flowchart_data' => 'nullable|array',
            'flowchart_data.*.title' => 'required|string',
            'flowchart_data.*.fallback_icon' => 'nullable|string',
            'flowchart_data.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'flowchart_data.*.existing_image' => 'nullable|string',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
        ]);

        $flowchartData = $request->input('flowchart_data', []);

        if ($request->has('flowchart_data')) {
            foreach ($flowchartData as $index => $item) {
                // Preserve existing image if available
                if (isset($item['existing_image'])) {
                    $flowchartData[$index]['image'] = $item['existing_image'];
                    unset($flowchartData[$index]['existing_image']);
                }

                if ($request->hasFile("flowchart_data.{$index}.image")) {
                    $file = $request->file("flowchart_data.{$index}.image");
                    if ($file && $file->isValid()) {
                        // Delete old image if exists
                        if (isset($flowchartData[$index]['image']) && Storage::disk('public')->exists($flowchartData[$index]['image'])) {
                            Storage::disk('public')->delete($flowchartData[$index]['image']);
                        }
                        $path = $file->store('reflections', 'public');
                        $flowchartData[$index]['image'] = $path;
                    }
                }
            }
        }

        DB::transaction(function () use ($reflections, $validated, $flowchartData) {
            $reflections->update([
                'title' => $validated['title'],
                'mascot_left_text' => $validated['mascot_left_text'],
                'mascot_right_text' => $validated['mascot_right_text'],
                'flowchart_data' => $flowchartData,
            ]);

            // Recreate questions
            $reflections->questions()->delete();
            if (!empty($validated['questions'])) {
                foreach ($validated['questions'] as $index => $q) {
                    Reflection_questions::create([
                        'reflection_id' => $reflections->id,
                        'question_text' => $q['question_text'],
                        'order_number' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('admin.modules.missions.reflections.show', [$modules->id, $missions->id, $reflections->id])
            ->with('success', 'Refleksi berhasil diperbarui.');
    }

    public function destroy(Learning_modules $modules, Missions $missions, Scientific_reflections $reflections)
    {
        // Delete images
        if (is_array($reflections->flowchart_data)) {
            foreach ($reflections->flowchart_data as $item) {
                if (isset($item['image']) && Storage::disk('public')->exists($item['image'])) {
                    Storage::disk('public')->delete($item['image']);
                }
            }
        }

        $reflections->delete();

        return redirect()->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Refleksi berhasil dihapus.');
    }
}
