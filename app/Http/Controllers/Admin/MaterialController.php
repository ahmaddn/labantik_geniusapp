<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learning_modules;
use App\Models\Missions;
use App\Models\Materials;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function create(Learning_modules $modules, Missions $missions)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Materials/Create', [
            'module'  => ['id' => $modules->id, 'name' => $modules->name],
            'mission' => ['id' => $missions->id, 'name' => $missions->name, 'order_number' => $missions->order_number],
            'mascots' => $mascots,
        ]);
    }

    public function store(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'materials'               => 'required|array|min:1',
            'materials.*.title'       => 'required|string|max:255',
            'materials.*.description' => 'nullable|string',
            'materials.*.content'     => 'nullable|string',
            'materials.*.mascot_id'   => 'nullable|exists:mascots,id',
            'materials.*.youtube_link'=> 'nullable|url|max:255',
            'materials.*.image'       => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv,webm|max:51200',
            'materials.*.image_left'  => 'nullable|file|mimes:jpeg,png,jpg,gif|max:51200',
            'materials.*.image_right' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:51200',
            'materials.*.layout_type' => 'nullable|string',
        ], [
            'materials.*.title.required'   => 'Judul material wajib diisi.',
            'materials.*.content.required' => 'Konten material wajib diisi.',
            'materials.*.image.mimes'      => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, MP4, MOV, AVI, atau WebM.',
            'materials.*.image.max'        => 'Ukuran file maksimal 50MB.',
        ]);

        foreach ($validated['materials'] as $index => $material) {
            $data = [
                'mission_id'  => $missions->id,
                'module_id'   => $modules->id,
                'title'       => $material['title'],
                'description' => $material['description'] ?? '-',
                'content'     => $material['content'] ?? '-',
                'youtube_link'=> $material['youtube_link'] ?? null,
                'mascot_id'   => $material['mascot_id'] ?? null,
                'layout_type' => $material['layout_type'] ?? null,
                'created_by'  => Auth::id(),
            ];

            // Upload gambar atau video ke kolom image
            if ($request->hasFile("materials.{$index}.image")) {
                $file    = $request->file("materials.{$index}.image");
                $isVideo = in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'wmv', 'webm']);
                $folder  = $isVideo ? 'materials/videos' : 'materials/images';
                $data['image'] = $file->store($folder, 'public');
            }

            // Handle layout-specific images
            if (($data['layout_type'] ?? '') === 'image_comparison') {
                $contentData = json_decode($data['content'], true);
                if (!is_array($contentData)) $contentData = [];

                if ($request->hasFile("materials.{$index}.image_left")) {
                    $contentData['image_left'] = $request->file("materials.{$index}.image_left")->store('materials/images', 'public');
                }
                if ($request->hasFile("materials.{$index}.image_right")) {
                    $contentData['image_right'] = $request->file("materials.{$index}.image_right")->store('materials/images', 'public');
                }
                $data['content'] = json_encode($contentData);
            } elseif (($data['layout_type'] ?? '') === 'conceptual_systematic') {
                $contentData = json_decode($data['content'], true);
                if (!is_array($contentData)) $contentData = [];

                if (isset($contentData['levels']) && is_array($contentData['levels'])) {
                    foreach ($contentData['levels'] as $lvlIdx => &$level) {
                        $inputName = "materials.{$index}.conceptual_level_{$lvlIdx}_image";
                        if ($request->hasFile($inputName)) {
                            $level['image'] = $request->file($inputName)->store('materials/images', 'public');
                        }
                    }
                }
                $data['content'] = json_encode($contentData);
            }

            Materials::create($data);
        }

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Semua material berhasil ditambahkan.');
    }

    /**
     * Import materials from CSV file. Expected CSV header: title,description,content,mascot_id
     */
    public function import(Learning_modules $modules, Missions $missions, Request $request)
    {
        if ($missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            // max = 10240 KB = 10 MB
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = [];
        // CSV/TXT handling
        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return back()->with('error', 'Gagal membaca file.');
            }

            $header = null;
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($header === null) {
                    $header = array_map(fn($h) => trim(strtolower($h)), $row);
                    continue;
                }
                $rows[] = array_combine($header, $row);
            }
            fclose($handle);

            // XLSX/XLS handling via PhpSpreadsheet if available
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                return back()->with('error', 'Dukungan XLSX tidak ditemukan. Jalankan: composer require phpoffice/phpspreadsheet');
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            $header = null;
            foreach ($data as $r) {
                // $r is associative with keys 'A','B',...
                $values = array_values($r);
                if ($header === null) {
                    $header = array_map(fn($h) => trim(strtolower($h)), $values);
                    continue;
                }
                $rows[] = array_combine($header, $values);
            }
        } else {
            return back()->with('error', 'Silakan unggah file CSV atau XLSX.');
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau format tidak sesuai.');
        }

        $rowNumber = 1;
        $materialsToCreate = [];
        foreach ($rows as $row) {
            $rowNumber++;
            if ($row === false || !is_array($row)) {
                return back()->with('error', "Format baris tidak sesuai (baris $rowNumber).");
            }


            $data = [
                'title'       => $row['title'] ?? null,
                'description' => $row['description'] ?? null,
                'content'     => $row['content'] ?? null,
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'title'   => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            if ($validator->fails()) {
                return back()->with('error', "Validasi gagal pada baris $rowNumber: " . $validator->errors()->first());
            }

            // Resolve mascot_id if provided; if not found, set to null
            $rawMascot = isset($row['mascot_id']) ? trim($row['mascot_id']) : '';
            $resolvedMascot = null;
            if ($rawMascot !== '') {
                $m = \App\Models\Mascots::where('id', $rawMascot)->first();
                if (!$m) {
                    $basename = pathinfo($rawMascot, PATHINFO_BASENAME);
                    $m = \App\Models\Mascots::where('image', 'like', "%{$basename}%")->first();
                }
                if ($m) $resolvedMascot = $m->id;
            }

            $data['mascot_id'] = $resolvedMascot;
            $materialsToCreate[] = $data;
        }

        foreach ($materialsToCreate as $m) {
            Materials::create([
                'mission_id'  => $missions->id,
                'module_id'   => $modules->id,
                'title'       => $m['title'],
                'description' => $m['description'] ?? null,
                'content'     => $m['content'],
                'mascot_id'   => $m['mascot_id'] ?? null,
                'created_by'  => Auth::id(),
            ]);
        }

        return redirect()->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Import materi berhasil.');
    }

    public function show(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot', 'createdBy']);

        return Inertia::render('Admin/Modules/Materials/Show', [
            'module'   => ['id' => $modules->id, 'name' => $modules->name],
            'mission'  => ['id' => $missions->id, 'name' => $missions->name],
            'material' => $materials,
        ]);
    }

    public function edit(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $materials->load(['mascot']);
        $mascots = $modules->template?->mascots ?? [];

        return Inertia::render('Admin/Modules/Materials/Edit', [
            'module'   => ['id' => $modules->id, 'name' => $modules->name],
            'mission'  => ['id' => $missions->id, 'name' => $missions->name],
            'material' => $materials,
            'mascots'  => $mascots,
        ]);
    }

    public function update(Learning_modules $modules, Missions $missions, Materials $materials, Request $request)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'mascot_id'    => 'nullable|exists:mascots,id',
            'youtube_link' => 'nullable|url|max:255',
            'image'        => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv,webm|max:51200',
            'image_left'   => 'nullable|file|mimes:jpeg,png,jpg,gif|max:51200',
            'image_right'  => 'nullable|file|mimes:jpeg,png,jpg,gif|max:51200',
            'remove_image' => 'nullable|boolean',
            'layout_type'  => 'nullable|string',
        ], [
            'title.required'   => 'Judul material wajib diisi.',
            'content.required' => 'Konten material wajib diisi.',
            'image.mimes'      => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, MP4, MOV, AVI, atau WebM.',
            'image.max'        => 'Ukuran file maksimal 50MB.',
        ]);

        $data = [
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? '-',
            'content'     => $validated['content'] ?? '-',
            'youtube_link'=> $validated['youtube_link'],
            'mascot_id'   => $validated['mascot_id'],
            'layout_type' => $validated['layout_type'] ?? null,
        ];

        // Handle remove / replace image or video
        if ($request->input('remove_image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
            }
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($materials->image) {
                Storage::disk('public')->delete($materials->image);
            }
            $file    = $request->file('image');
            $isVideo = in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'wmv', 'webm']);
            $folder  = $isVideo ? 'materials/videos' : 'materials/images';
            $data['image'] = $file->store($folder, 'public');
        }

        if (($data['layout_type'] ?? '') === 'image_comparison') {
            $contentData = json_decode($data['content'] ?? '{}', true);
            if (!is_array($contentData)) $contentData = [];

            if ($request->hasFile('image_left')) {
                if (!empty($contentData['image_left'])) Storage::disk('public')->delete($contentData['image_left']);
                $contentData['image_left'] = $request->file('image_left')->store('materials/images', 'public');
            }
            if ($request->hasFile('image_right')) {
                if (!empty($contentData['image_right'])) Storage::disk('public')->delete($contentData['image_right']);
                $contentData['image_right'] = $request->file('image_right')->store('materials/images', 'public');
            }
            $data['content'] = json_encode($contentData);
        } elseif (($data['layout_type'] ?? '') === 'conceptual_systematic') {
            $contentData = json_decode($data['content'] ?? '{}', true);
            if (!is_array($contentData)) $contentData = [];

            if (isset($contentData['levels']) && is_array($contentData['levels'])) {
                foreach ($contentData['levels'] as $lvlIdx => &$level) {
                    $inputName = "conceptual_level_{$lvlIdx}_image";
                    if ($request->hasFile($inputName)) {
                        if (!empty($level['image'])) Storage::disk('public')->delete($level['image']);
                        $level['image'] = $request->file($inputName)->store('materials/images', 'public');
                    }
                }
            }
            $data['content'] = json_encode($contentData);
        }

        $materials->update($data);

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Learning_modules $modules, Missions $missions, Materials $materials)
    {
        if ($materials->mission_id !== $missions->id || $missions->module_id !== $modules->id) {
            abort(404);
        }

        if ($materials->image) {
            Storage::disk('public')->delete($materials->image);
        }

        $materials->delete();

        return redirect()
            ->route('admin.modules.missions.show', [$modules->id, $missions->id])
            ->with('success', 'Material berhasil dihapus.');
    }

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $headers = [
            'A1' => 'title',
            'B1' => 'description',
            'C1' => 'content',
            'D1' => 'mascot_id',
        ];

        // Example Data
        $example = [
            'A2' => 'Pengenalan Materi 1',
            'B2' => 'Ini adalah materi pengenalan yang menyenangkan.',
            'C2' => '<p>Konten materi format HTML atau teks biasa</p>',
            'D2' => '',
        ];

        foreach (array_merge($headers, $example) as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getColumnDimension(substr($cell, 0, 1))->setAutoSize(true);
        }
        
        // Style Header
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Materi.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
