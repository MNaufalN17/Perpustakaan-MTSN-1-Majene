<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                    Staff IT Admin
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">
                    Manajemen User Sistem
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kelola akun login, role, dan status pengguna sistem.
                </p>
            </div>

            <a href="{{ route('users.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if(session('success') || session('success_title') || session('success_message'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
                    <p class="text-sm font-bold">
                        {{ session('success_title', 'Berhasil') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ session('success_message', session('success')) }}
                    </p>
                </div>
            @endif

            @if(session('error') || session('error_title') || session('error_message'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
                    <p class="text-sm font-bold">
                        {{ session('error_title', 'Gagal') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ session('error_message', session('error')) }}
                    </p>
                </div>
            @endif

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl">
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6">
                    <h3 class="text-lg font-bold text-white">
                        Daftar User Sistem
                    </h3>
                    <p class="mt-1 text-sm text-emerald-50">
                        Pantau akun aktif, nonaktif, dan role setiap pengguna.
                    </p>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-3xl border border-gray-100 bg-white">
                        <table class="min-w-[960px] w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-4 font-bold">Nama</th>
                                    <th class="px-5 py-4 font-bold">Email</th>
                                    <th class="px-5 py-4 text-center font-bold">Role</th>
                                    <th class="px-5 py-4 text-center font-bold">Status</th>
                                    <th class="px-5 py-4 text-center font-bold">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($users as $user)
                                    @php
                                        $roleLabel = match ((int) $user->role_id) {
                                            1 => 'Pustakawan',
                                            2 => 'Kepala Perpustakaan',
                                            3 => 'Staff IT Admin',
                                            default => 'Pengguna Sistem',
                                        };
                                    @endphp

                                    <tr class="transition hover:bg-emerald-50/40">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-gray-900">
                                                {{ $user->name }}
                                            </p>

                                            @if($user->id === auth()->id())
                                                <p class="mt-1 text-xs font-semibold text-emerald-700">
                                                    Akun sedang digunakan
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-gray-700">
                                            {{ $user->email }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                {{ $roleLabel }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            @if($user->is_active ?? true)
                                                <span class="inline-flex rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="mx-auto w-[190px] rounded-2xl border border-gray-100 bg-slate-50 p-2 shadow-sm">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <a href="{{ route('users.edit', $user) }}"
                                                       class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-teal-200 bg-white px-3 text-xs font-bold text-teal-700 transition hover:bg-teal-50">
                                                        <span class="material-symbols-outlined text-[15px]">edit</span>
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('users.destroy', $user) }}"
                                                        onsubmit="return confirm('Hapus user ini dari sistem?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                                        >
                                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-14 text-center">
                                            <p class="text-sm font-semibold text-gray-700">
                                                Belum ada user sistem.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            Kembali ke Dashboard Admin
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>