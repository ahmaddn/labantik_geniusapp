<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Simulation_sliders;
use App\Models\Simulation_slider_levels;
use App\Models\Simulation_comparisons;
use App\Models\Simulation_clickable_objects;
use App\Models\Simulation_decisions;
use App\Models\Simulation_decision_options;
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
            'simulation_decisions.options',
        ]);

        $modules->load('template.mascots');

        return Inertia::render('Admin/Modules/Missions/Simulation/Edit', [
            'module' => $modules,
            'mission' => $missions,
            'configs' => [
                'slider' => $missions->simulation_sliders->first(),
                'comparisons' => $missions->simulation_comparisons,
                'clickable_objects' => $missions->simulation_clickable_objects,
                'decisions' => $missions->simulation_decisions,
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
            case 'decision':
                $this->updateDecision($request, $missions);
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
            'levels.*.existing_image' => 'nullable|string',
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

                $imagePath = $levelData['existing_image'] ?? null;

                if (!empty($levelData['remove_image']) && $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                    $imagePath = null;
                }

                if ($request->hasFile("levels.{$index}.image")) {
                    if ($imagePath) {
                        Storage::disk('public')->delete($imagePath);
                    }
                    $imagePath = $request->file("levels.{$index}.image")->store('simulations/sliders', 'public');
                }

                $level->image = $imagePath;
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
            'comparisons.*.explanation' => 'nullable|string',
            'comparisons.*.items' => 'nullable|array',
            'comparisons.*.items.*.toggle_name' => 'nullable|string|max:255',
            'comparisons.*.items.*.label' => 'nullable|string|max:255',
            'comparisons.*.items.*.narration' => 'nullable|string',
            'comparisons.*.items.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'comparisons.*.items.*.remove_image' => 'nullable|boolean',
            'comparisons.*.items.*.existing_image' => 'nullable|string',
        ]);

        $existingIds = [];
        if (!empty($validated['comparisons'])) {
            foreach ($validated['comparisons'] as $index => $compData) {
                $compId = $compData['id'] ?? (string) Str::uuid();
                $existingIds[] = $compId;

                $comp = Simulation_comparisons::firstOrNew(['id' => $compId]);
                $comp->mission_id = $mission->id;
                $comp->title = $validated['page_title'] ?? null;
                $comp->explanation = $compData['explanation'] ?? null;

                $newItems = [];
                if (!empty($compData['items'])) {
                    foreach ($compData['items'] as $itemIndex => $itemData) {
                        $imagePath = $itemData['existing_image'] ?? null;

                        if (!empty($itemData['remove_image']) && $imagePath) {
                            Storage::disk('public')->delete($imagePath);
                            $imagePath = null;
                        }

                        if ($request->hasFile("comparisons.{$index}.items.{$itemIndex}.image")) {
                            if ($imagePath) Storage::disk('public')->delete($imagePath);
                            $imagePath = $request->file("comparisons.{$index}.items.{$itemIndex}.image")->store('simulations/comparisons', 'public');
                        }

                        $newItems[] = [
                            'toggle_name' => $itemData['toggle_name'] ?? null,
                            'label'       => $itemData['label'] ?? null,
                            'narration'   => $itemData['narration'] ?? null,
                            'image'       => $imagePath,
                        ];
                    }
                }
                
                $comp->items = $newItems;
                $comp->save();
            }
        }

        $toDelete = Simulation_comparisons::where('mission_id', $mission->id)->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $item) {
            $items = $item->items ?? [];
            foreach ($items as $itm) {
                if (!empty($itm['image'])) Storage::disk('public')->delete($itm['image']);
            }
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
            'clickables.*.impact_text' => 'nullable|string',
            'clickables.*.is_positive' => 'required|boolean',
            'clickables.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'clickables.*.existing_image' => 'nullable|string',
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
                $c->impact_text = $cData['impact_text'] ?? null;
                $c->is_positive = $cData['is_positive'];

                $imagePath = $cData['existing_image'] ?? null;

                if (!empty($cData['remove_image']) && $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                    $imagePath = null;
                }
                if ($request->hasFile("clickables.{$index}.image")) {
                    if ($imagePath) Storage::disk('public')->delete($imagePath);
                    $imagePath = $request->file("clickables.{$index}.image")->store('simulations/clickables', 'public');
                }

                $c->image = $imagePath;
                $c->save();
            }
        }

        $toDelete = Simulation_clickable_objects::where('mission_id', $mission->id)->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $item) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $item->delete();
        }
    }

    private function updateDecision(Request $request, Missions $mission)
    {
        $validated = $request->validate([
            'decisions' => 'nullable|array',
            'decisions.*.id' => 'nullable|string',
            'decisions.*.title' => 'nullable|string|max:255',
            'decisions.*.initial_state_title' => 'nullable|string|max:255',
            'decisions.*.future_state_title' => 'nullable|string|max:255',
            'decisions.*.initial_state_image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'decisions.*.existing_initial_image' => 'nullable|string',
            'decisions.*.remove_initial_image' => 'nullable|boolean',
            'decisions.*.character_image' => 'nullable|string',
            
            'decisions.*.options' => 'nullable|array',
            'decisions.*.options.*.id' => 'nullable|string',
            'decisions.*.options.*.button_label' => 'nullable|string|max:255',
            'decisions.*.options.*.button_color' => 'nullable|string|max:255',
            'decisions.*.options.*.feedback_message' => 'nullable|string',
            'decisions.*.options.*.future_state_image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'decisions.*.options.*.existing_future_image' => 'nullable|string',
            'decisions.*.options.*.remove_future_image' => 'nullable|boolean',
        ]);

        $existingDecIds = [];
        if (!empty($validated['decisions'])) {
            foreach ($validated['decisions'] as $index => $dData) {
                $dId = $dData['id'] ?? (string) Str::uuid();
                $existingDecIds[] = $dId;

                $dec = Simulation_decisions::firstOrNew(['id' => $dId]);
                $dec->mission_id = $mission->id;
                $dec->title = $dData['title'] ?? null;
                $dec->initial_state_title = $dData['initial_state_title'] ?? null;
                $dec->future_state_title = $dData['future_state_title'] ?? null;

                $initialImagePath = $dData['existing_initial_image'] ?? null;

                // Handle initial state image
                if (!empty($dData['remove_initial_image']) && $initialImagePath) {
                    Storage::disk('public')->delete($initialImagePath);
                    $initialImagePath = null;
                }
                if ($request->hasFile("decisions.{$index}.initial_state_image")) {
                    if ($initialImagePath) Storage::disk('public')->delete($initialImagePath);
                    $initialImagePath = $request->file("decisions.{$index}.initial_state_image")->store('simulations/decisions', 'public');
                }
                $dec->initial_state_image = $initialImagePath;

                // Handle character image (selected from template)
                if (array_key_exists('character_image', $dData)) {
                    $dec->character_image = $dData['character_image'];
                }

                $dec->save();

                $existingOptIds = [];
                if (!empty($dData['options'])) {
                    foreach ($dData['options'] as $optIndex => $oData) {
                        $oId = $oData['id'] ?? (string) Str::uuid();
                        $existingOptIds[] = $oId;

                        $opt = Simulation_decision_options::firstOrNew(['id' => $oId]);
                        $opt->simulation_decision_id = $dec->id;
                        $opt->button_label = $oData['button_label'] ?? null;
                        $opt->button_color = $oData['button_color'] ?? null;
                        $opt->feedback_message = $oData['feedback_message'] ?? null;

                        $futureImagePath = $oData['existing_future_image'] ?? null;

                        if (!empty($oData['remove_future_image']) && $futureImagePath) {
                            Storage::disk('public')->delete($futureImagePath);
                            $futureImagePath = null;
                        }
                        if ($request->hasFile("decisions.{$index}.options.{$optIndex}.future_state_image")) {
                            if ($futureImagePath) Storage::disk('public')->delete($futureImagePath);
                            $futureImagePath = $request->file("decisions.{$index}.options.{$optIndex}.future_state_image")->store('simulations/decisions', 'public');
                        }
                        
                        $opt->future_state_image = $futureImagePath;

                        $opt->save();
                    }
                }

                // Delete removed options
                $optsToDelete = Simulation_decision_options::where('simulation_decision_id', $dec->id)
                    ->whereNotIn('id', $existingOptIds)
                    ->get();
                foreach ($optsToDelete as $optToDelete) {
                    if ($optToDelete->future_state_image) Storage::disk('public')->delete($optToDelete->future_state_image);
                    $optToDelete->delete();
                }
            }
        }

        // Delete removed decisions
        $decsToDelete = Simulation_decisions::where('mission_id', $mission->id)->whereNotIn('id', $existingDecIds)->get();
        foreach ($decsToDelete as $item) {
            $item->delete(); // The model's deleting event will handle the children and files
        }
    }

    public function destroy(Request $request, Learning_modules $modules, Missions $missions, $id)
    {
        $type = $request->query('type');
        switch ($type) {
            case 'simulation_slider':
                Simulation_sliders::where('id', $id)->delete();
                break;
            case 'simulation_comparison':
                Simulation_comparisons::where('id', $id)->delete();
                break;
            case 'simulation_clickable_object':
                Simulation_clickable_objects::where('id', $id)->delete();
                break;
            case 'simulation_decision':
                Simulation_decisions::where('id', $id)->delete();
                break;
        }

        return back()->with('success', 'Simulasi berhasil dihapus.');
    }
}
