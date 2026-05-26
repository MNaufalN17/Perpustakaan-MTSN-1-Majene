<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        if ((int) auth()->user()->role_id !== 3) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $identitySettings = SystemSetting::where('setting_group', 'identity')
            ->orderBy('id')
            ->get();

        $circulationSettings = SystemSetting::where('setting_group', 'circulation')
            ->orderBy('id')
            ->get();

        return view('admin.settings.index', compact(
            'identitySettings',
            'circulationSettings'
        ));
    }

    public function update(Request $request)
    {
        if ((int) auth()->user()->role_id !== 3) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $validated = $request->validate([
            'settings' => ['required', 'array'],

            'settings.school_name' => ['required', 'string', 'max:255'],
            'settings.library_name' => ['required', 'string', 'max:255'],
            'settings.school_address' => ['nullable', 'string', 'max:1000'],
            'settings.head_librarian' => ['nullable', 'string', 'max:255'],

            'settings.loan_days' => ['required', 'integer', 'min:1', 'max:30'],
            'settings.max_books_per_loan' => ['required', 'integer', 'min:1', 'max:20'],
            'settings.fine_per_day' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success_title', 'Pengaturan berhasil diperbarui')
            ->with('success_message', 'Konfigurasi sistem berhasil disimpan.')
            ->with('success_detail', 'Pengaturan ini dapat digunakan pada peminjaman, denda, struk, dan laporan.');
    }
}