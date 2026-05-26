<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                Staff IT Admin
            </p>
            <h2 class="text-xl font-bold text-gray-900">
                Edit User Sistem
            </h2>
            <p class="text-sm text-gray-500">
                Perbarui akun, role, status, atau reset password pengguna.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/40 to-sky-50/40 py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm">
                    <p class="text-sm font-bold">
                        User belum bisa diperbarui
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

            <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl">
                <div class="bg-gradient-to-r from-emerald-700 to-teal-500 p-6 text-white">
                    <h3 class="text-lg font-bold">
                        Form Edit User
                    </h3>
                    <p class="mt-1 text-sm text-emerald-50">
                        Kosongkan password jika tidak ingin mengganti password.
                    </p>
                </div>

                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6 p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Nama User <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Email <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div>
                            <label for="role_id" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Role <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="role_id"
                                name="role_id"
                                required
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="1" {{ old('role_id', $user->role_id) == '1' ? 'selected' : '' }}>Pustakawan</option>
                                <option value="2" {{ old('role_id', $user->role_id) == '2' ? 'selected' : '' }}>Kepala Perpustakaan</option>
                                <option value="3" {{ old('role_id', $user->role_id) == '3' ? 'selected' : '' }}>Staff IT Admin</option>
                            </select>
                        </div>

                        <div>
                            <label for="is_active" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Status Akun <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="is_active"
                                name="is_active"
                                required
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                                <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Password Baru
                            </label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-[0.12em] text-gray-500">
                                Konfirmasi Password Baru
                            </label>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="mt-2 block w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                            >
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-bold text-gray-600 hover:bg-gray-50">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800"
                        >
                            <span>Simpan Perubahan</span>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>