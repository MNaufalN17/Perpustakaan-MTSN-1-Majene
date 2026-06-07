<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SystemSettingController extends Controller
{
    public function index()
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 3) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $settings = [
            'school_name' => $this->settingValue('school_name', 'MTsN 1 Majene'),
            'library_name' => $this->settingValue('library_name', 'SIM Perpustakaan'),
            'fine_per_day' => $this->settingInt('fine_per_day', 500),
            'loan_duration_days' => $this->settingInt('loan_duration_days', 7),
            'max_normal_loan_items' => $this->settingInt('max_normal_loan_items', 3),
            'max_class_loan_items' => $this->settingInt('max_class_loan_items', 40),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 3) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'library_name' => ['required', 'string', 'max:150'],
            'fine_per_day' => ['required', 'integer', 'min:0', 'max:1000000'],
            'loan_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'max_normal_loan_items' => ['required', 'integer', 'min:1', 'max:200'],
            'max_class_loan_items' => ['required', 'integer', 'min:1', 'max:500'],
        ], [
            'max_normal_loan_items.required' => 'Maksimal buku peminjaman biasa wajib diisi.',
            'max_normal_loan_items.integer' => 'Maksimal buku peminjaman biasa harus berupa angka.',
            'max_class_loan_items.required' => 'Maksimal eksemplar peminjaman kelas wajib diisi.',
            'max_class_loan_items.integer' => 'Maksimal eksemplar peminjaman kelas harus berupa angka.',
        ]);

        foreach ($validated as $key => $value) {
            $this->saveSetting($key, $value);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success_title', 'Pengaturan berhasil disimpan')
            ->with('success_message', 'Pengaturan sistem perpustakaan berhasil diperbarui.')
            ->with('success_detail', 'Batas peminjaman biasa dan peminjaman kelas sudah mengikuti pengaturan terbaru.');
    }

    private function settingValue(string $key, mixed $default = null): mixed
    {
        try {
            if (method_exists(SystemSetting::class, 'getValue')) {
                return SystemSetting::getValue($key, $default);
            }

            if (Schema::hasColumn('system_settings', 'key')) {
                return SystemSetting::where('key', $key)->value('value') ?? $default;
            }

            if (Schema::hasColumn('system_settings', 'setting_key')) {
                return SystemSetting::where('setting_key', $key)->value('setting_value') ?? $default;
            }
        } catch (\Throwable $e) {
            return $default;
        }

        return $default;
    }

    private function settingInt(string $key, int $default = 0): int
    {
        try {
            if (method_exists(SystemSetting::class, 'intValue')) {
                return (int) SystemSetting::intValue($key, $default);
            }

            return (int) $this->settingValue($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function saveSetting(string $key, mixed $value): void
    {
        if (Schema::hasColumn('system_settings', 'key') && Schema::hasColumn('system_settings', 'value')) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                $this->settingPayload($key, $value, 'value')
            );

            return;
        }

        if (Schema::hasColumn('system_settings', 'setting_key') && Schema::hasColumn('system_settings', 'setting_value')) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                $this->settingPayload($key, $value, 'setting_value')
            );

            return;
        }

        SystemSetting::updateOrCreate(
            ['key' => $key],
            $this->settingPayload($key, $value, 'value')
        );
    }

    private function settingPayload(string $key, mixed $value, string $valueColumn): array
    {
        $metadata = $this->settingMetadata($key);
        $payload = [
            $valueColumn => (string) $value,
        ];

        if (Schema::hasColumn('system_settings', 'label')) {
            $payload['label'] = $metadata['label'];
        }

        if (Schema::hasColumn('system_settings', 'type')) {
            $payload['type'] = $metadata['type'];
        }

        if (Schema::hasColumn('system_settings', 'setting_group')) {
            $payload['setting_group'] = $metadata['group'];
        }

        if (Schema::hasColumn('system_settings', 'description')) {
            $payload['description'] = $metadata['description'];
        }

        return $payload;
    }

    private function settingMetadata(string $key): array
    {
        return [
            'school_name' => [
                'label' => 'Nama Sekolah',
                'type' => 'text',
                'group' => 'identity',
                'description' => 'Nama sekolah atau madrasah yang digunakan pada sistem.',
            ],
            'library_name' => [
                'label' => 'Nama Perpustakaan',
                'type' => 'text',
                'group' => 'identity',
                'description' => 'Nama perpustakaan yang tampil pada halaman dan laporan.',
            ],
            'fine_per_day' => [
                'label' => 'Denda per Hari',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Nominal denda keterlambatan per eksemplar per hari.',
            ],
            'loan_duration_days' => [
                'label' => 'Masa Pinjam Default',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Jumlah hari peminjaman default.',
            ],
            'max_normal_loan_items' => [
                'label' => 'Maksimal Buku Peminjaman Biasa',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Jumlah maksimal eksemplar dalam satu transaksi peminjaman biasa.',
            ],
            'max_class_loan_items' => [
                'label' => 'Maksimal Eksemplar Peminjaman Kelas',
                'type' => 'number',
                'group' => 'circulation',
                'description' => 'Jumlah maksimal eksemplar dalam satu transaksi peminjaman kelas.',
            ],
        ][$key] ?? [
            'label' => str($key)->replace('_', ' ')->title()->toString(),
            'type' => 'text',
            'group' => 'general',
            'description' => null,
        ];
    }
}
