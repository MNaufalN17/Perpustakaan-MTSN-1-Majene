<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanValidationEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_normal_loan_rejects_inactive_member(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);

        $memberId = $this->createMember(null, [
            'status' => 'nonaktif',
        ]);

        $bookId = $this->createBook();
        $bookItemId = $this->createBookItem($bookId, 'EDGE-INACTIVE-MEMBER-001', 1);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test anggota nonaktif',
        ]);

        $response->assertSessionHasErrors('member_id');

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'book_item_id' => $bookItemId,
        ]);
    }

    public function test_normal_loan_rejects_unavailable_book_item(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);

        $memberId = $this->createMember();
        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'EDGE-RUSAK-001', 1, [
            'status' => 'rusak',
            'condition' => 'rusak ringan',
        ]);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test item tidak tersedia',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'rusak',
            'condition' => 'rusak ringan',
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'book_item_id' => $bookItemId,
        ]);
    }

    public function test_normal_loan_rejects_nonactive_book_item(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);

        $memberId = $this->createMember();
        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'EDGE-NONAKTIF-001', 1, [
            'status' => 'nonaktif',
            'condition' => 'baik',
        ]);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test item nonaktif',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'nonaktif',
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'book_item_id' => $bookItemId,
        ]);
    }

    public function test_normal_loan_rejects_due_date_before_loan_date(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);

        $memberId = $this->createMember();
        $bookId = $this->createBook();
        $bookItemId = $this->createBookItem($bookId, 'EDGE-DATE-001', 1);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->subDay()->format('Y-m-d'),
            'notes' => 'Test tanggal tidak valid',
        ]);

        $response->assertSessionHasErrors('due_date');

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'book_item_id' => $bookItemId,
        ]);
    }

    public function test_normal_loan_rejects_non_borrowable_book(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_normal_loan_items', 3);

        $memberId = $this->createMember();

        $bookId = $this->createBook('Buku Referensi Tidak Dipinjam', [
            'is_borrowable' => 0,
            'borrowing_status' => 'tidak bisa dipinjam',
        ]);

        $bookItemId = $this->createBookItem($bookId, 'EDGE-NON-BORROWABLE-001', 1);

        $response = $this->post(route('loans.store'), [
            'member_id' => $memberId,
            'book_item_ids' => [$bookItemId],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test buku tidak bisa dipinjam',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'book_item_id' => $bookItemId,
        ]);
    }

    public function test_class_bulk_loan_rejects_items_more_than_admin_limit(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_class_loan_items', 2);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);
        $bookId = $this->createBook('Buku Paket Edge Limit');

        $firstItemId = $this->createBookItem($bookId, 'EDGE-CLASS-LIMIT-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'EDGE-CLASS-LIMIT-002', 2);
        $thirdItemId = $this->createBookItem($bookId, 'EDGE-CLASS-LIMIT-003', 3);

        $response = $this->post(route('loans.class_bulk.store'), [
            'member_id' => $memberId,
            'student_class_id' => $classId,
            'book_id' => $bookId,
            'book_item_ids' => [
                $firstItemId,
                $secondItemId,
                $thirdItemId,
            ],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test melebihi batas kelas',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertSame(3, DB::table('book_items')
            ->whereIn('id', [$firstItemId, $secondItemId, $thirdItemId])
            ->where('status', 'tersedia')
            ->count());

        $this->assertSame(0, DB::table('loan_items')
            ->whereIn('book_item_id', [$firstItemId, $secondItemId, $thirdItemId])
            ->count());
    }

    public function test_class_bulk_loan_rejects_items_from_different_book(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_class_loan_items', 40);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);

        $firstBookId = $this->createBook('Buku Paket Pertama');
        $secondBookId = $this->createBook('Buku Paket Kedua');

        $firstItemId = $this->createBookItem($firstBookId, 'EDGE-CLASS-BOOK-001', 1);
        $secondItemId = $this->createBookItem($secondBookId, 'EDGE-CLASS-BOOK-002', 1);

        $response = $this->post(route('loans.class_bulk.store'), [
            'member_id' => $memberId,
            'student_class_id' => $classId,
            'book_id' => $firstBookId,
            'book_item_ids' => [
                $firstItemId,
                $secondItemId,
            ],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test beda jenis buku',
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

    public function test_class_bulk_loan_rejects_active_borrowed_item(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('max_class_loan_items', 40);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);
        $bookId = $this->createBook('Buku Paket Borrowed');

        $availableItemId = $this->createBookItem($bookId, 'EDGE-CLASS-ACTIVE-001', 1);

        $borrowedItemId = $this->createBookItem($bookId, 'EDGE-CLASS-ACTIVE-002', 2, [
            'status' => 'dipinjam',
        ]);

        $this->createActiveLoan($borrowedItemId);

        $response = $this->post(route('loans.class_bulk.store'), [
            'member_id' => $memberId,
            'student_class_id' => $classId,
            'book_id' => $bookId,
            'book_item_ids' => [
                $availableItemId,
                $borrowedItemId,
            ],
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'Test item sedang dipinjam',
        ]);

        $response->assertSessionHasErrors('book_item_ids');

        $this->assertDatabaseHas('book_items', [
            'id' => $availableItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $borrowedItemId,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'book_item_id' => $borrowedItemId,
            'status' => 'dipinjam',
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

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => 'dipinjam',
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