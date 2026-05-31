<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with(['class', 'createdBy'])->latest()->get();
        $classes = Classes::latest()->get();
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'classes' => $classes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        // If current user is a teacher, they may only create student accounts
        $current = Auth::user();
        if ($current && $current->role === 'guru' && $request->role !== 'siswa') {
            return redirect()->back()->with('error', 'Role guru hanya dapat membuat akun siswa.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password ?? '11223344'),
            'role' => $request->role,
            'class_id' => $request->class_id,
            'created_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $current = Auth::user();

        // If current user is a teacher, disallow updating admin accounts or assigning non-student roles
        if ($current && $current->role === 'guru') {
            if ($user->role === 'admin') {
                return redirect()->back()->with('error', 'Anda tidak diizinkan mengubah akun admin.');
            }
            if ($request->role !== 'siswa') {
                return redirect()->back()->with('error', 'Role guru hanya dapat mengatur akun siswa.');
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role,
            'class_id' => $request->class_id,
        ]);

        return redirect()->back()->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $current = Auth::user();

        // Prevent deleting self
        if ($current && $current->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // If current user is a teacher, they may only delete student accounts
        if ($current && $current->role === 'guru' && $user->role !== 'siswa') {
            return redirect()->back()->with('error', 'Anda tidak diizinkan menghapus akun non-siswa.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus');
    }

    public function downloadTemplate(Request $request)
    {
        $role = $request->query('role', 'siswa');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'Nama Lengkap');
        $sheet->setCellValue('B1', 'Email (Opsional)');

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        
        // Style Header
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = $role === 'guru' ? 'Template_Import_Guru.xlsx' : 'Template_Import_Siswa.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'role' => 'required|in:guru,siswa',
            'class_id' => 'required_if:role,siswa',
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $current = Auth::user();
        if ($current && $current->role === 'guru') {
            if ($request->role !== 'siswa') {
                return redirect()->back()->with('error', 'Guru hanya dapat mengimport akun siswa.');
            }
        }

        $role = $request->role;

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Remove header row
            array_shift($rows);

            $successCount = 0;

            foreach ($rows as $row) {
                $name = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');

                if (empty($name)) {
                    continue;
                }

                if (empty($email)) {
                    // Generate a fake email if not provided
                    $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(100,999) . '@' . $role . '.com';
                }

                // Generate simple username: First word of name (letters only) + 3 random letters
                $firstName = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $name)[0]));
                if (empty($firstName)) {
                    $firstName = $role;
                }
                
                $username = $firstName . strtolower(Str::random(3));
                
                // Ensure unique username
                while (User::where('username', $username)->exists()) {
                    $username = $firstName . strtolower(Str::random(3));
                }

                // Ensure unique email
                while (User::where('email', $email)->exists()) {
                    $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(1000,9999) . '@' . $role . '.com';
                }

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => $username,
                    'password' => Hash::make('11223344'), // Default password
                    'role' => $role,
                    'class_id' => $role === 'siswa' ? $request->class_id : null,
                    'created_by' => Auth::id()
                ]);

                $successCount++;
            }

            if ($successCount > 0) {
                return redirect()->back()->with('success', $successCount . ' ' . ucfirst($role) . ' berhasil diimport.');
            } else {
                return redirect()->back()->with('error', 'Tidak ada data valid yang dapat diimport dari file tersebut.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }
}
