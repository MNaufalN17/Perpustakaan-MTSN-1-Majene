<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $user = Auth::user();

        $isPustakawan = $user && (int) $user->role_id === 1;
        $isKepala = $user && (int) $user->role_id === 2;
        $isAdmin = $user && (int) $user->role_id === 3;

        $dashboardRoute = $isAdmin
            ? route('admin.dashboard')
            : ($isKepala ? route('kepala_sekolah.dashboard') : route('dashboard'));

        $panduanFile = '#';
        if ($isAdmin) {
            $panduanFile = asset('panduan/Panduan_Admin_Perpustakaan_MTSN1_Majene.pdf');
        } elseif ($isPustakawan) {
            $panduanFile = asset('panduan/Panduan_Staff_Perpustakaan_MTSN1_Majene.pdf');
        } elseif ($isKepala) {
            $panduanFile = asset('panduan/Panduan_Kepala_Perpustakaan_MTSN1_Majene.pdf');
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ $dashboardRoute }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-emerald-600" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    @if($isAdmin)
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('users.*', 'admin.settings.*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                        <span>Manajemen IT</span>
                                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('users.index')">
                                        {{ __('Kelola Akun Staf') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('admin.settings.index')">
                                        {{ __('Pengaturan Sistem') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    @if($isPustakawan)
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <x-nav-link :href="route('loans.index')" :active="request()->routeIs('loans.*')">
                            {{ __('Peminjaman') }}
                        </x-nav-link>

                        <x-nav-link :href="route('visits.index')" :active="request()->routeIs('visits.*')">
                            {{ __('Pengunjung') }}
                        </x-nav-link>

                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('books.*', 'book_items.*', 'categories.*', 'ddc.*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                        <span>Koleksi</span>
                                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('books.index')">
                                        {{ __('Buku Induk') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('book_items.index')">
                                        {{ __('Stok Fisik Buku') }}
                                    </x-dropdown-link>

                                    <div class="my-1 border-t border-gray-100"></div>

                                    <x-dropdown-link :href="route('categories.index')">
                                        {{ __('Kategori') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('ddc.index')">
                                        {{ __('Klasifikasi DDC') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('members.*', 'classes.*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                        <span>Anggota</span>
                                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('members.index')">
                                        {{ __('Daftar Anggota') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('classes.index')">
                                        {{ __('Kelas') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    @if($isKepala)
                        <x-nav-link :href="route('kepala_sekolah.dashboard')" :active="request()->routeIs('kepala_sekolah.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="56">
                                <x-slot name="trigger">
                                    <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('kepala_sekolah.reports.*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                        <span>Laporan</span>
                                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('kepala_sekolah.reports.index')">
                                        {{ __('Laporan Peminjaman') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('kepala_sekolah.reports.collections')">
                                        {{ __('Laporan Koleksi') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('kepala_sekolah.reports.members')">
                                        {{ __('Laporan Anggota') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('kepala_sekolah.reports.damaged_lost')">
                                        {{ __('Laporan Rusak / Hilang') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('books.*', 'members.*', 'visits.*') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                        <span>Data Monitoring</span>
                                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('books.index')">
                                        {{ __('Data Koleksi') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('members.index')">
                                        {{ __('Data Anggota') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('visits.index')">
                                        {{ __('Buku Tamu') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Tombol Panduan -->
                @if($panduanFile !== '#')
                <a href="{{ $panduanFile }}" target="_blank"
                   class="mr-4 inline-flex items-center px-3 py-1.5 border border-gray-200 text-sm font-medium rounded-md text-gray-600 bg-white hover:text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out focus:outline-none shadow-sm"
                   title="Lihat Buku Panduan">
                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Panduan
                </a>
                @endif

                <!-- Tombol Switch Tema -->
                <div class="mr-4 flex items-center justify-center"
                    x-data="{ 
                        theme: localStorage.getItem('app-theme') || 'emerald',
                        toggleTheme() {
                            this.theme = this.theme === 'emerald' ? 'pink' : 'emerald';
                            localStorage.setItem('app-theme', this.theme);
                            if (this.theme === 'pink') {
                                document.documentElement.setAttribute('data-theme', 'pink');
                            } else {
                                document.documentElement.removeAttribute('data-theme');
                            }
                        }
                    }"
                >
                    <button 
                        @click="toggleTheme()"
                        :class="theme === 'pink' ? 'bg-pink-500' : 'bg-emerald-500'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                        role="switch" 
                        title="Ganti Tema Warna"
                    >
                        <span class="sr-only">Toggle Theme</span>
                        <span 
                            :class="theme === 'pink' ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                    </button>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil Saya') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-gray-200 sm:hidden">
        <div class="space-y-1 pb-3 pt-2">

            @if($isAdmin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <div class="px-4 py-2 mt-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Manajemen IT
                </div>

                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="pl-8 border-l-2">
                    {{ __('Kelola Akun Staf') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')" class="pl-8 border-l-2">
                    {{ __('Pengaturan Sistem') }}
                </x-responsive-nav-link>
            @endif

            @if($isPustakawan)
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('loans.index')" :active="request()->routeIs('loans.*')">
                    {{ __('Peminjaman') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('visits.index')" :active="request()->routeIs('visits.*')">
                    {{ __('Pengunjung') }}
                </x-responsive-nav-link>

                <div class="px-4 py-2 mt-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Koleksi
                </div>

                <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.*')" class="pl-8 border-l-2">
                    {{ __('Buku Induk') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('book_items.index')" :active="request()->routeIs('book_items.*')" class="pl-8 border-l-2">
                    {{ __('Stok Fisik Buku') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" class="pl-8 border-l-2">
                    {{ __('Kategori') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('ddc.index')" :active="request()->routeIs('ddc.*')" class="pl-8 border-l-2">
                    {{ __('Klasifikasi DDC') }}
                </x-responsive-nav-link>

                <div class="px-4 py-2 mt-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Anggota
                </div>

                <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')" class="pl-8 border-l-2">
                    {{ __('Daftar Anggota') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('classes.index')" :active="request()->routeIs('classes.*')" class="pl-8 border-l-2">
                    {{ __('Kelas') }}
                </x-responsive-nav-link>
            @endif

            @if($isKepala)
                <x-responsive-nav-link :href="route('kepala_sekolah.dashboard')" :active="request()->routeIs('kepala_sekolah.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <div class="px-4 py-2 mt-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Laporan
                </div>

                <x-responsive-nav-link :href="route('kepala_sekolah.reports.index')" :active="request()->routeIs('kepala_sekolah.reports.index')" class="pl-8 border-l-2">
                    {{ __('Peminjaman') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('kepala_sekolah.reports.collections')" :active="request()->routeIs('kepala_sekolah.reports.collections')" class="pl-8 border-l-2">
                    {{ __('Koleksi') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('kepala_sekolah.reports.members')" :active="request()->routeIs('kepala_sekolah.reports.members')" class="pl-8 border-l-2">
                    {{ __('Anggota') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('kepala_sekolah.reports.damaged_lost')" :active="request()->routeIs('kepala_sekolah.reports.damaged_lost')" class="pl-8 border-l-2">
                    {{ __('Rusak / Hilang') }}
                </x-responsive-nav-link>

                <div class="px-4 py-2 mt-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Data Monitoring
                </div>

                <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.*')" class="pl-8 border-l-2">
                    {{ __('Data Koleksi') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')" class="pl-8 border-l-2">
                    {{ __('Data Anggota') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('visits.index')" :active="request()->routeIs('visits.*')" class="pl-8 border-l-2">
                    {{ __('Buku Tamu') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t border-gray-200 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800">
                    {{ Auth::user()->name }}
                </div>
                <div class="text-sm font-medium text-gray-500">
                    {{ Auth::user()->email }}
                </div>
            </div>

            <div class="mt-3 space-y-1">
                @if($panduanFile !== '#')
                    <x-responsive-nav-link :href="$panduanFile" target="_blank">
                        {{ __('Buku Panduan') }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Keluar') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
