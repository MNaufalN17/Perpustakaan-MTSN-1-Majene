<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sistem Informasi Manajemen Perpustakaan - Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "on-error-container": "#93000a",
                "on-background": "#121c2a",
                "tertiary-container": "#636f68",
                "secondary-fixed-dim": "#b7c8e1",
                "secondary": "#505f76",
                "on-tertiary-container": "#e5f2e9",
                "surface-tint": "#006d37",
                "tertiary": "#4b5750",
                "surface-bright": "#f8f9ff",
                "surface-dim": "#d0dbed",
                "surface-variant": "#d9e3f6",
                "secondary-fixed": "#d3e4fe",
                "error-container": "#ffdad6",
                "surface-container-high": "#dee9fc",
                "tertiary-fixed-dim": "#bdcac1",
                "on-primary-fixed-variant": "#005228",
                "surface-container": "#e6eeff",
                "inverse-primary": "#7ada95",
                "primary-fixed": "#96f7af",
                "inverse-on-surface": "#eaf1ff",
                "surface": "#f8f9ff",
                "on-tertiary-fixed": "#131e19",
                "on-tertiary-fixed-variant": "#3e4943",
                "on-secondary": "#ffffff",
                "on-surface": "#121c2a",
                "surface-container-low": "#eff4ff",
                "outline": "#6f7a6f",
                "surface-container-highest": "#d9e3f6",
                "on-tertiary": "#ffffff",
                "primary-fixed-dim": "#7ada95",
                "primary": "#006130",
                "surface-container-lowest": "#ffffff",
                "inverse-surface": "#27313f",
                "on-error": "#ffffff",
                "primary-container": "#107c41",
                "secondary-container": "#d0e1fb",
                "on-primary": "#ffffff",
                "background": "#f8f9ff",
                "error": "#ba1a1a",
                "on-secondary-fixed-variant": "#38485d",
                "on-primary-container": "#b6ffc5",
                "on-surface-variant": "#3f4940",
                "on-primary-fixed": "#00210c",
                "tertiary-fixed": "#d9e6dd",
                "on-secondary-container": "#54647a",
                "outline-variant": "#becabd",
                "on-secondary-fixed": "#0b1c30"
        },
        "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
        },
        "spacing": {
                "stack-sm": "8px",
                "margin-page": "40px",
                "stack-lg": "32px",
                "base": "8px",
                "stack-md": "16px",
                "gutter": "24px",
                "container-max": "1280px",
                "container-margin": "40px",
                "section-gap": "32px"
        },
        "fontFamily": {
                "body-md": [
                        "Inter"
                ],
                "label-caps": [
                        "Inter"
                ],
                "headline-md": [
                        "Inter"
                ],
                "display-lg": [
                        "Inter"
                ],
                "title-sm": [
                        "Inter"
                ],
                "body-sm": [
                        "Inter"
                ],
                "tabel-header": [
                        "Inter"
                ],
                "h2-subjudul": [
                        "Inter"
                ],
                "body-utama": [
                        "Inter"
                ],
                "label-tombol": [
                        "Inter"
                ],
                "body-kecil": [
                        "Inter"
                ],
                "h1-judul": [
                        "Inter"
                ]
        },
        "fontSize": {
                "body-md": [
                        "16px",
                        {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                        }
                ],
                "label-caps": [
                        "12px",
                        {
                                "lineHeight": "16px",
                                "letterSpacing": "0.05em",
                                "fontWeight": "600"
                        }
                ],
                "headline-md": [
                        "24px",
                        {
                                "lineHeight": "32px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "600"
                        }
                ],
                "display-lg": [
                        "36px",
                        {
                                "lineHeight": "44px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
                        }
                ],
                "title-sm": [
                        "18px",
                        {
                                "lineHeight": "28px",
                                "fontWeight": "600"
                        }
                ],
                "body-sm": [
                        "14px",
                        {
                                "lineHeight": "20px",
                                "fontWeight": "400"
                        }
                ],
                "tabel-header": [
                        "12px",
                        {
                                "lineHeight": "16px",
                                "letterSpacing": "0.05em",
                                "fontWeight": "700"
                        }
                ],
                "h2-subjudul": [
                        "24px",
                        {
                                "lineHeight": "32px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "600"
                        }
                ],
                "body-utama": [
                        "16px",
                        {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                        }
                ],
                "label-tombol": [
                        "14px",
                        {
                                "lineHeight": "20px",
                                "fontWeight": "600"
                        }
                ],
                "body-kecil": [
                        "14px",
                        {
                                "lineHeight": "20px",
                                "fontWeight": "400"
                        }
                ],
                "h1-judul": [
                        "30px",
                        {
                                "lineHeight": "38px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
                        }
                ]
        }
},
    },
  }
</script>
</head>
<body class="bg-background text-on-background font-body-utama text-body-utama flex min-h-screen antialiased">
<!-- SideNavBar -->
<nav class="bg-surface w-64 h-screen fixed left-0 top-0 border-r border-outline-variant flex flex-col h-full py-base z-20">
<!-- Brand Header -->
<div class="px-container-margin py-gutter flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-primary-container" style="font-variation-settings: 'FILL' 1;">local_library</span>
</div>
<div>
<h1 class="font-h1-judul text-h2-subjudul font-bold text-primary truncate" title="SIM Perpustakaan">SIM Perpustakaan</h1>
<p class="font-body-kecil text-body-kecil text-on-surface-variant">MTsN 1 Majene</p>
</div>
</div>
<!-- Navigation Links -->
<ul class="flex-1 flex flex-col gap-1 px-3">
<!-- Active Tab: Beranda -->
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary font-bold border-l-4 border-primary bg-surface-container-high cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="font-label-tombol text-label-tombol">Beranda</span>
</a>
</li>
<!-- Inactive Tabs -->
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary border-l-4 border-transparent hover:bg-surface-container-low transition-colors cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">group</span>
<span class="font-label-tombol text-label-tombol">Manajemen Anggota</span>
</a>
</li>
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary border-l-4 border-transparent hover:bg-surface-container-low transition-colors cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">library_books</span>
<span class="font-label-tombol text-label-tombol">Katalog Buku</span>
</a>
</li>
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary border-l-4 border-transparent hover:bg-surface-container-low transition-colors cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">swap_horiz</span>
<span class="font-label-tombol text-label-tombol">Transaksi</span>
</a>
</li>
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary border-l-4 border-transparent hover:bg-surface-container-low transition-colors cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">payments</span>
<span class="font-label-tombol text-label-tombol">Manajemen Denda</span>
</a>
</li>
<li>
<a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:text-primary border-l-4 border-transparent hover:bg-surface-container-low transition-colors cursor-pointer transition-all duration-200" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">assessment</span>
<span class="font-label-tombol text-label-tombol">Laporan</span>
</a>
</li>
</ul>
</nav>
<!-- TopAppBar -->
<header class="bg-surface-bright fixed top-0 right-0 left-64 z-10 border-b border-outline-variant flex items-center justify-between px-container-margin h-20 transition-all duration-150 ease-in-out">
<!-- Search Bar on Left -->
<div class="relative w-96">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-variation-settings: 'FILL' 0;">search</span>
<input class="w-full bg-surface-container-low border border-outline-variant text-on-surface font-body-utama text-body-utama rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow" placeholder="Cari buku, anggota, atau transaksi..." type="text"/>
</div>
<!-- Trailing Actions & Profile -->
<div class="flex items-center gap-2">
<button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-all duration-150 ease-in-out flex items-center justify-center">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">notifications</span>
</button>
<button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-all duration-150 ease-in-out flex items-center justify-center">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">settings</span>
</button>
<div class="ml-4 pl-4 border-l border-outline-variant flex items-center gap-3 cursor-pointer">
<div class="text-right hidden md:block">
<p class="font-label-tombol text-label-tombol text-on-surface">Ahmad Faisal</p>
<p class="font-body-kecil text-body-kecil text-on-surface-variant">Kepala Perpustakaan</p>
</div>
<img alt="Foto Profil Pengguna" class="w-10 h-10 rounded-full object-cover border border-outline-variant" data-alt="A professional headshot of a middle-aged Indonesian man wearing a neat light blue shirt. The background is a softly blurred office environment with warm, neutral lighting. The man has a welcoming, reliable expression, suitable for a school librarian profile picture. High resolution and clear focus on the face." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAOlOb_qujAsXOy5Pdk3TGi97SnCXc6XUKoAT_AwsVX9zgIM8T0jRKhWRl_nFdhSdeN_c-S4Mmg-gA4iaUV3OjnTu_FH4Nhw9pRiIBBrpH3zPl3VhBgfPyEhLNobbxmjvsBJo2zN3h23mdnf0aaTWcun58jCFVyVSy8SoVDrdb8R4GvmoeQsyuFCZjfJml5eiz8eONERAdxmNtVWbkEOEsGY6XQTyBa8yY9cglhqEV4orJwiUENtz2hOME2TbE0906TA7rVhtVz2Jc"/>
</div>
</div>
</header>
<!-- Main Content Area -->
<main class="ml-64 mt-20 p-container-margin w-full bg-background flex flex-col gap-section-gap">
<!-- Page Header -->
<div>
<h2 class="font-h1-judul text-h1-judul text-on-background">Dashboard Utama</h2>
<p class="font-body-utama text-body-utama text-on-surface-variant mt-1">Ringkasan aktivitas dan metrik sistem perpustakaan hari ini.</p>
</div>
<!-- Bento Grid: Summary Statistics -->
<div class="grid grid-cols-12 gap-gutter">
<!-- Stat Card 1 -->
<div class="col-span-12 md:col-span-6 lg:col-span-3 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
<div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-110 transition-transform"></div>
<div class="flex items-center justify-between z-10">
<p class="font-label-tombol text-label-tombol text-on-surface-variant">Total Buku</p>
<div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">library_books</span>
</div>
</div>
<div class="z-10">
<h3 class="font-h1-judul text-h1-judul text-on-background">12,450</h3>
<p class="font-body-kecil text-body-kecil text-secondary mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">trending_up</span> +124 bulan ini
                    </p>
</div>
</div>
<!-- Stat Card 2 -->
<div class="col-span-12 md:col-span-6 lg:col-span-3 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
<div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary/5 rounded-full group-hover:scale-110 transition-transform"></div>
<div class="flex items-center justify-between z-10">
<p class="font-label-tombol text-label-tombol text-on-surface-variant">Anggota Aktif</p>
<div class="w-10 h-10 rounded-full bg-secondary-container/20 flex items-center justify-center">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">group</span>
</div>
</div>
<div class="z-10">
<h3 class="font-h1-judul text-h1-judul text-on-background">842</h3>
<p class="font-body-kecil text-body-kecil text-secondary mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">trending_up</span> +12 minggu ini
                    </p>
</div>
</div>
<!-- Stat Card 3 -->
<div class="col-span-12 md:col-span-6 lg:col-span-3 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
<div class="absolute -right-6 -top-6 w-24 h-24 bg-tertiary-container/5 rounded-full group-hover:scale-110 transition-transform"></div>
<div class="flex items-center justify-between z-10">
<p class="font-label-tombol text-label-tombol text-on-surface-variant">Peminjaman Hari Ini</p>
<div class="w-10 h-10 rounded-full bg-tertiary-fixed/40 flex items-center justify-center">
<span class="material-symbols-outlined text-tertiary-container" style="font-variation-settings: 'FILL' 1;">swap_horiz</span>
</div>
</div>
<div class="z-10">
<h3 class="font-h1-judul text-h1-judul text-on-background">156</h3>
<p class="font-body-kecil text-body-kecil text-on-surface-variant mt-1 flex items-center gap-1">
                        42 masih diproses
                    </p>
</div>
</div>
<!-- Stat Card 4 -->
<div class="col-span-12 md:col-span-6 lg:col-span-3 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
<div class="absolute -right-6 -top-6 w-24 h-24 bg-error/5 rounded-full group-hover:scale-110 transition-transform"></div>
<div class="flex items-center justify-between z-10">
<p class="font-label-tombol text-label-tombol text-on-surface-variant">Total Denda Belum Dibayar</p>
<div class="w-10 h-10 rounded-full bg-error-container/40 flex items-center justify-center">
<span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">payments</span>
</div>
</div>
<div class="z-10">
<h3 class="font-h2-subjudul text-h2-subjudul text-error">Rp 245.000</h3>
<p class="font-body-kecil text-body-kecil text-on-surface-variant mt-1 flex items-center gap-1">
                        Dari 15 anggota
                    </p>
</div>
</div>
</div>
<!-- Middle Section: Chart & Quick Actions -->
<div class="grid grid-cols-12 gap-gutter">
<!-- Chart Widget -->
<div class="col-span-12 lg:col-span-8 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col">
<div class="flex items-center justify-between mb-6">
<h3 class="font-h2-subjudul text-h2-subjudul text-on-background">Tren Peminjaman Bulanan</h3>
<select class="bg-surface-container-low border border-outline-variant text-on-surface font-body-kecil rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
<option>Tahun Ini</option>
<option>6 Bulan Terakhir</option>
</select>
</div>
<!-- Stylized CSS Chart Representation -->
<div class="flex-1 flex items-end justify-between gap-2 h-48 mt-4 border-b border-outline-variant pb-2 relative">
<!-- Y-Axis labels (conceptual) -->
<div class="absolute left-0 top-0 h-full flex flex-col justify-between text-outline-variant font-body-kecil text-[10px]">
<span>400</span>
<span>200</span>
<span>0</span>
</div>
<div class="w-full flex items-end justify-around pl-8 h-full">
<div class="w-1/12 bg-primary-fixed-dim hover:bg-primary rounded-t-sm transition-colors relative group" style="height: 40%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">160</div>
</div>
<div class="w-1/12 bg-primary-fixed-dim hover:bg-primary rounded-t-sm transition-colors relative group" style="height: 65%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">260</div>
</div>
<div class="w-1/12 bg-primary-fixed-dim hover:bg-primary rounded-t-sm transition-colors relative group" style="height: 50%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">200</div>
</div>
<div class="w-1/12 bg-primary-fixed-dim hover:bg-primary rounded-t-sm transition-colors relative group" style="height: 85%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">340</div>
</div>
<div class="w-1/12 bg-primary-container rounded-t-sm transition-colors relative group shadow-[0_0_10px_rgba(30,58,138,0.3)]" style="height: 95%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">380</div>
</div>
<div class="w-1/12 bg-primary-fixed-dim hover:bg-primary rounded-t-sm transition-colors relative group" style="height: 60%">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface font-body-kecil text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">240</div>
</div>
</div>
</div>
<!-- X-Axis Labels -->
<div class="flex justify-around pl-8 mt-2 text-on-surface-variant font-body-kecil text-body-kecil">
<span>Jan</span>
<span>Feb</span>
<span>Mar</span>
<span>Apr</span>
<span class="font-bold text-primary">Mei</span>
<span>Jun</span>
</div>
</div>
<!-- Quick Info Panel -->
<div class="col-span-12 lg:col-span-4 bg-surface rounded-xl border border-outline-variant p-6 flex flex-col gap-4">
<h3 class="font-h2-subjudul text-h2-subjudul text-on-background mb-2">Buku Paling Sering Dipinjam</h3>
<div class="flex items-center gap-4 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
<div class="w-12 h-16 bg-surface-container-high rounded border border-outline-variant flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-outline-variant" style="font-variation-settings: 'FILL' 0;">menu_book</span>
</div>
<div>
<h4 class="font-label-tombol text-label-tombol text-on-surface line-clamp-1">Laskar Pelangi</h4>
<p class="font-body-kecil text-body-kecil text-on-surface-variant">Andrea Hirata</p>
<p class="font-body-kecil text-[12px] text-primary mt-1 font-bold">45 kali dipinjam</p>
</div>
</div>
<div class="flex items-center gap-4 p-3 rounded-lg hover:bg-surface-container-low transition-colors">
<div class="w-12 h-16 bg-surface-container-high rounded border border-outline-variant flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-outline-variant" style="font-variation-settings: 'FILL' 0;">menu_book</span>
</div>
<div>
<h4 class="font-label-tombol text-label-tombol text-on-surface line-clamp-1">Bumi Manusia</h4>
<p class="font-body-kecil text-body-kecil text-on-surface-variant">Pramoedya A. Toer</p>
<p class="font-body-kecil text-[12px] text-primary mt-1 font-bold">38 kali dipinjam</p>
</div>
</div>
<button class="mt-auto w-full py-2 border border-primary text-primary font-label-tombol text-label-tombol rounded-lg hover:bg-primary-fixed transition-colors">
                    Lihat Laporan Lengkap
                </button>
</div>
</div>
<!-- Bottom Section: Data Table -->
<div class="bg-surface rounded-xl border border-outline-variant overflow-hidden flex flex-col">
<div class="p-6 border-b border-outline-variant flex items-center justify-between bg-surface-bright">
<h3 class="font-h2-subjudul text-h2-subjudul text-on-background">Transaksi Terbaru</h3>
<button class="flex items-center gap-2 text-primary font-label-tombol text-label-tombol hover:bg-primary-fixed-dim/20 px-3 py-1.5 rounded-lg transition-colors">
                    Lihat Semua <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr>
<th class="py-3 px-4 font-tabel-header text-tabel-header text-on-surface-variant uppercase tracking-wider">ID Transaksi</th>
<th class="py-3 px-4 font-tabel-header text-tabel-header text-on-surface-variant uppercase tracking-wider">Peminjam</th>
<th class="py-3 px-4 font-tabel-header text-tabel-header text-on-surface-variant uppercase tracking-wider">Judul Buku</th>
<th class="py-3 px-4 font-tabel-header text-tabel-header text-on-surface-variant uppercase tracking-wider">Tgl Pinjam</th>
<th class="py-3 px-4 font-tabel-header text-tabel-header text-on-surface-variant uppercase tracking-wider">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">#TRX-2023-089</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface font-semibold">Budi Santoso</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">Matematika Kelas IX</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface-variant">12 Mei 2024</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-container text-on-secondary-container border border-secondary/20">
                                    Dikembalikan
                                </span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">#TRX-2023-090</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface font-semibold">Siti Aminah</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">Sejarah Nasional Indonesia</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface-variant">14 Mei 2024</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-fixed text-on-primary-fixed border border-primary/20">
                                    Dipinjam
                                </span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">#TRX-2023-091</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface font-semibold">Reza Rahadian</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">Fisika Dasar Terapan</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface-variant">01 Mei 2024</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-container text-on-error-container border border-error/20">
                                    Terlambat
                                </span>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors">
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">#TRX-2023-092</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface font-semibold">Dewi Lestari</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface">Kumpulan Cerpen Modern</td>
<td class="py-3 px-4 font-body-kecil text-body-kecil text-on-surface-variant">15 Mei 2024</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-fixed text-on-primary-fixed border border-primary/20">
                                    Dipinjam
                                </span>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</main>
</body></html>