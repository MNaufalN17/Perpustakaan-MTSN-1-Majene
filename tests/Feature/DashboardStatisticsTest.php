<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->setSetting('fine_per_day', 500);
        $this->setSetting('loan_duration_days', 7);
        $this->setSetting('max_normal_loan_items', 3);
        $this->setSetting('max_class_loan_items', 40);
    }

    public function test_dashboard_redirects_admin_and_kepala_sekolah_to_their_dashboards(): void
    {
        $this->actingAsRole(3);

        $this->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAsRole(2);

        $this->get(route('dashboard'))
            ->assertRedirect(route('kepala_sekolah.dashboard'));
    }

    public function test_pustakawan_dashboard_statistics_are_calculated_correctly(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('fine_per_day', 500);

        $classId = $this->createStudentClass();

        $activeMemberId = $this->createMember($classId, [
            'status' => 'aktif',
            'name' => 'Anggota Aktif Satu',
        ]);

        $this->createMember($classId, [
            'status' => 'aktif',
            'name' => 'Anggota Aktif Dua',
        ]);

        $this->createMember($classId, [
            'status' => 'nonaktif',
            'name' => 'Anggota Nonaktif',
        ]);

        $bookId = $this->createBook('Buku Dashboard Statistik');

        $todayBorrowedItemId = $this->createBookItem($bookId, 'DASH-001', 1);
        $overdueItemId = $this->createBookItem($bookId, 'DASH-002', 2);
        $availableItemId = $this->createBookItem($bookId, 'DASH-003', 3);
        $damagedItemId = $this->createBookItem($bookId, 'DASH-004', 4, [
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);

        $this->createLoanWithItem(
            memberId: $activeMemberId,
            bookItemId: $todayBorrowedItemId,
            loanDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(7)->format('Y-m-d'),
            loanStatus: 'aktif',
            loanItemStatus: 'dipinjam',
            createdAt: now()->subHour()
        );

        $this->createLoanWithItem(
            memberId: $activeMemberId,
            bookItemId: $overdueItemId,
            loanDate: now()->subDays(5)->format('Y-m-d'),
            dueDate: now()->subDays(2)->format('Y-m-d'),
            loanStatus: 'aktif',
            loanItemStatus: 'dipinjam',
            createdAt: now()
        );

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('pustakawan.dashboard')
            ->assertViewHas('totalBooks', 4)
            ->assertViewHas('activeMembers', 2)
            ->assertViewHas('loansToday', 1)
            ->assertViewHas('activeLoans', 2)
            ->assertViewHas('overdueLoansCount', 1)
            ->assertViewHas('estimatedFines', function ($value) {
                return (int) $value === 1000;
            });

        $this->assertDatabaseHas('book_items', [
            'id' => $availableItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $damagedItemId,
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);
    }

    public function test_dashboard_estimated_fines_follow_admin_setting(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('fine_per_day', 750);

        $classId = $this->createStudentClass();

        $memberId = $this->createMember($classId);

        $bookId = $this->createBook('Buku Denda Dashboard');

        $bookItemId = $this->createBookItem($bookId, 'DASH-FINE-001', 1);

        $this->createLoanWithItem(
            memberId: $memberId,
            bookItemId: $bookItemId,
            loanDate: now()->subDays(10)->format('Y-m-d'),
            dueDate: now()->subDays(4)->format('Y-m-d'),
            loanStatus: 'terlambat',
            loanItemStatus: 'terlambat',
            createdAt: now()
        );

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('estimatedFines', function ($value) {
                return (int) $value === 3000;
            })
            ->assertViewHas('overdueLoansCount', 1);
    }

    public function test_dashboard_recent_loans_are_ordered_from_latest(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $memberId = $this->createMember($classId);

        $bookId = $this->createBook('Buku Riwayat Dashboard');

        $oldItemId = $this->createBookItem($bookId, 'DASH-RECENT-001', 1);
        $newItemId = $this->createBookItem($bookId, 'DASH-RECENT-002', 2);

        $oldLoanId = $this->createLoanWithItem(
            memberId: $memberId,
            bookItemId: $oldItemId,
            loanDate: now()->subDays(2)->format('Y-m-d'),
            dueDate: now()->addDays(5)->format('Y-m-d'),
            loanStatus: 'aktif',
            loanItemStatus: 'dipinjam',
            createdAt: now()->subDays(2)
        );

        $newLoanId = $this->createLoanWithItem(
            memberId: $memberId,
            bookItemId: $newItemId,
            loanDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(7)->format('Y-m-d'),
            loanStatus: 'aktif',
            loanItemStatus: 'dipinjam',
            createdAt: now()
        );

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('recentLoans', function ($recentLoans) use ($newLoanId, $oldLoanId) {
                return $recentLoans->count() >= 2
                    && (int) $recentLoans->first()->id === (int) $newLoanId
                    && $recentLoans->pluck('id')->contains($oldLoanId);
            });
    }

    public function test_admin_dashboard_page_can_be_accessed_by_admin_only(): void
    {
        $this->actingAsRole(3);

        $this->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAsRole(1);

        $this->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAsRole(2);

        $this->get(route('admin.dashboard'))
            ->assertForbidden();
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
            'class_name' => 'VII-' . strtoupper(substr(uniqid(), -4)),
            'name' => 'VII Test',
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
            'name' => 'Kategori-' . uniqid(),
            'description' => 'Kategori test',
        ]);
    }

    private function createDdcClass(): int
    {
        return $this->insertRow('ddc_classes', [
            'code' => (string) random_int(100, 999),
            'name' => 'DDC Test ' . uniqid(),
            'description' => 'Klasifikasi test',
        ]);
    }

    private function createBook(string $title = 'Buku Test', array $overrides = []): int
    {
        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();

        return $this->insertRow('books', array_merge([
            'category_id' => $categoryId,
            'ddc_class_id' => $ddcClassId,
            'title' => $title . ' ' . uniqid(),
            'author' => 'Penulis Test',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'publisher' => 'Penerbit Test',
            'publication_year' => 2026,
            'isbn' => 'ISBN-' . uniqid(),
            'price' => 0,
            'description' => 'Deskripsi test',
            'classification_code' => '400',
            'borrowing_status' => 'bisa dipinjam',
            'is_borrowable' => 1,
        ], $overrides));
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

    private function createLoanWithItem(
        int $memberId,
        int $bookItemId,
        string $loanDate,
        string $dueDate,
        string $loanStatus = 'aktif',
        string $loanItemStatus = 'dipinjam',
        ?Carbon $createdAt = null
    ): int {
        $createdAt = $createdAt ?: now();

        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-DASH-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => $loanDate,
            'due_date' => $dueDate,
            'status' => $loanStatus,
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan dashboard test',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'status' => $loanItemStatus,
            'late_days' => 0,
            'fine_amount' => 0,
            'renewal_count' => 0,
            'notes' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => in_array($loanItemStatus, ['dipinjam', 'terlambat'], true) ? 'dipinjam' : 'tersedia',
                'updated_at' => now(),
            ]));

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