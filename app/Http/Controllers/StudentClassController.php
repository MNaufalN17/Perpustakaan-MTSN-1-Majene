<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StudentClassController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $keyword = trim((string) (
            $request->input('keyword')
            ?? $request->input('search')
            ?? $request->input('q')
            ?? ''
        ));

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

                    if (Schema::hasColumn('classes', 'school_year')) {
                        $subQuery->orWhere('school_year', 'like', "%{$keyword}%");
                    }

                    if (Schema::hasColumn('classes', 'homeroom_teacher')) {
                        $subQuery->orWhere('homeroom_teacher', 'like', "%{$keyword}%");
                    }

                    if (Schema::hasColumn('classes', 'teacher_name')) {
                        $subQuery->orWhere('teacher_name', 'like', "%{$keyword}%");
                    }
                });
            });

        if (Schema::hasColumn('classes', 'level')) {
            $classes->orderBy('level');
        }

        if (Schema::hasColumn('classes', 'class_name')) {
            $classes->orderBy('class_name');
        } elseif (Schema::hasColumn('classes', 'name')) {
            $classes->orderBy('name');
        }

        $classes = $classes
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

        if ($this->isBulkRequest($request)) {
            return $this->storeBulk($request);
        }

        return $this->storeSingle($request);
    }

    private function storeSingle(Request $request)
    {
        $payload = $this->normalizePayload($request->all());

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

        $this->fillClassData($class, $validated);

        $class->save();

        return redirect()
            ->route('classes.index')
            ->with('success_title', 'Kelas berhasil ditambahkan')
            ->with('success_message', 'Kelas "' . $validated['class_name'] . '" berhasil ditambahkan.')
            ->with('success_detail', 'Data kelas sudah tersimpan dan dapat digunakan pada data anggota.');
    }

    private function storeBulk(Request $request)
    {
        $rows = $this->extractBulkRows($request);

        $normalizedRows = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $normalized = $this->normalizePayload($row);

            if ($this->isEmptyRow($normalized)) {
                continue;
            }

            if ($normalized['class_name'] === '') {
                $errors["classes.{$index}.class_name"] = 'Nama kelas wajib diisi.';
            }

            if ($normalized['academic_year'] === '') {
                $errors["classes.{$index}.academic_year"] = 'Tahun ajaran wajib diisi.';
            }

            if ($normalized['level'] < 1 || $normalized['level'] > 12) {
                $errors["classes.{$index}.level"] = 'Tingkat kelas tidak valid.';
            }

            if (!in_array($normalized['status'], ['aktif', 'nonaktif'], true)) {
                $errors["classes.{$index}.status"] = 'Status kelas tidak valid.';
            }

            $normalizedRows[] = [
                'index' => $index,
                'data' => $normalized,
            ];
        }

        if (empty($normalizedRows)) {
            throw ValidationException::withMessages([
                'classes' => 'Minimal isi satu data kelas.',
            ]);
        }

        $seen = [];

        foreach ($normalizedRows as $row) {
            $data = $row['data'];
            $key = strtolower($data['class_name'] . '|' . $data['academic_year']);

            if (isset($seen[$key])) {
                $errors["classes.{$row['index']}.class_name"] = 'Data kelas ini duplikat pada form.';
                continue;
            }

            $seen[$key] = true;

            if ($this->classExists($data['class_name'], $data['academic_year'])) {
                $errors["classes.{$row['index']}.class_name"] = 'Kelas "' . $data['class_name'] . '" pada tahun ajaran ' . $data['academic_year'] . ' sudah ada.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($normalizedRows) {
            foreach ($normalizedRows as $row) {
                $class = new StudentClass();

                $this->fillClassData($class, $row['data']);

                $class->save();
            }
        });

        return redirect()
            ->route('classes.index')
            ->with('success_title', 'Kelas berhasil ditambahkan')
            ->with('success_message', count($normalizedRows) . ' data kelas berhasil disimpan.')
            ->with('success_detail', 'Data kelas baru sudah tersedia untuk data anggota dan peminjaman.');
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

        $payload = $this->normalizePayload($request->all());

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

        $this->fillClassData($class, $validated);

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

    private function isBulkRequest(Request $request): bool
    {
        if (is_array($request->input('classes'))) {
            return true;
        }

        if (is_array($request->input('rows'))) {
            return true;
        }

        if (is_array($request->input('items'))) {
            return true;
        }

        if (is_array($request->input('class_name'))) {
            return true;
        }

        if (is_array($request->input('name'))) {
            return true;
        }

        if (is_array($request->input('academic_year'))) {
            return true;
        }

        if (is_array($request->input('school_year'))) {
            return true;
        }

        return false;
    }

    private function extractBulkRows(Request $request): array
    {
        if (is_array($request->input('classes'))) {
            return array_values($request->input('classes'));
        }

        if (is_array($request->input('rows'))) {
            return array_values($request->input('rows'));
        }

        if (is_array($request->input('items'))) {
            return array_values($request->input('items'));
        }

        $classNames = $this->arrayInput($request, 'class_name');
        $names = $this->arrayInput($request, 'name');
        $levels = $this->arrayInput($request, 'level');
        $grades = $this->arrayInput($request, 'grade');
        $gradeLevels = $this->arrayInput($request, 'grade_level');
        $academicYears = $this->arrayInput($request, 'academic_year');
        $schoolYears = $this->arrayInput($request, 'school_year');
        $homeroomTeachers = $this->arrayInput($request, 'homeroom_teacher');
        $teacherNames = $this->arrayInput($request, 'teacher_name');
        $statuses = $this->arrayInput($request, 'status');
        $isActives = $this->arrayInput($request, 'is_active');

        $max = max([
            count($classNames),
            count($names),
            count($levels),
            count($grades),
            count($gradeLevels),
            count($academicYears),
            count($schoolYears),
            count($homeroomTeachers),
            count($teacherNames),
            count($statuses),
            count($isActives),
        ]);

        $rows = [];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                'class_name' => $classNames[$i] ?? null,
                'name' => $names[$i] ?? null,
                'level' => $levels[$i] ?? null,
                'grade' => $grades[$i] ?? null,
                'grade_level' => $gradeLevels[$i] ?? null,
                'academic_year' => $academicYears[$i] ?? null,
                'school_year' => $schoolYears[$i] ?? null,
                'homeroom_teacher' => $homeroomTeachers[$i] ?? null,
                'teacher_name' => $teacherNames[$i] ?? null,
                'status' => $statuses[$i] ?? null,
                'is_active' => $isActives[$i] ?? null,
            ];
        }

        return $rows;
    }

    private function arrayInput(Request $request, string $key): array
    {
        $value = $request->input($key, []);

        return is_array($value) ? array_values($value) : [];
    }

    private function normalizePayload(array $payload): array
    {
        $rawLevel = $payload['level']
            ?? $payload['grade']
            ?? $payload['grade_level']
            ?? 7;

        $level = $this->normalizeLevel($rawLevel);

        $className = trim((string) (
            $payload['class_name']
            ?? $payload['name']
            ?? ''
        ));

        if ($className === '') {
            $className = $this->levelLabel($level);
        }

        $academicYear = trim((string) (
            $payload['academic_year']
            ?? $payload['school_year']
            ?? ''
        ));

        $homeroomTeacher = trim((string) (
            $payload['homeroom_teacher']
            ?? $payload['teacher_name']
            ?? ''
        ));

        $status = $payload['status'] ?? null;

        if ($status === null && array_key_exists('is_active', $payload)) {
            $status = (bool) $payload['is_active'] ? 'aktif' : 'nonaktif';
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

    private function isEmptyRow(array $row): bool
    {
        return trim((string) $row['class_name']) === ''
            && trim((string) $row['academic_year']) === ''
            && trim((string) ($row['homeroom_teacher'] ?? '')) === '';
    }

    private function normalizeLevel(mixed $level): int
    {
        $level = strtoupper(trim((string) $level));

        return match ($level) {
            'VII', '7', 'KELAS 7', 'KELAS VII' => 7,
            'VIII', '8', 'KELAS 8', 'KELAS VIII' => 8,
            'IX', '9', 'KELAS 9', 'KELAS IX' => 9,
            'X', '10', 'KELAS 10', 'KELAS X' => 10,
            'XI', '11', 'KELAS 11', 'KELAS XI' => 11,
            'XII', '12', 'KELAS 12', 'KELAS XII' => 12,
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
        $query = StudentClass::query();

        if (Schema::hasColumn('classes', 'class_name')) {
            $query->where('class_name', $className);
        } elseif (Schema::hasColumn('classes', 'name')) {
            $query->where('name', $className);
        }

        if (Schema::hasColumn('classes', 'academic_year')) {
            $query->where('academic_year', $academicYear);
        } elseif (Schema::hasColumn('classes', 'school_year')) {
            $query->where('school_year', $academicYear);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function fillClassData(StudentClass $class, array $data): void
    {
        foreach ($this->filterColumns('classes', [
            'level' => $data['level'],
            'class_name' => $data['class_name'],
            'name' => $data['class_name'],
            'academic_year' => $data['academic_year'],
            'school_year' => $data['academic_year'],
            'homeroom_teacher' => $data['homeroom_teacher'] ?? null,
            'teacher_name' => $data['homeroom_teacher'] ?? null,
            'status' => $data['status'],
            'is_active' => $data['status'] === 'aktif' ? 1 : 0,
        ]) as $column => $value) {
            $class->{$column} = $value;
        }
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