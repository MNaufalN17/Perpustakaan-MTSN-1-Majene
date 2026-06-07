<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentClassController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $keyword = trim((string) $request->input('keyword', ''));

        $classes = StudentClass::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    if (Schema::hasColumn('classes', 'class_name')) {
                        $subQuery->orWhere('class_name', 'like', "%{$keyword}%");
                    }

                    if (Schema::hasColumn('classes', 'name')) {
                        $subQuery->orWhere('name', 'like', "%{$keyword}%");
                    }

                    if (Schema::hasColumn('classes', 'academic_year')) {
                        $subQuery->orWhere('academic_year', 'like', "%{$keyword}%");
                    }

                    if (Schema::hasColumn('classes', 'homeroom_teacher')) {
                        $subQuery->orWhere('homeroom_teacher', 'like', "%{$keyword}%");
                    }
                });
            })
            ->orderBy('level')
            ->orderBy('class_name')
            ->paginate(10)
            ->withQueryString();

        return view('pustakawan.classes.index', compact('classes', 'keyword'));
    }

    public function create()
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('pustakawan.classes.create');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $payload = $this->normalizePayload($request);

        $request->merge($payload);

        $validated = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:12'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'homeroom_teacher' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
        ], [
            'level.required' => 'Tingkat kelas wajib diisi.',
            'level.integer' => 'Tingkat kelas harus berupa angka.',
            'class_name.required' => 'Nama kelas wajib diisi.',
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
            'status.required' => 'Status kelas wajib dipilih.',
            'status.in' => 'Status kelas tidak valid.',
        ]);

        if ($this->classExists($validated['class_name'], $validated['academic_year'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'class_name' => 'Kelas "' . $validated['class_name'] . '" pada tahun ajaran ' . $validated['academic_year'] . ' sudah ada.',
                ]);
        }

        $class = new StudentClass();

        foreach ($this->filterColumns('classes', [
            'level' => $validated['level'],
            'class_name' => $validated['class_name'],
            'name' => $validated['class_name'],
            'academic_year' => $validated['academic_year'],
            'school_year' => $validated['academic_year'],
            'homeroom_teacher' => $validated['homeroom_teacher'] ?? null,
            'teacher_name' => $validated['homeroom_teacher'] ?? null,
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'aktif' ? 1 : 0,
        ]) as $column => $value) {
            $class->{$column} = $value;
        }

        $class->save();

        return redirect()
            ->route('classes.index')
            ->with('success_title', 'Kelas berhasil ditambahkan')
            ->with('success_message', 'Kelas "' . $validated['class_name'] . '" berhasil ditambahkan.')
            ->with('success_detail', 'Data kelas sudah tersimpan dan dapat digunakan pada data anggota.');
    }

    public function show(StudentClass $class)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('pustakawan.classes.show', compact('class'));
    }

    public function edit(StudentClass $class)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('pustakawan.classes.edit', compact('class'));
    }

    public function update(Request $request, StudentClass $class)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $payload = $this->normalizePayload($request);

        $request->merge($payload);

        $validated = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:12'],
            'class_name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'homeroom_teacher' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:aktif,nonaktif'],
        ], [
            'level.required' => 'Tingkat kelas wajib diisi.',
            'level.integer' => 'Tingkat kelas harus berupa angka.',
            'class_name.required' => 'Nama kelas wajib diisi.',
            'academic_year.required' => 'Tahun ajaran wajib diisi.',
            'status.required' => 'Status kelas wajib dipilih.',
            'status.in' => 'Status kelas tidak valid.',
        ]);

        if ($this->classExists($validated['class_name'], $validated['academic_year'], (int) $class->id)) {
            return back()
                ->withInput()
                ->withErrors([
                    'class_name' => 'Kelas "' . $validated['class_name'] . '" pada tahun ajaran ' . $validated['academic_year'] . ' sudah ada.',
                ]);
        }

        foreach ($this->filterColumns('classes', [
            'level' => $validated['level'],
            'class_name' => $validated['class_name'],
            'name' => $validated['class_name'],
            'academic_year' => $validated['academic_year'],
            'school_year' => $validated['academic_year'],
            'homeroom_teacher' => $validated['homeroom_teacher'] ?? null,
            'teacher_name' => $validated['homeroom_teacher'] ?? null,
            'status' => $validated['status'],
            'is_active' => $validated['status'] === 'aktif' ? 1 : 0,
        ]) as $column => $value) {
            $class->{$column} = $value;
        }

        $class->save();

        return redirect()
            ->route('classes.index')
            ->with('success_title', 'Kelas berhasil diperbarui')
            ->with('success_message', 'Kelas "' . $validated['class_name'] . '" berhasil diperbarui.')
            ->with('success_detail', 'Perubahan data kelas sudah tersimpan.');
    }

    public function destroy(StudentClass $class)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $memberCount = 0;

        if (Schema::hasTable('members')) {
            if (Schema::hasColumn('members', 'student_class_id')) {
                $memberCount += DB::table('members')
                    ->where('student_class_id', $class->id)
                    ->count();
            }

            if (Schema::hasColumn('members', 'class_id')) {
                $memberCount += DB::table('members')
                    ->where('class_id', $class->id)
                    ->count();
            }
        }

        if ($memberCount > 0) {
            return redirect()
                ->route('classes.index')
                ->with('error_title', 'Kelas tidak bisa dihapus')
                ->with('error_message', 'Kelas ini masih digunakan oleh data anggota.')
                ->with('error_detail', 'Pindahkan atau ubah data anggota terlebih dahulu sebelum menghapus kelas.');
        }

        $className = $class->class_name ?? $class->name ?? 'Kelas';

        $class->delete();

        return redirect()
            ->route('classes.index')
            ->with('success_title', 'Kelas berhasil dihapus')
            ->with('success_message', 'Kelas "' . $className . '" berhasil dihapus.')
            ->with('success_detail', 'Data kelas sudah tidak tampil pada daftar kelas.');
    }

    private function normalizePayload(Request $request): array
    {
        $rawLevel = $request->input('level')
            ?? $request->input('grade')
            ?? $request->input('grade_level')
            ?? 7;

        $level = $this->normalizeLevel($rawLevel);

        $className = trim((string) (
            $request->input('class_name')
            ?? $request->input('name')
            ?? ''
        ));

        if ($className === '') {
            $className = $this->levelLabel($level);
        }

        $academicYear = trim((string) (
            $request->input('academic_year')
            ?? $request->input('school_year')
            ?? ''
        ));

        $homeroomTeacher = trim((string) (
            $request->input('homeroom_teacher')
            ?? $request->input('teacher_name')
            ?? ''
        ));

        $status = $request->input('status');

        if ($status === null && $request->has('is_active')) {
            $status = (bool) $request->input('is_active') ? 'aktif' : 'nonaktif';
        }

        $status = strtolower(trim((string) ($status ?: 'aktif')));

        if (!in_array($status, ['aktif', 'nonaktif'], true)) {
            $status = 'aktif';
        }

        return [
            'level' => $level,
            'class_name' => $className,
            'academic_year' => $academicYear,
            'homeroom_teacher' => $homeroomTeacher !== '' ? $homeroomTeacher : null,
            'status' => $status,
        ];
    }

    private function normalizeLevel(mixed $level): int
    {
        $level = strtoupper(trim((string) $level));

        return match ($level) {
            'VII', '7' => 7,
            'VIII', '8' => 8,
            'IX', '9' => 9,
            'X', '10' => 10,
            'XI', '11' => 11,
            'XII', '12' => 12,
            default => is_numeric($level) ? (int) $level : 7,
        };
    }

    private function levelLabel(int $level): string
    {
        return match ($level) {
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => (string) $level,
        };
    }

    private function classExists(string $className, string $academicYear, ?int $ignoreId = null): bool
    {
        $query = StudentClass::query()
            ->where('class_name', $className)
            ->where('academic_year', $academicYear);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, string $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }
}