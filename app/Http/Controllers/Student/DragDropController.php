<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Backgrounds;
use App\Models\Drag_drop_groups;
use App\Models\Drag_drop_items;
use App\Models\Mascots;
use App\Models\Missions;
use App\Models\Learning_modules;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DragDropController extends Controller
{
    public function show(Request $request, Missions $mission)
    {
        $module = Learning_modules::find($mission->module_id);

        $groups = Drag_drop_groups::where('question_id', $mission->id)
            ->orderBy('id')->get()
            ->map(fn($g) => ['id' => $g->id, 'group_name' => $g->group_name]);

        $items = Drag_drop_items::where('question_id', $mission->id)
            ->orderBy('id')->get()
            ->map(fn($i) => [
                'id'               => $i->id,
                'item_text'        => $i->item_text,
                'item_image'       => $i->item_image,
                'correct_group_id' => $i->correct_group_id,
            ]);

        $mascot     = Mascots::first();
        $background = Backgrounds::first();

        return Inertia::render('Playground/Mission/Drag_drop', [
            'mission'    => ['id' => $mission->id, 'title' => $mission->title],
            'module'     => $module ? ['id' => $module->id, 'title' => $module->title] : null,
            'groups'     => $groups,
            'items'      => $items,
            'mascot'     => $mascot     ? ['id' => $mascot->id,     'name' => $mascot->name ?? 'Teman Belajar', 'image' => $mascot->image]     : null,
            'background' => $background ? ['id' => $background->id, 'name' => $background->name,               'image' => $background->image] : null,
            'auth'       => ['user' => $request->user()],
        ]);
    }

   
}
