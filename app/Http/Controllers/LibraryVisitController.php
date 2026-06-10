<?php

namespace App\Http\Controllers;

use App\Models\LibraryVisit;
use App\Models\Member;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LibraryVisitController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeView();

        $keyword = trim((string) $request->input('keyword', ''));
        $visitorType = trim((string) $request->input('visitor_type', ''));
        $dateStart = trim((string) $request->input('date_start', ''));
        $dateEnd = trim((string) $request->input('date_end', ''));

        $visits = LibraryVisit::with(['member', 'studentClass', 'recorder'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('visitor_name', 'like', "%{$keyword}%")
                        ->orWhere('identity_number', 'like', "%{$keyword}%")
                        ->orWhere('visit_purpose', 'like', "%{$keyword}%")
                        ->orWhereHas('member', function ($memberQuery) use ($keyword) {
                            $memberQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('member_code', 'like', "%{$keyword}%")
                                ->orWhere('nis_nip', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('studentClass', function ($classQuery) use ($keyword) {
                            $classQuery->where('class_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($visitorType !== '', function ($query) use ($visitorType) {
                $query->where('visitor_type', $visitorType);
            })
            ->when($dateStart !== '', function ($query) use ($dateStart) {
                $query->whereDate('visit_date', '>=', $dateStart);
            })
            ->when($dateEnd !== '', function ($query) use ($dateEnd) {
                $query->whereDate('visit_date', '<=', $dateEnd);
            })
            ->latest('visit_date')
            ->latest('check_in_time')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalVisits = LibraryVisit::count();
        $todayVisits = LibraryVisit::whereDate('visit_date', today())->count();
        $monthVisits = LibraryVisit::whereBetween('visit_date', [
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d'),
        ])->count();

        return view('pustakawan.visits.index', [
            'visits' => $visits,
            'keyword' => $keyword,
            'visitorType' => $visitorType,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'totalVisits' => $totalVisits,
            'todayVisits' => $todayVisits,
            'monthVisits' => $monthVisits,
            'canManage' => $this->canManage(),
        ]);
    }

    public function create()
    {
        $this->authorizeManage();

        return view('pustakawan.visits.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $payload = $this->validatedPayload($request);
        $payload['recorded_by'] = auth()->id();

        $visit = LibraryVisit::create($payload);

        return redirect()
            ->route('visits.show', $visit)
            ->with('success_title', 'Kunjungan berhasil dicatat')
            ->with('success_message', 'Data kunjungan "' . $visit->visitor_name . '" berhasil disimpan.')
            ->with('success_detail', 'Riwayat buku tamu sudah masuk ke laporan kunjungan.');
    }

    public function show(LibraryVisit $visit)
    {
        $this->authorizeView();

        $visit->load(['member.studentClass', 'studentClass', 'recorder']);

        return view('pustakawan.visits.show', [
            'visit' => $visit,
            'canManage' => $this->canManage(),
        ]);
    }

    public function edit(LibraryVisit $visit)
    {
        $this->authorizeManage();

        $visit->load(['member.studentClass', 'studentClass']);

        return view('pustakawan.visits.edit', array_merge(
            ['visit' => $visit],
            $this->formOptions()
        ));
    }

    public function update(Request $request, LibraryVisit $visit)
    {
        $this->authorizeManage();

        $visit->update($this->validatedPayload($request));

        return redirect()
            ->route('visits.show', $visit)
            ->with('success_title', 'Kunjungan berhasil diperbarui')
            ->with('success_message', 'Data kunjungan "' . $visit->visitor_name . '" berhasil diperbarui.');
    }

    public function destroy(LibraryVisit $visit)
    {
        $this->authorizeManage();

        $visitorName = $visit->visitor_name;

        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success_title', 'Kunjungan berhasil dihapus')
            ->with('success_message', 'Catatan kunjungan "' . $visitorName . '" sudah dihapus.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'visitor_name' => ['nullable', 'string', 'max:150'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'visitor_type' => ['required', 'in:siswa,guru,staf,umum'],
            'student_class_id' => ['nullable', 'exists:classes,id'],
            'visit_purpose' => ['required', 'string', 'max:100'],
            'visit_date' => ['required', 'date'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'visitor_type.required' => 'Jenis pengunjung wajib dipilih.',
            'visitor_type.in' => 'Jenis pengunjung tidak valid.',
            'visit_purpose.required' => 'Keperluan kunjungan wajib diisi.',
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'check_in_time.date_format' => 'Jam masuk harus memakai format HH:MM.',
        ], [
            'member_id' => 'Anggota',
            'visitor_name' => 'Nama pengunjung',
            'identity_number' => 'NIS/NIP/Identitas',
            'student_class_id' => 'Kelas',
            'visit_purpose' => 'Keperluan',
            'visit_date' => 'Tanggal kunjungan',
            'check_in_time' => 'Jam masuk',
            'notes' => 'Catatan',
        ]);

        $member = ! empty($validated['member_id'])
            ? Member::with('studentClass')->find($validated['member_id'])
            : null;

        if ($member) {
            $validated['visitor_name'] = trim((string) ($validated['visitor_name'] ?? '')) ?: $member->name;
            $validated['identity_number'] = trim((string) ($validated['identity_number'] ?? '')) ?: $member->nis_nip;
            $validated['student_class_id'] = $validated['student_class_id'] ?? $member->student_class_id;

            if (in_array($member->member_type, ['siswa', 'guru'], true)) {
                $validated['visitor_type'] = $member->member_type;
            }
        }

        $validated['visitor_name'] = trim((string) ($validated['visitor_name'] ?? ''));

        if ($validated['visitor_name'] === '') {
            throw ValidationException::withMessages([
                'visitor_name' => 'Nama pengunjung wajib diisi jika tidak memilih anggota.',
            ]);
        }

        return [
            'member_id' => $validated['member_id'] ?? null,
            'visitor_name' => $validated['visitor_name'],
            'identity_number' => trim((string) ($validated['identity_number'] ?? '')) ?: null,
            'visitor_type' => $validated['visitor_type'],
            'student_class_id' => $validated['student_class_id'] ?? null,
            'visit_purpose' => trim((string) $validated['visit_purpose']),
            'visit_date' => $validated['visit_date'],
            'check_in_time' => $validated['check_in_time'] ?? null,
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
        ];
    }

    private function formOptions(): array
    {
        $members = Member::with('studentClass')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $studentClasses = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        return compact('members', 'studentClasses');
    }

    private function authorizeView(): void
    {
        if (! auth()->check() || ! in_array((int) auth()->user()->role_id, [1, 2], true)) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    private function authorizeManage(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    private function canManage(): bool
    {
        return auth()->check() && (int) auth()->user()->role_id === 1;
    }
}
