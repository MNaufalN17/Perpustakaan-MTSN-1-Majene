<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanReturnAndCancelFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_partial_return_keeps_loan_active_and_restores_only_returned_item(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'RETURN-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'RETURN-002', 2);

        [$loanId, $loanItemIds] = $this->createActiveLoanWithItems([
            $firstItemId,
            $secondItemId,
        ]);

        $response = $this->patch(route('loans.update', $loanId), [
            'return_date' => now()->format('Y-m-d'),
            'loan_item_ids' => [
                $loanItemIds[$firstItemId],
            ],
            'return_conditions' => [
                $loanItemIds[$firstItemId] => 'baik',
            ],
            'notes' => 'Pengembalian sebagian',
        ]);

        $response->assertRedirect(route('loans.show', $loanId));

        $this->assertDatabaseHas('loans', [
            'id' => $loanId,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$firstItemId],
            'status' => 'dikembalikan',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$secondItemId],
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $firstItemId,
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $secondItemId,
            'status' => 'dipinjam',
        ]);
    }

    public function test_full_return_marks_loan_finished_and_restores_all_items(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'FULL-RETURN-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'FULL-RETURN-002', 2);

        [$loanId, $loanItemIds] = $this->createActiveLoanWithItems([
            $firstItemId,
            $secondItemId,
        ]);

        $response = $this->patch(route('loans.update', $loanId), [
            'return_date' => now()->format('Y-m-d'),
            'loan_item_ids' => array_values($loanItemIds),
            'return_conditions' => [
                $loanItemIds[$firstItemId] => 'baik',
                $loanItemIds[$secondItemId] => 'baik',
            ],
            'notes' => 'Pengembalian semua',
        ]);

        $response->assertRedirect(route('loans.show', $loanId));

        $this->assertDatabaseHas('loans', [
            'id' => $loanId,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $firstItemId,
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $secondItemId,
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);
    }

    public function test_late_return_calculates_fine_amount(): void
    {
        $this->actingAsRole(1);

        $this->setSetting('fine_per_day', 500);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'LATE-001', 1);

        $dueDate = now()->subDays(3)->format('Y-m-d');

        [$loanId, $loanItemIds] = $this->createActiveLoanWithItems(
            [$bookItemId],
            $dueDate,
            'terlambat'
        );

        $response = $this->patch(route('loans.update', $loanId), [
            'return_date' => now()->format('Y-m-d'),
            'loan_item_ids' => [
                $loanItemIds[$bookItemId],
            ],
            'return_conditions' => [
                $loanItemIds[$bookItemId] => 'baik',
            ],
            'notes' => 'Pengembalian terlambat',
        ]);

        $response->assertRedirect(route('loans.show', $loanId));

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$bookItemId],
            'status' => 'dikembalikan',
            'late_days' => 3,
            'fine_amount' => 1500,
        ]);

        $this->assertDatabaseHas('loans', [
            'id' => $loanId,
            'status' => 'selesai',
        ]);
    }

    public function test_return_with_heavy_damage_updates_book_item_status_and_condition(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'DAMAGE-001', 1);

        [$loanId, $loanItemIds] = $this->createActiveLoanWithItems([
            $bookItemId,
        ]);

        $response = $this->patch(route('loans.update', $loanId), [
            'return_date' => now()->format('Y-m-d'),
            'loan_item_ids' => [
                $loanItemIds[$bookItemId],
            ],
            'return_conditions' => [
                $loanItemIds[$bookItemId] => 'rusak berat',
            ],
            'notes' => 'Buku kembali rusak berat',
        ]);

        $response->assertRedirect(route('loans.show', $loanId));

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$bookItemId],
            'status' => 'dikembalikan',
            'return_condition' => 'rusak berat',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);
    }

    public function test_cancel_transaction_rejects_wrong_confirmation_code(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'CANCEL-WRONG-001', 1);

        [$loanId, $loanItemIds] = $this->createActiveLoanWithItems([
            $bookItemId,
        ]);

        $response = $this->delete(route('loans.destroy', $loanId), [
            'cancel_confirmation' => 'KODE-SALAH',
            'cancel_reason' => 'Test salah konfirmasi',
            'cancel_agreement' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'id' => $loanId,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$bookItemId],
            'status' => 'dipinjam',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'dipinjam',
        ]);
    }

    public function test_cancel_active_transaction_with_correct_confirmation_restores_stock_and_deletes_transaction(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $firstItemId = $this->createBookItem($bookId, 'CANCEL-001', 1);
        $secondItemId = $this->createBookItem($bookId, 'CANCEL-002', 2);

        [$loanId, $loanItemIds, $loanCode] = $this->createActiveLoanWithItems([
            $firstItemId,
            $secondItemId,
        ]);

        $response = $this->delete(route('loans.destroy', $loanId), [
            'cancel_confirmation' => $loanCode,
            'cancel_reason' => 'Salah input transaksi',
            'cancel_agreement' => '1',
        ]);

        $response->assertRedirect(route('loans.index'));

        $this->assertDatabaseMissing('loans', [
            'id' => $loanId,
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'id' => $loanItemIds[$firstItemId],
        ]);

        $this->assertDatabaseMissing('loan_items', [
            'id' => $loanItemIds[$secondItemId],
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $firstItemId,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $secondItemId,
            'status' => 'tersedia',
        ]);
    }

    public function test_finished_transaction_cannot_be_canceled(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook();

        $bookItemId = $this->createBookItem($bookId, 'FINISHED-CANCEL-001', 1);

        [$loanId, $loanItemIds, $loanCode] = $this->createActiveLoanWithItems([
            $bookItemId,
        ]);

        DB::table('loans')
            ->where('id', $loanId)
            ->update($this->filterColumns('loans', [
                'status' => 'selesai',
                'return_date' => now()->format('Y-m-d'),
                'updated_at' => now(),
            ]));

        DB::table('loan_items')
            ->where('id', $loanItemIds[$bookItemId])
            ->update($this->filterColumns('loan_items', [
                'status' => 'dikembalikan',
                'return_date' => now()->format('Y-m-d'),
                'return_condition' => 'baik',
                'updated_at' => now(),
            ]));

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => 'tersedia',
                'condition' => 'baik',
                'updated_at' => now(),
            ]));

        $response = $this->delete(route('loans.destroy', $loanId), [
            'cancel_confirmation' => $loanCode,
            'cancel_reason' => 'Mencoba batalkan transaksi selesai',
            'cancel_agreement' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'id' => $loanId,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('loan_items', [
            'id' => $loanItemIds[$bookItemId],
            'status' => 'dikembalikan',
        ]);

        $this->assertDatabaseHas('book_items', [
            'id' => $bookItemId,
            'status' => 'tersedia',
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

    private function createActiveLoanWithItems(array $bookItemIds, ?string $dueDate = null, string $loanStatus = 'aktif'): array
    {
        $memberId = $this->createMember();

        $loanCode = 'TRX-TEST-' . uniqid();

        $dueDate = $dueDate ?: now()->addDays(7)->format('Y-m-d');

        $loanDate = Carbon::parse($dueDate)
            ->subDays(7)
            ->format('Y-m-d');

        if (Carbon::parse($loanDate)->isFuture()) {
            $loanDate = now()->format('Y-m-d');
        }

        $loanId = $this->insertRow('loans', [
            'loan_code' => $loanCode,
            'member_id' => $memberId,
            'loan_date' => $loanDate,
            'due_date' => $dueDate,
            'status' => $loanStatus,
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan aktif test',
        ]);

        $loanItemIds = [];

        foreach ($bookItemIds as $bookItemId) {
            $loanItemStatus = $loanStatus === 'terlambat' ? 'terlambat' : 'dipinjam';

            $loanItemIds[$bookItemId] = $this->insertRow('loan_items', [
                'loan_id' => $loanId,
                'book_item_id' => $bookItemId,
                'status' => $loanItemStatus,
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
        }

        return [
            $loanId,
            $loanItemIds,
            $loanCode,
        ];
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