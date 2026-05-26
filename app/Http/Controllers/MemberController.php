<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index()
{
    $members = \App\Models\Member::with('studentClass')
        ->latest()
        ->paginate(10);

    if ((int) auth()->user()->role_id === 2) {
        return view('kepala_sekolah.members.index', compact('members'));
    }

    return view('pustakawan.members.index', compact('members'));
}

    public function create()
    {
        $classes = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        return view('pustakawan.members.create', compact('classes'));
    }

    public function store(Request $request)
{
    if ($request->has('members')) {
        $rawMembers = collect($request->input('members', []));

        $filteredMembers = $rawMembers
            ->filter(function ($member) {
                return collect([
                    $member['nis_nip'] ?? null,
                    $member['name'] ?? null,
                    $member['gender'] ?? null,
                    $member['student_class_id'] ?? null,
                    $member['phone'] ?? null,
                ])->filter(function ($value) {
                    return trim((string) $value) !== '';
                })->isNotEmpty();
            })
            ->values()
            ->toArray();

        if (empty($filteredMembers)) {
            return back()
                ->withInput()
                ->withErrors([
                    'members' => 'Minimal isi satu baris data anggota sebelum menyimpan.',
                ]);
        }

        $request->merge([
            'members' => $filteredMembers,
        ]);

        $validated = $request->validate([
            'members' => ['required', 'array', 'min:1', 'max:50'],
            'members.*.nis_nip' => ['required', 'string', 'max:50', 'distinct', 'unique:members,nis_nip'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.member_type' => ['required', 'in:siswa,guru'],
            'members.*.gender' => ['required', 'in:laki-laki,perempuan'],
            'members.*.student_class_id' => ['nullable', 'exists:classes,id'],
            'members.*.phone' => ['nullable', 'string', 'max:30'],
            'members.*.status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'members.required' => 'Minimal satu data anggota wajib diisi.',
            'members.min' => 'Minimal satu data anggota wajib diisi.',
            'members.max' => 'Maksimal 50 anggota dalam satu kali simpan.',

            'members.*.nis_nip.required' => 'NIS / NIP wajib diisi pada baris yang digunakan.',
            'members.*.nis_nip.distinct' => 'NIS / NIP tidak boleh sama antar baris.',
            'members.*.nis_nip.unique' => 'NIS / NIP sudah terdaftar.',
            'members.*.name.required' => 'Nama wajib diisi pada baris yang digunakan.',
            'members.*.member_type.required' => 'Jenis anggota wajib dipilih.',
            'members.*.member_type.in' => 'Jenis anggota tidak valid.',
            'members.*.gender.required' => 'Jenis kelamin wajib dipilih pada baris yang digunakan.',
            'members.*.gender.in' => 'Jenis kelamin tidak valid.',
            'members.*.student_class_id.exists' => 'Kelas yang dipilih tidak tersedia.',
            'members.*.status.required' => 'Status wajib dipilih.',
            'members.*.status.in' => 'Status anggota tidak valid.',
        ]);

        foreach ($validated['members'] as $index => $member) {
            if ($member['member_type'] === 'siswa' && empty($member['student_class_id'])) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "members.$index.student_class_id" => 'Baris ke-' . ($index + 1) . ': kelas wajib dipilih untuk anggota siswa.',
                    ]);
            }
        }

        foreach ($validated['members'] as $member) {
            \App\Models\Member::create([
                'member_code' => $this->generateMemberCode($member['member_type']),
                'nis_nip' => trim($member['nis_nip']),
                'name' => trim($member['name']),
                'member_type' => $member['member_type'],
                'gender' => $member['gender'],
                'student_class_id' => $member['member_type'] === 'siswa'
                    ? $member['student_class_id']
                    : null,
                'phone' => !empty($member['phone']) ? trim($member['phone']) : null,
                'status' => $member['status'],
                'card_image' => null,
            ]);
        }

        return redirect()
            ->route('members.index')
            ->with('success_title', 'Anggota berhasil ditambahkan')
            ->with('success_message', count($validated['members']) . ' anggota berhasil ditambahkan.')
            ->with('success_detail', 'Baris kosong otomatis dilewati dan tidak ikut disimpan.');
    }

    $validated = $request->validate([
        'nis_nip' => ['required', 'string', 'max:50', 'unique:members,nis_nip'],
        'name' => ['required', 'string', 'max:255'],
        'member_type' => ['required', 'in:siswa,guru'],
        'gender' => ['required', 'in:laki-laki,perempuan'],
        'student_class_id' => ['nullable', 'exists:classes,id'],
        'phone' => ['nullable', 'string', 'max:30'],
        'status' => ['required', 'in:aktif,nonaktif'],
        'card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ], [
        'nis_nip.required' => 'NIS / NIP wajib diisi.',
        'nis_nip.unique' => 'NIS / NIP sudah terdaftar.',
        'name.required' => 'Nama lengkap wajib diisi.',
        'member_type.required' => 'Jenis anggota wajib dipilih.',
        'member_type.in' => 'Jenis anggota tidak valid.',
        'gender.required' => 'Jenis kelamin wajib dipilih.',
        'gender.in' => 'Jenis kelamin tidak valid.',
        'student_class_id.exists' => 'Kelas yang dipilih tidak tersedia.',
        'status.required' => 'Status keanggotaan wajib dipilih.',
        'status.in' => 'Status anggota tidak valid.',
        'card_image.image' => 'File kartu anggota harus berupa gambar.',
        'card_image.mimes' => 'Kartu anggota harus berformat JPG, JPEG, PNG, atau WEBP.',
        'card_image.max' => 'Ukuran kartu anggota maksimal 2MB.',
    ]);

    if ($validated['member_type'] === 'siswa' && empty($validated['student_class_id'])) {
        return back()
            ->withInput()
            ->withErrors([
                'student_class_id' => 'Kelas wajib dipilih untuk anggota siswa.',
            ]);
    }

    $cardImagePath = null;

    if ($request->hasFile('card_image')) {
        $cardImagePath = $request->file('card_image')->store('member-cards', 'public');
    }

    $member = \App\Models\Member::create([
        'member_code' => $this->generateMemberCode($validated['member_type']),
        'nis_nip' => trim($validated['nis_nip']),
        'name' => trim($validated['name']),
        'member_type' => $validated['member_type'],
        'gender' => $validated['gender'],
        'student_class_id' => $validated['member_type'] === 'siswa'
            ? $validated['student_class_id']
            : null,
        'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
        'status' => $validated['status'],
        'card_image' => $cardImagePath,
    ]);

    return redirect()
        ->route('members.show', $member)
        ->with('success_title', 'Anggota berhasil ditambahkan')
        ->with('success_message', 'Anggota "' . $member->name . '" berhasil ditambahkan.')
        ->with('success_detail', 'Kode anggota dibuat otomatis oleh sistem.');
}

    private function storeMany(Request $request)
    {
        $validated = $request->validate([
            'members' => ['required', 'array', 'min:1', 'max:50'],
            'members.*.nis_nip' => ['required', 'string', 'max:50'],
            'members.*.name' => ['required', 'string', 'max:150'],
            'members.*.member_type' => ['required', Rule::in(['siswa', 'guru'])],
            'members.*.gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'members.*.student_class_id' => ['nullable', 'exists:classes,id'],
            'members.*.phone' => ['nullable', 'string', 'max:30'],
            'members.*.status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], $this->validationMessages(), $this->validationAttributes());

        $rows = collect($validated['members'])
            ->map(function ($row) {
                return [
                    'nis_nip' => trim($row['nis_nip']),
                    'name' => trim($row['name']),
                    'member_type' => $row['member_type'],
                    'gender' => $row['gender'],
                    'student_class_id' => $row['member_type'] === 'siswa'
                        ? ($row['student_class_id'] ?? null)
                        : null,
                    'phone' => $row['phone'] ?? null,
                    'status' => $row['status'] ?? 'aktif',
                ];
            })
            ->values();

        $seenNisNip = [];

        foreach ($rows as $index => $row) {
            if ($row['member_type'] === 'siswa' && empty($row['student_class_id'])) {
                throw ValidationException::withMessages([
                    "members.$index.student_class_id" => 'Baris ke-' . ($index + 1) . ': kelas wajib dipilih untuk anggota siswa.',
                ]);
            }

            if (in_array($row['nis_nip'], $seenNisNip)) {
                throw ValidationException::withMessages([
                    "members.$index.nis_nip" => 'Baris ke-' . ($index + 1) . ': NIS / NIP ini sama dengan baris sebelumnya.',
                ]);
            }

            $seenNisNip[] = $row['nis_nip'];

            if (Member::where('nis_nip', $row['nis_nip'])->exists()) {
                throw ValidationException::withMessages([
                    "members.$index.nis_nip" => 'Baris ke-' . ($index + 1) . ': NIS / NIP "' . $row['nis_nip'] . '" sudah digunakan.',
                ]);
            }
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                Member::create([
                    'member_code' => $this->generateMemberCode($row['member_type']),
                    'nis_nip' => $row['nis_nip'],
                    'name' => $row['name'],
                    'member_type' => $row['member_type'],
                    'gender' => $row['gender'],
                    'student_class_id' => $row['student_class_id'],
                    'phone' => $row['phone'],
                    'status' => $row['status'],
                    'card_image' => null,
                ]);
            }
        });

        return redirect()
            ->route('members.index')
            ->with('success_title', 'Anggota berhasil ditambahkan')
            ->with('success_message', $rows->count() . ' data anggota berhasil ditambahkan ke sistem.')
            ->with('success_detail', 'Kartu anggota dapat ditambahkan nanti melalui halaman detail atau edit anggota.');
    }

    public function show(\App\Models\Member $member)
{
    $member->load('studentClass');

    if ((int) auth()->user()->role_id === 2) {
        return view('kepala_sekolah.members.show', compact('member'));
    }

    return view('pustakawan.members.show', compact('member'));
}

    public function edit(Member $member)
    {
        $classes = StudentClass::orderBy('level')
            ->orderBy('class_name')
            ->get();

        $member->load('studentClass');

        return view('pustakawan.members.edit', compact('member', 'classes'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'member_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('members', 'member_code')->ignore($member->id),
            ],
            'nis_nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('members', 'nis_nip')->ignore($member->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'member_type' => ['required', Rule::in(['siswa', 'guru'])],
            'gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'student_class_id' => ['nullable', 'required_if:member_type,siswa', 'exists:classes,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->validationMessages(), $this->validationAttributes());

        $cardImagePath = $member->card_image;

        if ($request->hasFile('card_image')) {
            if ($member->card_image && Storage::disk('public')->exists($member->card_image)) {
                Storage::disk('public')->delete($member->card_image);
            }

            $cardImagePath = $request->file('card_image')->store('member-cards', 'public');
        }

        $member->update([
            'member_code' => $validated['member_code'],
            'nis_nip' => $validated['nis_nip'],
            'name' => $validated['name'],
            'member_type' => $validated['member_type'],
            'gender' => $validated['gender'],
            'student_class_id' => $validated['member_type'] === 'siswa'
                ? $validated['student_class_id']
                : null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'card_image' => $cardImagePath,
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success_title', 'Anggota berhasil diperbarui')
            ->with('success_message', 'Data anggota "' . $member->name . '" berhasil diperbarui.')
            ->with('success_detail', 'Perubahan data sudah tersimpan di sistem perpustakaan.');
    }

    public function destroy(Member $member)
    {
        $memberName = $member->name;

        if ($member->card_image && Storage::disk('public')->exists($member->card_image)) {
            Storage::disk('public')->delete($member->card_image);
        }

        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success_title', 'Anggota berhasil dihapus')
            ->with('success_message', 'Data anggota "' . $memberName . '" berhasil dihapus dari sistem.')
            ->with('success_detail', 'Data tersebut tidak akan tampil lagi pada daftar anggota.');
    }

    private function generateMemberCode(string $memberType): string
    {
        $prefix = $memberType === 'siswa' ? 'SIS' : 'GUR';

        do {
            $lastId = Member::max('id') ?? 0;
            $memberCode = $prefix . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } while (Member::where('member_code', $memberCode)->exists());

        return $memberCode;
    }

    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib dipilih untuk anggota siswa.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'unique' => ':attribute sudah digunakan. Silakan gunakan data lain.',
            'exists' => ':attribute yang dipilih tidak tersedia.',
            'in' => ':attribute yang dipilih tidak valid.',
            'image' => ':attribute harus berupa file gambar.',
            'mimes' => ':attribute harus berformat JPG, JPEG, PNG, atau WEBP.',
            'card_image.max' => 'Ukuran kartu anggota maksimal 2MB.',

            'members.required' => 'Minimal satu data anggota wajib diisi.',
            'members.array' => 'Format data anggota tidak valid.',
            'members.min' => 'Minimal satu data anggota wajib diisi.',
            'members.max' => 'Maksimal hanya boleh menambahkan 50 anggota dalam satu kali simpan.',

            'members.*.nis_nip.required' => 'NIS / NIP wajib diisi.',
            'members.*.name.required' => 'Nama lengkap wajib diisi.',
            'members.*.member_type.required' => 'Jenis anggota wajib dipilih.',
            'members.*.gender.required' => 'Jenis kelamin wajib dipilih.',
            'members.*.status.required' => 'Status anggota wajib dipilih.',
            'members.*.student_class_id.exists' => 'Kelas yang dipilih tidak tersedia.',

            'member_code.required' => 'Kode anggota wajib diisi.',
            'member_code.unique' => 'Kode anggota sudah digunakan. Silakan gunakan kode anggota lain.',
            'nis_nip.required' => 'NIS / NIP wajib diisi.',
            'nis_nip.unique' => 'NIS / NIP sudah digunakan. Silakan periksa kembali nomor identitas anggota.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'member_type.required' => 'Jenis anggota wajib dipilih.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'student_class_id.required_if' => 'Kelas wajib dipilih untuk anggota siswa.',
            'status.required' => 'Status keanggotaan wajib dipilih.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'member_code' => 'Kode anggota',
            'nis_nip' => 'NIS / NIP',
            'name' => 'Nama lengkap',
            'member_type' => 'Jenis anggota',
            'gender' => 'Jenis kelamin',
            'student_class_id' => 'Kelas',
            'phone' => 'Nomor HP / WhatsApp',
            'status' => 'Status keanggotaan',
            'card_image' => 'Kartu anggota',

            'members.*.nis_nip' => 'NIS / NIP',
            'members.*.name' => 'Nama lengkap',
            'members.*.member_type' => 'Jenis anggota',
            'members.*.gender' => 'Jenis kelamin',
            'members.*.student_class_id' => 'Kelas',
            'members.*.phone' => 'Nomor HP / WhatsApp',
            'members.*.status' => 'Status keanggotaan',
        ];
    }
}