<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookItemValidationAndBulkProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_book_item_cannot_use_duplicate_item_code(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $this->createBookItem($bookId, 'ITEM-DUP-001', 1);

        $response = $this->post(route('book_items.store'), [
            'book_id' => $bookId,
            'item_code' => 'ITEM-DUP-001',
            'classification_code' => '400',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'copy_number' => 2,
            'status' => 'tersedia',
            'condition' => 'baik',
            'location' => 'Rak Test',
            'acquisition_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('item_code');

        $count = DB::table('book_items')
            ->where('item_code', 'ITEM-DUP-001')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_book_item_can_be_created_with_valid_payload(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $response = $this->post(route('book_items.store'), [
            'book_id' => $bookId,
            'item_code' => 'ITEM-CREATE-001',
            'classification_code' => '400',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'copy_number' => 1,
            'status' => 'tersedia',
            'condition' => 'baik',
            'location' => 'Rak Test',
            'acquisition_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'book_id' => $bookId,
            'item_code' => 'ITEM-CREATE-001',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);
    }

    public function test_book_item_can_be_updated_when_not_borrowed(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'ITEM-UPDATE-001', 1);

        $response = $this->put(route('book_items.update', $bookItemId), [
            'book_id' => $bookId,
            'item_code' => 'ITEM-UPDATE-002',
            'classification_code' => '400',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'copy_number' => 2,
            'status' => 'rusak',
            'condition' => 'rusak ringan',
            'location' => 'Rak Update',
            'acquisition_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'item_code' => 'ITEM-UPDATE-002',
            'status' => 'rusak',
            'condition' => 'rusak ringan',
            'location' => 'Rak Update',
        ]);
    }

    public function test_borrowed_book_item_status_cannot_be_changed_to_available(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'ITEM-ACTIVE-001', 1, [
            'status' => 'dipinjam',
        ]);

        $this->createActiveLoan($bookItemId);

        $response = $this->put(route('book_items.update', $bookItemId), [
            'book_id' => $bookId,
            'item_code' => 'ITEM-ACTIVE-001',
            'classification_code' => '400',
            'author_code' => 'TES',
            'title_code' => 't',
            'title_initial' => 't',
            'copy_number' => 1,
            'status' => 'tersedia',
            'condition' => 'baik',
            'location' => 'Rak Test',
            'acquisition_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'book_item_id' => $bookItemId,
            'status' => 'dipinjam',
        ]);
    }

    public function test_bulk_destroy_deletes_book_items_without_loan_history(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'BULK-DELETE-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'BULK-DELETE-002', 2);

        $response = $this->delete(route('book_items.bulk_destroy'), [
            'book_item_ids' => [
                $firstItemId,
                $secondItemId,
            ],
        ]);

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseMissing('book_items', [
            'id' => $firstItemId,
        ]);

        $this->assertDatabaseMissing('book_items', [
            'id' => $secondItemId,
        ]);
    }

    public function test_bulk_destroy_nonactivates_book_items_with_loan_history(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'BULK-HISTORY-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'BULK-HISTORY-002', 2);

        $this->createFinishedLoanHistory($firstItemId);
        $this->createFinishedLoanHistory($secondItemId);

        $response = $this->delete(route('book_items.bulk_destroy'), [
            'book_item_ids' => [
                $firstItemId,
                $secondItemId,
            ],
        ]);

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $firstItemId,
            'status' => 'nonaktif',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $secondItemId,
            'status' => 'nonaktif',
        ]);
    }

    public function test_bulk_destroy_rejects_book_items_with_active_loan(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $availableItemId = $this->createBookItem($bookId, 'BULK-MIXED-001', 1);

        $borrowedItemId = $this->createBookItem($bookId, 'BULK-MIXED-002', 2, [
            'status' => 'dipinjam',
        ]);

        $this->createActiveLoan($borrowedItemId);

        $response = $this->delete(route('book_items.bulk_destroy'), [
            'book_item_ids' => [
                $availableItemId,
                $borrowedItemId,
            ],
        ]);

        $response->assertRedirect();

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

    public function test_bulk_destroy_requires_at_least_one_selected_book_item(): void
    {
        $this->actingAsRole(1);

        $response = $this->delete(route('book_items.bulk_destroy'), [
            'book_item_ids' => [],
        ]);

        $response->assertSessionHasErrors('book_item_ids');
    }

    public function test_nonactive_book_item_with_light_damage_can_be_restored_to_stock(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'RESTORE-LIGHT-001', 1, [
            'status' => 'nonaktif',
            'condition' => 'rusak ringan',
        ]);

        $response = $this->patch(route('book_items.restore_to_stock', $bookItemId));

        $response->assertRedirect(route('book_items.index'));

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
            'condition' => 'rusak ringan',
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

    private function createBook(string $title = 'Buku Test'): int
    {
        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();

        return $this->insertRow('books', [
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

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => 'dipinjam',
                'updated_at' => now(),
            ]));

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