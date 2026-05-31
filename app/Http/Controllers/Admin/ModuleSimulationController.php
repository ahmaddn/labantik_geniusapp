<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Simulation_scenarios;
use App\Models\Simulation_scenario_options;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleSimulationController extends Controller
{
    /**
     * Show the simulation configuration page for a module.
     */
    public function edit(Learning_modules $modules)
    {
        $modules->load([
            'simulation_scenarios.options',
        ]);

        return Inertia::render('Admin/Modules/Simulation/Edit', [
            'module' => $modules,
            'configs' => [
                'scenarios' => $modules->simulation_scenarios,
            ]
        ]);
    }

    /**
     * Update the simulation configuration for a module.
     */
    public function update(Request $request, Learning_modules $modules)
    {
        $type = $request->input('config_type');

        if ($type === 'scenario') {
            $this->updateScenario($request, $modules);
            return back()->with('success', 'Studi Kasus berhasil disimpan.');
        }

        return back()->with('error', 'Tipe konfigurasi tidak valid.');
    }

    private function updateScenario(Request $request, Learning_modules $module)
    {
        // For Scenario & Case Study (Studi Kasus) at Module Level
        $validated = $request->validate([
            'scenarios' => 'nullable|array',
            'scenarios.*.id' => 'nullable|string',
            'scenarios.*.context' => 'required|string',
            'scenarios.*.correct_option' => 'nullable|string',
            'scenarios.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            'scenarios.*.remove_image' => 'nullable|boolean',
            'scenarios.*.options' => 'nullable|array',
            'scenarios.*.options.*.id' => 'nullable|string',
            'scenarios.*.options.*.label' => 'required|string',
            'scenarios.*.options.*.text' => 'required|string',
            'scenarios.*.options.*.feedback' => 'nullable|string',
        ]);

        $existingIds = [];
        if (!empty($validated['scenarios'])) {
            foreach ($validated['scenarios'] as $index => $sData) {
                $sId = $sData['id'] ?? (string) Str::uuid();
                $existingIds[] = $sId;

                $s = Simulation_scenarios::firstOrNew(['id' => $sId]);
                $s->module_id = $module->id;
                $s->mission_id = null; // Ensure it is explicitly null
                $s->context = $sData['context'];
                $s->correct_option = $sData['correct_option'] ?? null;

                if (!empty($sData['remove_image']) && $s->image) {
                    Storage::disk('public')->delete($s->image);
                    $s->image = null;
                }
                if ($request->hasFile("scenarios.{$index}.image")) {
                    if ($s->image) Storage::disk('public')->delete($s->image);
                    $s->image = $request->file("scenarios.{$index}.image")->store('simulations/scenarios', 'public');
                }

                $s->save();

                // Options
                $existingOptIds = [];
                if (!empty($sData['options'])) {
                    foreach ($sData['options'] as $oData) {
                        $oId = $oData['id'] ?? (string) Str::uuid();
                        $existingOptIds[] = $oId;

                        $o = Simulation_scenario_options::firstOrNew(['id' => $oId]);
                        $o->simulation_scenario_id = $s->id;
                        $o->label = $oData['label'];
                        $o->text = $oData['text'];
                        $o->feedback = $oData['feedback'] ?? null;
                        $o->save();
                    }
                }

                Simulation_scenario_options::where('simulation_scenario_id', $s->id)->whereNotIn('id', $existingOptIds)->delete();
            }
        }

        $toDelete = Simulation_scenarios::where('module_id', $module->id)->whereNotIn('id', $existingIds)->get();
        foreach ($toDelete as $item) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $item->delete();
        }
    }
}
