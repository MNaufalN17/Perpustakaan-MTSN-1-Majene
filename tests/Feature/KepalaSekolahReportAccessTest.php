<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KepalaSekolahReportAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->seedSettings();
    }

    public function test_guest_is_redirected_to_login_when_accessing_kepala_sekolah_reports(): void
    {
        $this->get(route('kepala_sekolah.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('kepala_sekolah.reports.index'))
            ->assertRedirect(route('login'));

        $this->get(route('kepala_sekolah.reports.collections'))
            ->assertRedirect(route('login'));

        $this->get(route('kepala_sekolah.reports.members'))
            ->assertRedirect(route('login'));

        $this->get(route('kepala_sekolah.reports.damaged_lost'))
            ->assertRedirect(route('login'));
    }

    public function test_kepala_sekolah_can_access_dashboard_and_report_pages(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $this->get(route('kepala_sekolah.dashboard'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.index'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.collections'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.members'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.damaged_lost'))
            ->assertOk();
    }

    public function test_kepala_sekolah_can_download_report_pdfs(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $this->get(route('kepala_sekolah.reports.download'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.collections.download'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.members.download'))
            ->assertOk();

        $this->get(route('kepala_sekolah.reports.damaged_lost.download'))
            ->assertOk();
    }

    public function test_pustakawan_cannot_access_kepala_sekolah_reports(): void
    {
        $this->actingAsRole(1);

        $this->get(route('kepala_sekolah.dashboard'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.index'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.collections'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.members'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.damaged_lost'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.collections.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.members.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.damaged_lost.download'))
            ->assertForbidden();
    }

    public function test_admin_it_cannot_access_kepala_sekolah_reports(): void
    {
        $this->actingAsRole(3);

        $this->get(route('kepala_sekolah.dashboard'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.index'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.collections'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.members'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.damaged_lost'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.collections.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.members.download'))
            ->assertForbidden();

        $this->get(route('kepala_sekolah.reports.damaged_lost.download'))
            ->assertForbidden();
    }

    private function prepareReportData(): void
    {
        $classId = $this->createStudentClass();

        $memberId = $this->createMember($classId, [
            'name' => 'Siswa Laporan',
            'nis_nip' => 'NIS-LAPORAN-001',
        ]);

        $bookId = $this->createBook('Buku Laporan Test');

        $availableItemId = $this->createBookItem($bookId, 'LAPORAN-001', 1, [
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $borrowedItemId = $this->createBookItem($bookId, 'LAPORAN-002', 2, [
            'status' => 'dipinjam',
            'condition' => 'baik',
        ]);

        $damagedItemId = $this->createBookItem($bookId, 'LAPORAN-003', 3, [
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);

        $lostItemId = $this->createBookItem($bookId, 'LAPORAN-004', 4, [
            'status' => 'hilang',
            'condition' => 'hilang',
        ]);

        $this->createActiveLoan($memberId, $borrowedItemId);

        $this->createFinishedLoan($memberId, $availableItemId);

        $this->assertDatabaseHas('book_items', [
            'id' => $damagedItemId,
            'status' => 'rusak',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $lostItemId,
            'status' => 'hilang',
        ]);
    }

    private function seedRoles(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        $roles = [
            1 => [
                'name' => 'Pustakawan',
                'role_name' => 'Pustakawan',
                'display_name' => 'Pustakawan',
                'code' => 'pustakawan',
                'guard_name' => 'web',
                'description' => 'Petugas perpustakaan',
            ],
            2 => [
                'name' => 'Kepala Sekolah',
                'role_name' => 'Kepala Sekolah',
                'display_name' => 'Kepala Sekolah',
                'code' => 'kepala_sekolah',
                'guard_name' => 'web',
                'description' => 'Kepala sekolah',
            ],
            3 => [
                'name' => 'Admin IT',
                'role_name' => 'Admin IT',
                'display_name' => 'Admin IT',
                'code' => 'admin',
                'guard_name' => 'web',
                'description' => 'Administrator sistem',
            ],
        ];

        foreach ($roles as $id => $payload) {
            DB::table('roles')->updateOrInsert(
                ['id' => $id],
                $this->filterColumns('roles', array_merge([
                    'id' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $payload))
            );
        }
    }

    private function seedSettings(): void
    {
        $this->setSetting('school_name', 'MTsN 1 Majene');
        $this->setSetting('library_name', 'Perpustakaan MTsN 1 Majene');
        $this->setSetting('fine_per_day', 500);
        $this->setSetting('loan_duration_days', 7);
        $this->setSetting('max_normal_loan_items', 3);
        $this->setSetting('max_class_loan_items', 40);
    }

    private function actingAsRole(int $roleId): User
    {
        $user = User::factory()->create([
            'role_id' => $roleId,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        return $user;
    }

    private function createStudentClass(): int
    {
        return $this->insertRow('classes', [
            'level' => 7,
            'class_name' => 'VII A',
            'name' => 'VII A',
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Test',
            'status' => 'aktif',
        ]);
    }

    private function createMember(?int $classId = null, array $overrides = []): int
    {
        $classId = $classId ?: $this->createStudentClass();

        return $this->insertRow('members', array_merge([
            'member_code' => 'AGT-' . uniqid(),
            'nis_nip' => 'NIS-' . random_int(10000, 99999),
            'name' => 'Anggota Test',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'class_id' => $classId,
            'phone' => '081234567890',
            'address' => 'Alamat Test',
            'status' => 'aktif',
            'joined_date' => now()->format('Y-m-d'),
        ], $overrides));
    }

    private function createCategory(): int
    {
        return $this->insertRow('categories', [
            'name' => 'Buku Pelajaran',
            'description' => 'Kategori test',
        ]);
    }

    private function createDdcClass(): int
    {
        return $this->insertRow('ddc_classes', [
            'code' => '400',
            'name' => 'Bahasa',
            'description' => 'Klasifikasi test',
        ]);
    }

    private function createBook(string $title = 'Buku Test'): int
    {
        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();

        return $this->insertRow('books', [
            'category_id' => $categoryId,
            'ddc_class_id' => $ddcClassId,
            'title' => $title,
            'author' => 'Penulis Test',
            'publisher' => 'Penerbit Test',
            'publication_year' => 2026,
            'isbn' => 'ISBN-' . uniqid(),
            'description' => 'Deskripsi test',
            'is_borrowable' => 1,
        ]);
    }

    private function createBookItem(int $bookId, string $itemCode, int $copyNumber, array $overrides = []): int
    {
        return $this->insertRow('book_items', array_merge([
            'book_id' => $bookId,
            'item_code' => $itemCode,
            'classification_code' => '400',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'copy_number' => $copyNumber,
            'status' => 'tersedia',
            'condition' => 'baik',
            'location' => 'Rak Test',
            'acquisition_date' => now()->format('Y-m-d'),
        ], $overrides));
    }

    private function createActiveLoan(int $memberId, int $bookItemId): int
    {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-ACTIVE-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->subDays(2)->format('Y-m-d'),
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan aktif laporan test',
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'status' => 'dipinjam',
            'late_days' => 0,
            'fine_amount' => 0,
            'renewal_count' => 0,
            'notes' => null,
        ]);

        return $loanId;
    }

    private function createFinishedLoan(int $memberId, int $bookItemId): int
    {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-FINISHED-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->subDays(14)->format('Y-m-d'),
            'due_date' => now()->subDays(7)->format('Y-m-d'),
            'return_date' => now()->subDays(6)->format('Y-m-d'),
            'status' => 'selesai',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan selesai laporan test',
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'return_date' => now()->subDays(6)->format('Y-m-d'),
            'status' => 'dikembalikan',
            'late_days' => 1,
            'fine_amount' => 500,
            'return_condition' => 'baik',
            'renewal_count' => 0,
            'notes' => null,
        ]);

        return $loanId;
    }

    private function setSetting(string $key, int|string $value): void
    {
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        $now = now();
        $label = ucwords(str_replace('_', ' ', $key));

        if (Schema::hasColumn('system_settings', 'key') && Schema::hasColumn('system_settings', 'value')) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                $this->filterColumns('system_settings', [
                    'key' => $key,
                    'value' => (string) $value,
                    'label' => $label,
                    'type' => is_numeric($value) ? 'integer' : 'string',
                    'description' => 'Setting test ' . $key,
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
                    'description' => 'Setting test ' . $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    private function insertRow(string $table, array $payload): int
    {
        if (Schema::hasColumn($table, 'created_at') && !array_key_exists('created_at', $payload)) {
            $payload['created_at'] = Carbon::now();
        }

        if (Schema::hasColumn($table, 'updated_at') && !array_key_exists('updated_at', $payload)) {
            $payload['updated_at'] = Carbon::now();
        }

        return (int) DB::table($table)->insertGetId(
            $this->filterColumns($table, $payload)
        );
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