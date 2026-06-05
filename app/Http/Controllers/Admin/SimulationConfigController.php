<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Simulation_sliders;
use App\Models\Simulation_slider_levels;
use App\Models\Simulation_comparisons;
use App\Models\Simulation_clickable_objects;
use App\Models\Simulation_solutions;
use App\Models\Simulation_solution_options;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SimulationConfigController extends Controller
{
    /**
     * Show the simulation configuration page for a mission.
     */
    public function edit(Learning_modules $modules, Missions $missions)
    {
        // Pastikan mission milik module yang benar
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $missions->load([
            'simulation_sliders.levels',
            'simulation_comparisons',
            'simulation_clickable_objects',
            'simulation_solutions.options',
        ]);

        return Inertia::render('Admin/Modules/Missions/Simulation/Edit', [
            'module' => $modules,
            'mission' => $missions,
            'configs' => [
                'slider' => $missions->simulation_sliders->first(),
                'comparisons' => $missions->simulation_comparisons,
                'clickable_objects' => $missions->simulation_clickable_objects,
                'solutions' => $missions->simulation_solutions->first(),
            ]
        ]);
    }

    /**
     * Update the simulation configuration.
     * We use a 'config_type' field to determine which type of simulation to save.
     */
    public function update(Request $request, Learning_modules $modules, Missions $missions)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $type = $request->input('config_type');

        switch ($type) {
            case 'slider':
                $this->updateSlider($request, $missions);
                break;
            case 'comparison':
                $this->updateComparison($request, $missions);
                break;
            case 'clickable':
                $this->updateClickable($request, $missions);
                break;
            default:
                return back()->with('error', 'Tipe konfigurasi tidak valid.');
        }

        return back()->with('success', 'Konfigurasi simulasi berhasil disimpan.');
    }

    private function updateSlider(Request $request, Missions $mission)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'variables' => 'nullable|array',
            'variables.*.name' => 'nullable|string|max:255',
            'variables.*.min_label' => 'nullable|string|max:255',
            'variables.*.max_label' => 'nullable|string|max:255',
            'conclusion_text' => 'nullable|string',
            'case_study_scenario' => 'nullable|string',
            'case_study_options' => 'nullable|array',
            'case_study_answer' => 'nullable|string',
            'case_study_feedback' => 'nullable|string',
            'levels' => 'nullable|array',
            'levels.*.id' => 'nullable|string',
            'levels.*.level_name' => 'required|string|max:255',
            'levels.*.narration' => 'nullable|string',
            'levels.*.metric_value' => 'nullable|string',
            'levels.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'levels.*.remove_image' => 'nullable|boolean',
        ]);

        $slider = Simulation_sliders::firstOrCreate(
            ['mission_id' => $mission->id],
            [
                'id' => (string) Str::uuid(),
                'title' => $validated['title'] ?? null,
                'variables' => $validated['variables'] ?? [],
                'conclusion_text' => $validated['conclusion_text'] ?? null,
                'case_study_scenario' => $validated['case_study_scenario'] ?? null,
                'case_study_options' => isset($validated['case_study_options']) ? json_encode($validated['case_study_options']) : null,
                'case_study_answer' => $validated['case_study_answer'] ?? null,
                'case_study_feedback' => $validated['case_study_feedback'] ?? null,
            ]
        );

        // Update if it existed
        $slider->update([
            'title' => $validated['title'] ?? null,
            'variables' => $validated['variables'] ?? [],
            'conclusion_text' => $validated['conclusion_text'] ?? null,
            'case_study_scenario' => $validated['case_study_scenario'] ?? null,
            'case_study_options' => isset($validated['case_study_options']) ? json_encode($validated['case_study_options']) : null,
            'case_study_answer' => $validated['case_study_answer'] ?? null,
            'case_study_feedback' => $validated['case_study_feedback'] ?? null,
        ]);

        // Process levels
        $existingLevelIds = [];
        if (!empty($validated['levels'])) {
            foreach ($validated['levels'] as $index => $levelData) {
                $levelId = $levelData['id'] ?? (string) Str::uuid();
                $existingLevelIds[] = $levelId;

                $level = Simulation_slider_levels::firstOrNew(['id' => $levelId]);
                $level->simulation_slider_id = $slider->id;
                $level->level_name = $levelData['level_name'];
                $level->narration = $levelData['narration'] ?? null;
                $level->metric_value = $levelData['metric_value'] ?? null;

                if (!empty($levelData['remove_image']) && $level->image) {
                    Storage::disk('public')->delete($level->image);
                    $level->image = null;
                }

                if ($request->hasFile("levels.{$index}.image")) {
                    if ($level->image) {
                        Storage::disk('public')->delete($level->image);
                    }
                    $path = $request->file("levels.{$index}.image")->store('simulations/sliders', 'public');
                    $level->image = $path;
                }

                $level->save();
            }
        }

        // Delete removed levels
        $levelsToDelete = Simulation_slider_levels::where('simulation_slider_id', $slider->id)
            ->whereNotIn('id', $existingLevelIds)
            ->get();

        foreach ($levelsToDelete as $lvl) {
            if ($lvl->image) Storage::disk('public')->delete($lvl->image);
            $lvl->delete();
        }
    }

    private function updateComparison(Request $request, Missions $mission)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'comparisons' => 'nullable|array',
            'comparisons.*.id' => 'nullable|string',
            'comparisons.*.left_label' => 'nullable|string|max:255',
            'comparisons.*.right_label' => 'nullable|string|max:255',
            'comparisons.*.left_narration' => 'nullable|string',
            'comparisons.*.right_narration' => 'nullable|string',
            'comparisons.*.explanation' => 'nullable|string',
            'comparisons.*.left_image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'comparisons.*.right_image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'comparisons.*.remove_left_image' => 'nullable|boolean',
            'comparisons.*.remove_right_image' => 'nullable|boolean',
        ]);

        $existingIds = [];
        if (!empty($validated['comparisons'])) {
            foreach ($validated['comparisons'] as $index => $compData) {
                $compId = $compData['id'] ?? (string) Str::uuid();
                $existingIds[] = $compId;

                $comp = Simulation_comparisons::firstOrNew(['id' => $compId]);
                $comp->mission_id = $mission->id;
                $comp->title = $validated['page_title'] ?? null;
                $comp->left_label = $compData['left_label'] ?? null;
                $comp->right_label = $compData['right_label'] ?? null;
                $comp->left_narration = $compData['left_narration'] ?? null;
                $comp->right_narration = $compData['right_narration'] ?? null;
                $comp->explanation = $compData['explanation'] ?? null;

                if (!empty($compData['remove_left_image']) && $comp->left_image) {
                    Storage::disk('public')->delete($comp->left_image);
                    $comp->left_image = null;
                }
                if ($request->hasFile("comparisons.{$index}.left_image")) {
                    if ($comp->left_image) Storage::disk('public')->delete($comp->left_image);
                    $comp->left_image = $request->file("comparisons.{$index}.left_image")->store('simulations/comparisons', 'public');
                }

                if (!empty($compData['remove_right_image']) && $comp->right_image) {
                    Storage::disk('public')->delete($comp->right_image);
                    $comp->right_image = null;
                }
                if ($request->hasFile("comparisons.{$index}.right_image")) {
                    if ($comp->right_image) Storage::disk('public')->delete($comp->right_image);
                    $comp->right_image = $request->file("comparisons.{$index}.right_image")->store('simulations/comparisons', 'public');
                }

                $comp->save();
            }
        }

        $toDelete = Simulation_comparisons::where('mission_id', $mission->id)->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $item) {
            if ($item->left_image) Storage::disk('public')->delete($item->left_image);
            if ($item->right_image) Storage::disk('public')->delete($item->right_image);
            $item->delete();
        }
    }

    private function updateClickable(Request $request, Missions $mission)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'clickables' => 'nullable|array',
            'clickables.*.id' => 'nullable|string',
            'clickables.*.name' => 'required|string|max:255',
            'clickables.*.pos_x' => 'nullable|string|max:50',
            'clickables.*.pos_y' => 'nullable|string|max:50',
            'clickables.*.impact_text' => 'nullable|string',
            'clickables.*.is_positive' => 'required|boolean',
            'clickables.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'clickables.*.remove_image' => 'nullable|boolean',
        ]);

        $existingIds = [];
        if (!empty($validated['clickables'])) {
            foreach ($validated['clickables'] as $index => $cData) {
                $cId = $cData['id'] ?? (string) Str::uuid();
                $existingIds[] = $cId;

                $c = Simulation_clickable_objects::firstOrNew(['id' => $cId]);
                $c->mission_id = $mission->id;
                $c->title = $validated['page_title'] ?? null;
                $c->name = $cData['name'];
                $c->pos_x = $cData['pos_x'] ?? null;
                $c->pos_y = $cData['pos_y'] ?? null;
                $c->impact_text = $cData['impact_text'] ?? null;
                $c->is_positive = $cData['is_positive'];

                if (!empty($cData['remove_image']) && $c->image) {
                    Storage::disk('public')->delete($c->image);
                    $c->image = null;
                }
                if ($request->hasFile("clickables.{$index}.image")) {
                    if ($c->image) Storage::disk('public')->delete($c->image);
                    $c->image = $request->file("clickables.{$index}.image")->store('simulations/clickables', 'public');
                }

                $c->save();
            }
        }

        $toDelete = Simulation_clickable_objects::where('mission_id', $mission->id)->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $item) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $item->delete();
        }
    }
}
