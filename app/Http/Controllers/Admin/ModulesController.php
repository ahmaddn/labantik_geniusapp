<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModulesController extends Controller
{
    /**
     * Display a listing of modules
     */
    public function index()
    {
        // Data dummy modules
        $modules = [
            [
                'id' => 1,
                'title' => 'Pengenalan JavaScript',
                'description' => 'Modul dasar JavaScript untuk pemula',
                'content' => 'Konten lengkap modul JavaScript',
                'thumbnail' => '/images/js-thumb.jpg',
                'created_by' => 'Admin',
                'maxval_id' => 1,
                'missionsCount' => 3,
                'materialsCount' => 5,
                'quizzesCount' => 2,
                'created_at' => '2024-01-15 10:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Pemrograman PHP',
                'description' => 'Belajar PHP dari dasar hingga advanced',
                'content' => 'Konten lengkap modul PHP',
                'thumbnail' => '/images/php-thumb.jpg',
                'created_by' => 'Admin',
                'maxval_id' => 2,
                'missionsCount' => 4,
                'materialsCount' => 8,
                'quizzesCount' => 3,
                'created_at' => '2024-01-20 14:30:00',
            ],
        ];

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules
        ]);
    }

    /**
     * Show module detail with missions list
     */
    public function show($id)
    {
        // Data dummy module
        $module = [
            'id' => $id,
            'title' => 'Pengenalan JavaScript',
            'description' => 'Modul dasar JavaScript untuk pemula',
            'content' => 'Konten lengkap modul JavaScript',
            'thumbnail' => '/images/js-thumb.jpg',
            'created_by' => 'Admin',
            'materials_count' => 5,
            'quizzes_count' => 2,
            'created_at' => '2024-01-15 10:00:00',
        ];

        // Data dummy missions
        $missions = [
            [
                'id' => 1,
                'module_id' => $id,
                'name' => 'Variabel dan Tipe Data',
                'description' => 'Memahami konsep variabel dan berbagai tipe data di JavaScript',
                'order_number' => 1,
                'materials_count' => 2,
                'quizzes_count' => 1,
                'created_at' => '2024-01-16 09:00:00',
            ],
            [
                'id' => 2,
                'module_id' => $id,
                'name' => 'Function dan Scope',
                'description' => 'Belajar membuat dan menggunakan function dalam JavaScript',
                'order_number' => 2,
                'materials_count' => 3,
                'quizzes_count' => 1,
                'created_at' => '2024-01-17 10:30:00',
            ],
            [
                'id' => 3,
                'module_id' => $id,
                'name' => 'Array dan Object',
                'description' => 'Mengenal struktur data array dan object',
                'order_number' => 3,
                'materials_count' => 0,
                'quizzes_count' => 0,
                'created_at' => '2024-01-18 11:00:00',
            ],
        ];

        return Inertia::render('Admin/Modules/Show', [
            'module' => $module,
            'missions' => $missions
        ]);
    }

    /**
     * Show mission detail with materials and quizzes list
     */
    public function showMission($moduleId, $missionId)
    {
        // Data dummy module
        $module = [
            'id' => $moduleId,
            'title' => 'Pengenalan JavaScript',
            'description' => 'Modul dasar JavaScript untuk pemula',
        ];

        // Data dummy mission
        $mission = [
            'id' => $missionId,
            'module_id' => $moduleId,
            'name' => 'Variabel dan Tipe Data',
            'description' => 'Memahami konsep variabel dan berbagai tipe data di JavaScript',
            'order_number' => 1,
            'created_at' => '2024-01-16 09:00:00',
        ];

        // Data dummy materials (sorted by created_at)
        $materials = [
            [
                'id' => 1,
                'mission_id' => $missionId,
                'title' => 'Pengenalan Variabel',
                'description' => 'Memahami konsep dasar variabel dalam JavaScript',
                'content' => 'Konten tentang variabel...',
                'type' => 'text',
                'created_by' => 'Admin',
                'created_at' => '2024-01-16 10:00:00',
            ],
            [
                'id' => 2,
                'mission_id' => $missionId,
                'title' => 'Video Tutorial: Tipe Data',
                'description' => 'Video pembelajaran tentang tipe data JavaScript',
                'content' => 'https://youtube.com/example',
                'type' => 'video',
                'created_by' => 'Instructor',
                'created_at' => '2024-01-16 11:30:00',
            ],
            [
                'id' => 3,
                'mission_id' => $missionId,
                'title' => 'Panduan Lengkap Variabel',
                'description' => 'Dokumen PDF panduan lengkap variabel',
                'content' => '/files/variabel-guide.pdf',
                'type' => 'pdf',
                'created_by' => 'Admin',
                'created_at' => '2024-01-16 14:00:00',
            ],
        ];

        // Data dummy quizzes (sorted by created_at)
        $quizzes = [
            [
                'id' => 1,
                'mission_id' => $missionId,
                'title' => 'Quiz: Variabel dan Tipe Data',
                'description' => 'Uji pemahaman tentang variabel dan tipe data',
                'time_limit' => 30,
                'category' => 'Fundamental',
                'type' => 'multiple_choice',
                'questions_count' => 10,
                'created_at' => '2024-01-16 15:00:00',
            ],
            [
                'id' => 2,
                'mission_id' => $missionId,
                'title' => 'Drag & Drop: Tipe Data',
                'description' => 'Cocokkan tipe data dengan contohnya',
                'time_limit' => 15,
                'category' => 'Praktik',
                'type' => 'drag_drop',
                'questions_count' => 5,
                'created_at' => '2024-01-17 09:00:00',
            ],
        ];

        return Inertia::render('Admin/Modules/ShowMission', [
            'module' => $module,
            'mission' => $mission,
            'materials' => $materials,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Show create mission wizard
     */
    public function createMission($id)
    {
        // Get module data
        $module = [
            'id' => $id,
            'title' => 'Pengenalan JavaScript',
        ];

        return Inertia::render('Admin/Modules/Wizards/Mission', [
            'moduleId' => $id,
            'moduleName' => $module['title']
        ]);
    }

    /**
     * Show create material wizard
     */
    public function createMaterial($moduleId, $missionId)
    {
        // Get module and mission data
        $module = [
            'id' => $moduleId,
            'title' => 'Pengenalan JavaScript',
        ];

        $mission = [
            'id' => $missionId,
            'name' => 'Variabel dan Tipe Data',
        ];

        return Inertia::render('Admin/Modules/Wizards/Material', [
            'moduleId' => $moduleId,
            'missionId' => $missionId,
            'moduleName' => $module['title'],
            'missionName' => $mission['name']
        ]);
    }

    /**
     * Show create quiz wizard
     */
    public function createQuiz($moduleId, $missionId)
    {
        // Get module and mission data
        $module = [
            'id' => $moduleId,
            'title' => 'Pengenalan JavaScript',
        ];

        $mission = [
            'id' => $missionId,
            'name' => 'Variabel dan Tipe Data',
        ];

        return Inertia::render('Admin/Modules/Wizards/Quiz', [
            'moduleId' => $moduleId,
            'missionId' => $missionId,
            'moduleName' => $module['title'],
            'missionName' => $mission['name']
        ]);
    }
}
