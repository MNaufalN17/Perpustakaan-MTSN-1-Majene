@php
$normalizeLevel = function ($level) {
$value = strtoupper(trim((string) $level));

$romanMap = [
'I' => '1',
'II' => '2',
'III' => '3',
'IV' => '4',
'V' => '5',
'VI' => '6',
'VII' => '7',
'VIII' => '8',
'IX' => '9',
'X' => '10',
'XI' => '11',
'XII' => '12',
];

if (isset($romanMap[$value])) {
return $romanMap[$value];
}

if (preg_match('/\d+/', $value, $matches)) {
return (string) ((int) $matches[0]);
}

return $value;
};

$levelLabel = function ($level) {
$romanMap = [
'1' => 'I',
'2' => 'II',
'3' => 'III',
'4' => 'IV',
'5' => 'V',
'6' => 'VI',
'7' => 'VII',
'8' => 'VIII',
'9' => 'IX',
'10' => 'X',
'11' => 'XI',
'12' => 'XII',
];

$key = (string) $level;

return $romanMap[$key] ?? $key;
};

$classesData = $classes
->map(function ($class) use ($normalizeLevel, $levelLabel) {
$levelKey = $normalizeLevel($class->level);

return [
'id' => $class->id,
'class_name' => $class->class_name,
'level' => $class->level,
'level_key' => $levelKey,
'level_label' => $levelLabel($levelKey),
'academic_year' => $class->academic_year,
];
})
->groupBy('level_key')
->sortKeys()
->map(function ($group) {
return $group
->sortBy([
['class_name', 'asc'],
['academic_year', 'asc'],
])
->values();
});

$levelOptions = $classesData
->keys()
->map(function ($level) use ($levelLabel) {
return [
'value' => (string) $level,
'label' => 'Kelas ' . $levelLabel($level),
];
})
->values();

$allClasses = $classes
->map(function ($class) use ($normalizeLevel, $levelLabel) {
$levelKey = $normalizeLevel($class->level);

return [
'id' => $class->id,
'class_name' => $class->class_name,
'level' => $class->level,
'level_key' => $levelKey,
'level_label' => $levelLabel($levelKey),
'academic_year' => $class->academic_year,
];
})
->sortBy([
['level_key', 'asc'],
['class_name', 'asc'],
['academic_year', 'asc'],
])
->values();

$oldBulkRows = old('members', []);
$oldSelectedClassId = (string) old('student_class_id', '');
$oldSelectedLevel = $normalizeLevel(old('level_selector', ''));

if (!$oldSelectedLevel && $oldSelectedClassId) {
$selectedClass = $classes->firstWhere('id', $oldSelectedClassId);

if ($selectedClass) {
$oldSelectedLevel = $normalizeLevel($selectedClass->level);
}
}
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Manajemen Anggota
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Tambah Anggota Baru
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Tambahkan data siswa atau guru sebagai anggota perpustakaan.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-bulk-member-modal'))"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    <span class="material-symbols-outlined text-[18px]">group_add</span>
                    Tambah Banyak Anggota
                </button>

                <a href="{{ route('members.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div
        class="py-10 bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 min-h-screen"
        x-data="memberCreateForm(@js($oldBulkRows), @js($errors->any()))"
        @open-bulk-member-modal.window="openBulkModal()"
        @keydown.escape.window="showBulkModal = false">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any() && empty($oldBulkRows))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                <div class="font-bold mb-2">Data anggota belum bisa disimpan:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/75 backdrop-blur-xl shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <div class="relative overflow-hidden bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -left-20 bottom-0 h-48 w-48 rounded-full bg-emerald-200/20 blur-2xl"></div>

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white shadow-sm">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">
                                person_add
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold">
                                Form Tambah Anggota
                            </h3>
                            <p class="mt-1 text-sm text-emerald-50">
                                Gunakan form ini untuk menambahkan satu anggota.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        <section class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6">
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <span class="material-symbols-outlined text-[20px]">badge</span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Identitas Anggota
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Masukkan nomor identitas dan nama lengkap anggota.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="nis_nip" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        NIS / NIP <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="nis_nip"
                                        name="nis_nip"
                                        type="text"
                                        value="{{ old('nis_nip') }}"
                                        required
                                        placeholder="Masukkan nomor identitas..."
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">

                                    @error('nis_nip')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="name" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        value="{{ old('name') }}"
                                        required
                                        placeholder="Nama sesuai identitas..."
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">

                                    @error('name')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                    <span class="material-symbols-outlined text-[20px]">groups</span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Jenis Anggota
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Tentukan apakah anggota merupakan siswa atau guru.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="member_type" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Jenis Anggota <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative mt-2">
                                        <select
                                            id="member_type"
                                            name="member_type"
                                            x-model="memberType"
                                            @change="handleMemberTypeChange()"
                                            required
                                            class="block w-full appearance-none rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 pr-10 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                            <option value="">Pilih Jenis Anggota</option>
                                            <option value="siswa">Siswa</option>
                                            <option value="guru">Guru</option>
                                        </select>

                                        <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                            expand_more
                                        </span>
                                    </div>

                                    @error('member_type')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="gender" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative mt-2">
                                        <select
                                            id="gender"
                                            name="gender"
                                            required
                                            class="block w-full appearance-none rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 pr-10 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>

                                        <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                            expand_more
                                        </span>
                                    </div>

                                    @error('gender')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section
                            x-show="memberType === 'siswa'"
                            x-transition
                            class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5 md:p-6"
                            style="display: none;">
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <span class="material-symbols-outlined text-[20px]">school</span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Data Kelas Siswa
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Pilihan tingkat sudah disatukan. Data seperti 7 dan VII akan tampil sebagai satu tingkat yang sama.
                                    </p>
                                </div>
                            </div>

                            <input type="hidden" name="level_selector" :value="selectedLevel">

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="level_selector" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Tingkat Kelas
                                    </label>

                                    <div class="relative mt-2">
                                        <select
                                            id="level_selector"
                                            x-model="selectedLevel"
                                            @change="selectedClassId = ''"
                                            :required="memberType === 'siswa'"
                                            class="block w-full appearance-none rounded-2xl border border-emerald-200 bg-white px-4 py-3 pr-10 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                            <option value="">Pilih Tingkat</option>
                                            <template x-for="level in levelOptions" :key="level.value">
                                                <option :value="level.value" x-text="level.label"></option>
                                            </template>
                                        </select>

                                        <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                            expand_more
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="student_class_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Rombel / Kelas
                                    </label>

                                    <div class="relative mt-2">
                                        <select
                                            id="student_class_id"
                                            name="student_class_id"
                                            x-model="selectedClassId"
                                            :disabled="!selectedLevel"
                                            :required="memberType === 'siswa'"
                                            class="block w-full appearance-none rounded-2xl border border-emerald-200 bg-white px-4 py-3 pr-10 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                                            <option value="">Pilih Rombel</option>
                                            <template x-for="cls in filteredClasses" :key="cls.id">
                                                <option :value="String(cls.id)" x-text="cls.class_name + ' - ' + cls.academic_year"></option>
                                            </template>
                                        </select>

                                        <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                            expand_more
                                        </span>
                                    </div>

                                    @error('student_class_id')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 md:p-6 shadow-sm">
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                    <span class="material-symbols-outlined text-[20px]">settings</span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Kontak, Status, dan Kartu Anggota
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Lengkapi nomor kontak, status keanggotaan, dan upload kartu anggota jika tersedia.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="phone" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        No. HP / WhatsApp
                                    </label>

                                    <input
                                        id="phone"
                                        name="phone"
                                        type="text"
                                        value="{{ old('phone') }}"
                                        placeholder="Contoh: 08123456789"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">

                                    @error('phone')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Status Keanggotaan <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative mt-2">
                                        <select
                                            id="status"
                                            name="status"
                                            required
                                            class="block w-full appearance-none rounded-2xl border border-emerald-200 bg-emerald-50/60 px-4 py-3 pr-10 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>

                                        <span class="material-symbols-outlined pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-600">
                                            expand_more
                                        </span>
                                    </div>

                                    @error('status')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="card_image" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                        Upload Kartu Anggota / Identitas
                                    </label>

                                    <input
                                        id="card_image"
                                        name="card_image"
                                        type="file"
                                        accept="image/png,image/jpeg,image/jpg,image/webp"
                                        class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">

                                    <p class="mt-2 text-xs text-gray-500">
                                        Opsional. Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2MB.
                                    </p>

                                    @error('card_image')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('members.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Batal
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                                <span>Simpan Anggota</span>
                                <span class="material-symbols-outlined text-[18px]">save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Banyak Anggota --}}
        <div
            x-show="showBulkModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"
                @click="showBulkModal = false"></div>

            <div class="relative z-10 flex min-h-full items-start justify-center py-6">
                <div
                    x-show="showBulkModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                    class="flex max-h-[88vh] w-full max-w-7xl flex-col overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between border-b border-gray-100 bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-5 text-white">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-50">
                                Input Banyak Data
                            </p>
                            <h3 class="mt-1 text-lg font-bold">
                                Tambah Banyak Anggota
                            </h3>
                            <p class="mt-1 text-xs text-emerald-50">
                                Tentukan jumlah entry, lalu isi data anggota per baris.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="showBulkModal = false"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('members.store') }}" class="flex min-h-0 flex-1 flex-col">
                        @csrf

                        <div class="flex-1 space-y-5 overflow-y-auto p-6">
                            @if($errors->any() && !empty($oldBulkRows))
                            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                                <p class="text-sm font-bold">Data anggota belum bisa disimpan</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5">
                                <div class="grid gap-4 md:grid-cols-[1fr_180px_160px] md:items-end">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            Jumlah Entry Anggota
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Pilih jumlah baris anggota yang ingin dibuat. Maksimal 50 anggota sekali simpan.
                                        </p>
                                    </div>

                                    <div>
                                        <label for="member_entry_count" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                            Jumlah Entry
                                        </label>

                                        <input
                                            id="member_entry_count"
                                            type="number"
                                            min="1"
                                            max="50"
                                            x-model.number="entryCount"
                                            class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                    </div>

                                    <button
                                        type="button"
                                        @click="generateBulkRows()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                                        <span class="material-symbols-outlined text-[18px]">table_rows</span>
                                        Buat Baris
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-3xl border border-gray-100">
                                <table class="min-w-[1300px] w-full divide-y divide-gray-100 text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3 font-bold w-[70px]">No</th>
                                            <th class="px-4 py-3 font-bold w-[160px]">NIS / NIP</th>
                                            <th class="px-4 py-3 font-bold w-[240px]">Nama</th>
                                            <th class="px-4 py-3 font-bold w-[140px]">Jenis</th>
                                            <th class="px-4 py-3 font-bold w-[150px]">Gender</th>
                                            <th class="px-4 py-3 font-bold w-[220px]">Kelas</th>
                                            <th class="px-4 py-3 font-bold w-[170px]">No. HP</th>
                                            <th class="px-4 py-3 font-bold w-[140px]">Status</th>
                                            <th class="px-4 py-3 font-bold w-[90px] text-center">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="(row, index) in bulkRows" :key="index">
                                            <tr>
                                                <td class="px-4 py-4 align-middle">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-xs font-bold text-emerald-700">
                                                        <span x-text="index + 1"></span>
                                                    </div>
                                                </td>

                                                <td class="px-4 py-4">
                                                    <input
                                                        type="text"
                                                        :name="`members[${index}][nis_nip]`"
                                                        x-model="row.nis_nip"
                                                        :required="isBulkRowUsed(row)"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                </td>

                                                <td class="px-4 py-4">
                                                    <input
                                                        type="text"
                                                        :name="`members[${index}][name]`"
                                                        x-model="row.name"
                                                        :required="isBulkRowUsed(row)"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                </td>

                                                <td class="px-4 py-4">
                                                    <select
                                                        :name="`members[${index}][member_type]`"
                                                        x-model="row.member_type"
                                                        @change="handleBulkMemberTypeChange(row)"
                                                        :required="isBulkRowUsed(row)"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                        <option value="siswa">Siswa</option>
                                                        <option value="guru">Guru</option>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-4">
                                                    <select
                                                        :name="`members[${index}][gender]`"
                                                        x-model="row.gender"
                                                        :required="isBulkRowUsed(row)"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                        <option value="">Pilih</option>
                                                        <option value="laki-laki">Laki-laki</option>
                                                        <option value="perempuan">Perempuan</option>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-4">
                                                    <select
                                                        :name="`members[${index}][student_class_id]`"
                                                        x-model="row.student_class_id"
                                                        :required="row.member_type === 'siswa' && isBulkRowUsed(row)"
                                                        :disabled="row.member_type !== 'siswa'"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                        <option value="">Pilih Kelas</option>
                                                        <template x-for="cls in allClasses" :key="cls.id">
                                                            <option :value="String(cls.id)" x-text="'Kelas ' + cls.level_label + ' - ' + cls.class_name + ' - ' + cls.academic_year"></option>
                                                        </template>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-4">
                                                    <input
                                                        type="text"
                                                        :name="`members[${index}][phone]`"
                                                        x-model="row.phone"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                </td>

                                                <td class="px-4 py-4">
                                                    <select
                                                        :name="`members[${index}][status]`"
                                                        x-model="row.status"
                                                        :required="isBulkRowUsed(row)"
                                                        class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                                        <option value="aktif">Aktif</option>
                                                        <option value="nonaktif">Nonaktif</option>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-4 text-center">
                                                    <button
                                                        type="button"
                                                        @click="removeBulkRow(index)"
                                                        x-show="bulkRows.length > 1"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100">
                                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <p class="text-xs leading-5 text-amber-700">
                                    Catatan: upload kartu anggota tidak tersedia pada input banyak data. Kartu dapat ditambahkan nanti melalui halaman detail atau edit anggota.
                                </p>
                            </div>
                        </div>

                        <div class="sticky bottom-0 flex shrink-0 flex-col-reverse gap-3 border-t border-gray-100 bg-white px-6 py-5 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                @click="showBulkModal = false"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                                <span>Simpan Semua Anggota</span>
                                <span class="material-symbols-outlined text-[18px]">save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        <script>
    function memberCreateForm(oldBulkRows = [], hasErrors = false) {
        return {
            memberType: @js(old('member_type', '')),
            selectedLevel: @js($oldSelectedLevel),
            selectedClassId: @js($oldSelectedClassId),
            classesData: @js($classesData),
            levelOptions: @js($levelOptions),
            allClasses: @js($allClasses),

            showBulkModal: oldBulkRows.length > 0 && hasErrors,
            entryCount: oldBulkRows.length > 0 ? oldBulkRows.length : 1,
            bulkRows: oldBulkRows.length > 0 ? oldBulkRows : [
                {
                    nis_nip: '',
                    name: '',
                    member_type: 'siswa',
                    gender: '',
                    student_class_id: '',
                    phone: '',
                    status: 'aktif',
                }
            ],

            get filteredClasses() {
                if (!this.selectedLevel || !this.classesData[this.selectedLevel]) {
                    return [];
                }

                return this.classesData[this.selectedLevel];
            },

            handleMemberTypeChange() {
                if (this.memberType === 'guru') {
                    this.selectedLevel = '';
                    this.selectedClassId = '';
                }
            },

            openBulkModal() {
                this.showBulkModal = true;

                if (!this.bulkRows.length) {
                    this.generateBulkRows();
                }
            },

            generateBulkRows() {
                let count = parseInt(this.entryCount || 1);

                if (count < 1) {
                    count = 1;
                }

                if (count > 50) {
                    count = 50;
                }

                this.entryCount = count;

                this.bulkRows = Array.from({ length: count }, () => ({
                    nis_nip: '',
                    name: '',
                    member_type: 'siswa',
                    gender: '',
                    student_class_id: '',
                    phone: '',
                    status: 'aktif',
                }));
            },

            removeBulkRow(index) {
                this.bulkRows.splice(index, 1);
                this.entryCount = this.bulkRows.length;
            },

            isBulkRowUsed(row) {
                return Boolean(
                    (row.nis_nip && row.nis_nip.trim() !== '') ||
                    (row.name && row.name.trim() !== '') ||
                    (row.gender && row.gender.trim() !== '') ||
                    (row.student_class_id && String(row.student_class_id).trim() !== '') ||
                    (row.phone && row.phone.trim() !== '')
                );
            },

            handleBulkMemberTypeChange(row) {
                if (row.member_type === 'guru') {
                    row.student_class_id = '';
                }
            },
        };
    }
</script>
    </div>
</x-app-layout>