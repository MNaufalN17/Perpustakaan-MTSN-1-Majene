<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LibraryBusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_admin_can_update_loan_limit_settings(): void
    {
        $this->actingAsRole(3);

        $response = $this->put(route('admin.settings.update'), [
            'school_name' => 'MTsN 1 Majene',
            'library_name' => 'Perpustakaan MTsN 1 Majene',
            'fine_per_day' => 500,
            'loan_duration_days' => 7,
            'max_normal_loan_items' => 5,
            'max_class_loan_items' => 50,
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertSame(5, $this->getSettingInt('max_normal_loan_items', 0));
        $this->assertSame(50, $this->getSettingInt('max_class_loan_items', 0));
    }

    public function test_normal_loan_rejects_items_more_than_admin_limit(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 1);
        $this->setSetting('loan_duration_days', 7);

        $memberId = $this->createMember();
        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'BOOK-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'BOOK-002', 2);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$firstItemId, $secondItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test melebihi batas',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertDatabaseHas('book_items', [
            'id' => $firstItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $secondItemId,
            'status' => 'tersedia',
        ]);
    }

    public function test_normal_loan_can_be_created_and_book_item_becomes_borrowed(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);
        $this->setSetting('loan_duration_days', 7);

        $memberId = $this->createMember();
        $bookId = $this->createBook();
        $bookItemId = $this->createBookItem($bookId, 'BOOK-003', 1);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Peminjaman normal',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('loan_items', [
            'book_item_id' => $bookItemId,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'dipinjam',
        ]);
    }

    public function test_class_bulk_loan_can_borrow_many_copies_for_one_representative(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_class_loan_items', 40);
        $this->setSetting('loan_duration_days', 7);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);
        $bookId = $this->createBook('Buku Paket Bahasa Indonesia');

        $bookItemIds = [];

        for ($i = 1; $i <= 30; $i++) {
            $bookItemIds[] = $this->createBookItem(
                $bookId,
                'KELAS-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                $i
            );
        }

        $response = $this->post(route('loans.class_bulk.store'), [
            'member_id' => $memberId,
            'student_class_id' => $classId,
            'book_id' => $bookId,
            'book_item_ids' => $bookItemIds,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Dipinjam untuk satu kelas',
        ]);

        $response->assertRedirect();

        $borrowedCount = DB::table('book_items')
            ->whereIn('id', $bookItemIds)
            ->where('status', 'dipinjam')
            ->count();

        $this->assertSame(30, $borrowedCount);

        $loanItemCount = DB::table('loan_items')
            ->whereIn('book_item_id', $bookItemIds)
            ->where('status', 'dipinjam')
            ->count();

        $this->assertSame(30, $loanItemCount);
    }

    public function test_book_item_without_loan_history_can_be_deleted_permanently(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();
        $bookItemId = $this->createBookItem($bookId, 'DELETE-001', 1);

        $response = $this->delete(route('book_items.destroy', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseMissing('book_items', [
            'id' => $bookItemId,
        ]);
    }

    public function test_book_item_with_loan_history_becomes_nonactive_instead_of_deleted(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();
        $bookItemId = $this->createBookItem($bookId, 'ARCHIVE-001', 1);

        $this->createFinishedLoanHistory($bookItemId);

        $response = $this->delete(route('book_items.destroy', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'nonaktif',
        ]);
    }

    public function test_book_item_with_active_loan_cannot_be_deleted_or_nonactivated(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'ACTIVE-001', 1, [
            'status' => 'dipinjam',
        ]);

        $this->createActiveLoan($bookItemId);

        $response = $this->delete(route('book_items.destroy', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'book_item_id' => $bookItemId,
            'status' => 'dipinjam',
        ]);
    }

    public function test_nonactive_book_item_with_good_condition_can_be_restored_to_stock(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'RESTORE-001', 1, [
            'status' => 'nonaktif',
            'condition' => 'baik',
        ]);

        $response = $this->patch(route('book_items.restore_to_stock', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);
    }

    public function test_nonactive_book_item_with_heavy_damage_cannot_be_restored_to_stock(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'RESTORE-002', 1, [
            'status' => 'nonaktif',
            'condition' => 'rusak berat',
        ]);

        $response = $this->patch(route('book_items.restore_to_stock', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'nonaktif',
            'condition' => 'rusak berat',
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

    private function createMember(?int $classId = null): int
    {
        $classId = $classId ?: $this->createStudentClass();

        return $this->insertRow('members', [
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
        ]);
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

    private function createActiveLoan(int $bookItemId): int
    {
        $memberId = $this->createMember();

        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-ACTIVE-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'aktif',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan aktif test',
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

    private function createFinishedLoanHistory(int $bookItemId): int
    {
        $memberId = $this->createMember();

        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-FINISHED-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->subDays(10)->format('Y-m-d'),
            'due_date' => now()->subDays(3)->format('Y-m-d'),
            'return_date' => now()->format('Y-m-d'),
            'status' => 'selesai',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan selesai test',
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'return_date' => now()->format('Y-m-d'),
            'status' => 'dikembalikan',
            'late_days' => 0,
            'fine_amount' => 0,
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

    private function getSettingInt(string $key, int $default = 0): int
    {
        if (!Schema::hasTable('system_settings')) {
            return $default;
        }

        if (Schema::hasColumn('system_settings', 'key') && Schema::hasColumn('system_settings', 'value')) {
            $value = DB::table('system_settings')
                ->where('key', $key)
                ->value('value');

            return $value !== null ? (int) $value : $default;
        }

        if (Schema::hasColumn('system_settings', 'setting_key') && Schema::hasColumn('system_settings', 'setting_value')) {
            $value = DB::table('system_settings')
                ->where('setting_key', $key)
                ->value('setting_value');

            return $value !== null ? (int) $value : $default;
        }

        return $default;
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