@php
    $visit = $visit ?? null;
    $isEdit = (bool) $visit;
    $defaultDate = $visit?->visit_date ? $visit->visit_date->format('Y-m-d') : now()->format('Y-m-d');
    $defaultTime = $visit?->check_in_time ? substr((string) $visit->check_in_time, 0, 5) : now()->format('H:i');
    $memberOptions = $members->map(function ($member) {
        return [
            'id' => (string) $member->id,
            'name' => $member->name,
            'identity' => $member->nis_nip ?? $member->member_code ?? '',
            'type' => $member->member_type,
            'class_id' => $member->student_class_id ? (string) $member->student_class_id : '',
        ];
    })->values();
@endphp

<div
    x-data="{
        members: @js($memberOptions),
        memberId: @js((string) old('member_id', $visit->member_id ?? '')),
        visitorName: @js(old('visitor_name', $visit->visitor_name ?? '')),
        identityNumber: @js(old('identity_number', $visit->identity_number ?? '')),
        visitorType: @js(old('visitor_type', $visit->visitor_type ?? 'siswa')),
        studentClassId: @js((string) old('student_class_id', $visit->student_class_id ?? '')),
        applyMember(force = false) {
            const selected = this.members.find((member) => member.id === String(this.memberId));

            if (!selected) {
                return;
            }

            if (force || !this.visitorName) {
                this.visitorName = selected.name || '';
            }

            if (force || !this.identityNumber) {
                this.identityNumber = selected.identity || '';
            }

            if ((force || !this.studentClassId) && selected.class_id) {
                this.studentClassId = selected.class_id;
            }

            if (['siswa', 'guru'].includes(selected.type)) {
                this.visitorType = selected.type;
            }
        }
    }"
    x-init="applyMember(false)"
>
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
            <p class="text-sm font-bold">Data kunjungan belum bisa disimpan</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl md:p-8">
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <section class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5">
            <div class="mb-5 flex items-start gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <span class="material-symbols-outlined text-[20px]">badge</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Identitas Pengunjung</h3>
                    <p class="mt-1 text-sm text-gray-500">Pilih anggota aktif atau isi manual untuk pengunjung umum.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="member_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Ambil dari Anggota
                    </label>
                    <select
                        id="member_id"
                        name="member_id"
                        x-model="memberId"
                        @change="applyMember(true)"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">Input manual / pengunjung umum</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(old('member_id', $visit->member_id ?? '') == $member->id)>
                                {{ $member->name }} - {{ $member->nis_nip ?? $member->member_code ?? '-' }}
                                @if($member->studentClass)
                                    ({{ $member->studentClass->class_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="visitor_name" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Nama Pengunjung <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="visitor_name"
                        name="visitor_name"
                        type="text"
                        x-model="visitorName"
                        required
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                    @error('visitor_name')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="identity_number" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        NIS/NIP/Identitas
                    </label>
                    <input
                        id="identity_number"
                        name="identity_number"
                        type="text"
                        x-model="identityNumber"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                    @error('identity_number')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="visitor_type" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Jenis Pengunjung <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="visitor_type"
                        name="visitor_type"
                        x-model="visitorType"
                        required
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="staf">Staf</option>
                        <option value="umum">Umum</option>
                    </select>
                    @error('visitor_type')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="student_class_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Kelas
                    </label>
                    <select
                        id="student_class_id"
                        name="student_class_id"
                        x-model="studentClassId"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">Tanpa kelas</option>
                        @foreach($studentClasses as $class)
                            <option value="{{ $class->id }}" @selected(old('student_class_id', $visit->student_class_id ?? '') == $class->id)>
                                {{ $class->class_name }} - {{ $class->academic_year }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_class_id')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-start gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <span class="material-symbols-outlined text-[20px]">event_available</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Detail Kunjungan</h3>
                    <p class="mt-1 text-sm text-gray-500">Tanggal, jam, dan keperluan kunjungan.</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="visit_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="visit_date"
                        name="visit_date"
                        type="date"
                        value="{{ old('visit_date', $defaultDate) }}"
                        required
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                    @error('visit_date')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="check_in_time" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Jam Masuk
                    </label>
                    <input
                        id="check_in_time"
                        name="check_in_time"
                        type="time"
                        value="{{ old('check_in_time', $defaultTime) }}"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                    @error('check_in_time')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="visit_purpose" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Keperluan <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="visit_purpose"
                        name="visit_purpose"
                        type="text"
                        list="visit_purpose_options"
                        value="{{ old('visit_purpose', $visit->visit_purpose ?? '') }}"
                        required
                        placeholder="Contoh: Membaca buku"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >
                    <datalist id="visit_purpose_options">
                        <option value="Membaca buku">
                        <option value="Meminjam buku">
                        <option value="Mengembalikan buku">
                        <option value="Belajar kelompok">
                        <option value="Mencari referensi">
                    </datalist>
                    @error('visit_purpose')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                        Catatan
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    >{{ old('notes', $visit->notes ?? '') }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('visits.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                Batal
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kunjungan' }}
            </button>
        </div>
    </form>
</div>
