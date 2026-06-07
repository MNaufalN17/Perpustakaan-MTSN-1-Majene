<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'school_name.required' => 'Nama sekolah wajib diisi.',
            'library_name.required' => 'Nama perpustakaan wajib diisi.',
            'fine_per_day.required' => 'Denda per hari wajib diisi.',
            'loan_duration_days.required' => 'Masa pinjam default wajib diisi.',
            'max_normal_loan_items.required' => 'Maksimal buku peminjaman biasa wajib diisi.',
            'max_class_loan_items.required' => 'Maksimal eksemplar peminjaman kelas wajib diisi.',
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

            if (Schema::hasColumn('system_settings', 'key') && Schema::hasColumn('system_settings', 'value')) {
                return DB::table('system_settings')->where('key', $key)->value('value') ?? $default;
            }

            if (Schema::hasColumn('system_settings', 'setting_key') && Schema::hasColumn('system_settings', 'setting_value')) {
                return DB::table('system_settings')->where('setting_key', $key)->value('setting_value') ?? $default;
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
        $now = now();
        $label = $this->settingLabel($key);

        if (Schema::hasColumn('system_settings', 'key') && Schema::hasColumn('system_settings', 'value')) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                $this->filterColumns('system_settings', [
                    'key' => $key,
                    'value' => (string) $value,
                    'label' => $label,
                    'type' => is_numeric($value) ? 'integer' : 'string',
                    'description' => $this->settingDescription($key),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );

            return;
        }

        if (Schema::hasColumn('system_settings', 'setting_key') && Schema::hasColumn('system_settings', 'setting_value')) {
            DB::table('system_settings')->updateOrInsert(
                ['setting_key' => $key],
                $this->filterColumns('system_settings', [
                    'setting_key' => $key,
                    'setting_value' => (string) $value,
                    'label' => $label,
                    'type' => is_numeric($value) ? 'integer' : 'string',
                    'description' => $this->settingDescription($key),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    private function settingLabel(string $key): string
    {
        return match ($key) {
            'school_name' => 'Nama Sekolah',
            'library_name' => 'Nama Perpustakaan',
            'fine_per_day' => 'Denda per Hari',
            'loan_duration_days' => 'Masa Pinjam Default',
            'max_normal_loan_items' => 'Maksimal Buku Peminjaman Biasa',
            'max_class_loan_items' => 'Maksimal Eksemplar Peminjaman Kelas',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    private function settingDescription(string $key): string
    {
        return match ($key) {
            'school_name' => 'Nama sekolah yang ditampilkan pada sistem.',
            'library_name' => 'Nama perpustakaan yang ditampilkan pada sistem.',
            'fine_per_day' => 'Nominal denda keterlambatan per hari.',
            'loan_duration_days' => 'Jumlah hari default masa peminjaman.',
            'max_normal_loan_items' => 'Batas maksimal eksemplar untuk peminjaman biasa.',
            'max_class_loan_items' => 'Batas maksimal eksemplar untuk peminjaman kelas atau rombongan.',
            default => 'Pengaturan sistem.',
        };
    }

    private function filterColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(function ($value, string $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }
}