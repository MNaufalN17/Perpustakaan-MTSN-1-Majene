<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IndexFilterAndSearchFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_loans_index_can_filter_by_status(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);
        $bookId = $this->createBook('Buku Filter Pinjaman');

        $activeItemId = $this->createBookItem($bookId, 'FILTER-LOAN-ACTIVE-001', 1);
        $finishedItemId = $this->createBookItem($bookId, 'FILTER-LOAN-FINISHED-001', 2);

        $activeLoanId = $this->createLoanWithItem(
            memberId: $memberId,
            bookItemId: $activeItemId,
            loanStatus: 'aktif',
            loanItemStatus: 'dipinjam'
        );

        $finishedLoanId = $this->createLoanWithItem(
            memberId: $memberId,
            bookItemId: $finishedItemId,
            loanStatus: 'selesai',
            loanItemStatus: 'dikembalikan'
        );

        $response = $this->get(route('loans.index', [
            'status' => 'aktif',
        ]));

        $response->assertOk()
            ->assertViewHas('loans', function ($loans) use ($activeLoanId, $finishedLoanId) {
                $ids = $this->paginatorIds($loans);

                return $ids->contains($activeLoanId)
                    && !$ids->contains($finishedLoanId);
            });
    }

    public function test_loans_index_can_search_by_member_name(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $targetMemberId = $this->createMember($classId, [
            'name' => 'Siswa Pencarian Pinjaman',
            'nis_nip' => 'NIS-SEARCH-LOAN-001',
        ]);

        $otherMemberId = $this->createMember($classId, [
            'name' => 'Siswa Tidak Cocok',
            'nis_nip' => 'NIS-SEARCH-LOAN-002',
        ]);

        $bookId = $this->createBook('Buku Search Loan');

        $targetItemId = $this->createBookItem($bookId, 'SEARCH-LOAN-001', 1);
        $otherItemId = $this->createBookItem($bookId, 'SEARCH-LOAN-002', 2);

        $targetLoanId = $this->createLoanWithItem($targetMemberId, $targetItemId);
        $otherLoanId = $this->createLoanWithItem($otherMemberId, $otherItemId);

        $response = $this->get(route('loans.index', $this->searchPayload('Pencarian Pinjaman')));

        $response->assertOk()
            ->assertViewHas('loans', function ($loans) use ($targetLoanId, $otherLoanId) {
                $ids = $this->paginatorIds($loans);

                return $ids->contains($targetLoanId)
                    && !$ids->contains($otherLoanId);
            });
    }

    public function test_book_items_index_can_filter_by_status_and_condition(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook('Buku Filter Eksemplar');

        $targetItemId = $this->createBookItem($bookId, 'FILTER-ITEM-RUSAK-001', 1, [
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);

        $otherItemId = $this->createBookItem($bookId, 'FILTER-ITEM-BAIK-001', 2, [
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $response = $this->get(route('book_items.index', [
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]));

        $response->assertOk()
            ->assertViewHas('bookItems', function ($bookItems) use ($targetItemId, $otherItemId) {
                $ids = $this->paginatorIds($bookItems);

                return $ids->contains($targetItemId)
                    && !$ids->contains($otherItemId);
            });
    }

    public function test_book_items_index_can_search_by_item_code_or_book_title(): void
    {
        $this->actingAsRole(1);

        $targetBookId = $this->createBook('Judul Eksemplar Dicari');
        $otherBookId = $this->createBook('Judul Eksemplar Lain');

        $targetItemId = $this->createBookItem($targetBookId, 'SEARCH-ITEM-TARGET-001', 1);
        $otherItemId = $this->createBookItem($otherBookId, 'SEARCH-ITEM-OTHER-001', 1);

        $response = $this->get(route('book_items.index', $this->searchPayload('Eksemplar Dicari')));

        $response->assertOk()
            ->assertViewHas('bookItems', function ($bookItems) use ($targetItemId, $otherItemId) {
                $ids = $this->paginatorIds($bookItems);

                return $ids->contains($targetItemId)
                    && !$ids->contains($otherItemId);
            });
    }

    public function test_books_index_can_search_by_title(): void
    {
        $this->actingAsRole(1);

        $targetBookId = $this->createBook('Buku Index Dicari');
        $otherBookId = $this->createBook('Buku Index Lain');

        $response = $this->get(route('books.index', $this->searchPayload('Index Dicari')));

        $response->assertOk()
            ->assertViewHas('books', function ($books) use ($targetBookId, $otherBookId) {
                $ids = $this->paginatorIds($books);

                return $ids->contains($targetBookId)
                    && !$ids->contains($otherBookId);
            });
    }

    public function test_members_index_can_search_by_name_or_nis(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $targetMemberId = $this->createMember($classId, [
            'name' => 'Anggota Index Dicari',
            'nis_nip' => 'NIS-INDEX-TARGET-001',
        ]);

        $otherMemberId = $this->createMember($classId, [
            'name' => 'Anggota Index Lain',
            'nis_nip' => 'NIS-INDEX-OTHER-001',
        ]);

        $response = $this->get(route('members.index', $this->searchPayload('Index Dicari')));

        $response->assertOk()
            ->assertViewHas('members', function ($members) use ($targetMemberId, $otherMemberId) {
                $ids = $this->paginatorIds($members);

                return $ids->contains($targetMemberId)
                    && !$ids->contains($otherMemberId);
            });
    }

    public function test_members_index_can_filter_by_status(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $activeMemberId = $this->createMember($classId, [
            'name' => 'Anggota Aktif Filter',
            'status' => 'aktif',
        ]);

        $inactiveMemberId = $this->createMember($classId, [
            'name' => 'Anggota Nonaktif Filter',
            'status' => 'nonaktif',
        ]);

        $response = $this->get(route('members.index', [
            'status' => 'aktif',
            'member_status' => 'aktif',
            'filter_status' => 'aktif',
        ]));

        $response->assertOk()
            ->assertViewHas('members', function ($members) use ($activeMemberId, $inactiveMemberId) {
                $ids = $this->paginatorIds($members);

                return $ids->contains($activeMemberId)
                    && !$ids->contains($inactiveMemberId);
            });
    }

    public function test_categories_index_can_search_by_name(): void
    {
        $this->actingAsRole(1);

        $targetCategoryId = $this->createCategory('Kategori Index Dicari');
        $otherCategoryId = $this->createCategory('Kategori Index Lain');

        $response = $this->get(route('categories.index', $this->searchPayload('Index Dicari')));

        $response->assertOk()
            ->assertViewHas('categories', function ($categories) use ($targetCategoryId, $otherCategoryId) {
                $ids = $this->paginatorIds($categories);

                return $ids->contains($targetCategoryId)
                    && !$ids->contains($otherCategoryId);
            });
    }

    public function test_ddc_index_can_search_by_code_or_name(): void
    {
        $this->actingAsRole(1);

        $targetDdcId = $this->createDdcClass('811', 'DDC Index Dicari');
        $otherDdcId = $this->createDdcClass('812', 'DDC Index Lain');

        $response = $this->get(route('ddc.index', $this->searchPayload('Index Dicari')));

        $response->assertOk()
            ->assertViewHas('ddcClasses', function ($ddcClasses) use ($targetDdcId, $otherDdcId) {
                $ids = $this->paginatorIds($ddcClasses);

                return $ids->contains($targetDdcId)
                    && !$ids->contains($otherDdcId);
            });
    }

    public function test_classes_index_can_search_by_class_name_or_academic_year(): void
    {
        $this->actingAsRole(1);

        $targetClassId = $this->createStudentClass([
            'class_name' => 'IX Search Target',
            'name' => 'IX Search Target',
            'academic_year' => '2027/2028',
        ]);

        $otherClassId = $this->createStudentClass([
            'class_name' => 'IX Search Other',
            'name' => 'IX Search Other',
            'academic_year' => '2026/2027',
        ]);

        $response = $this->get(route('classes.index', $this->searchPayload('2027/2028')));

        $response->assertOk()
            ->assertViewHas('classes', function ($classes) use ($targetClassId, $otherClassId) {
                $ids = $this->paginatorIds($classes);

                return $ids->contains($targetClassId)
                    && !$ids->contains($otherClassId);
            });
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

    private function searchPayload(string $keyword): array
    {
        return [
            'keyword' => $keyword,
            'search' => $keyword,
            'q' => $keyword,
        ];
    }

    private function paginatorIds(mixed $paginator)
    {
        if (is_object($paginator) && method_exists($paginator, 'getCollection')) {
            return $paginator->getCollection()
                ->pluck('id')
                ->map(fn ($id) => (int) $id);
        }

        return collect($paginator)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    private function createStudentClass(array $overrides = []): int
    {
        return $this->insertRow('classes', array_merge([
            'level' => 7,
            'class_name' => 'VII-' . strtoupper(substr(uniqid(), -4)),
            'name' => 'VII Test',
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Test',
            'status' => 'aktif',
        ], $overrides));
    }

    private function createMember(?int $classId = null, array $overrides = []): int
    {
        $classId = $classId ?: $this->createStudentClass();

        return $this->insertRow('members', array_merge([
            'member_code' => 'AGT-' . uniqid(),
            'nis_nip' => 'NIS-' . random_int(10000, 99999),
            'name' => 'Anggota Test ' . uniqid(),
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

    private function createCategory(string $name = 'Kategori Test'): int
    {
        return $this->insertRow('categories', [
            'name' => $name . ' ' . uniqid(),
            'description' => 'Kategori test',
        ]);
    }

    private function createDdcClass(?string $code = null, string $name = 'Bahasa'): int
    {
        $code = $code ?: $this->uniqueDdcCode();

        return $this->insertRow('ddc_classes', [
            'code' => $code,
            'name' => $name . ' ' . uniqid(),
            'description' => 'Klasifikasi test',
        ]);
    }

    private function uniqueDdcCode(): string
    {
        do {
            $code = (string) random_int(100, 999);
        } while (
            Schema::hasTable('ddc_classes')
            && DB::table('ddc_classes')->where('code', $code)->exists()
        );

        return $code;
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
        string $loanStatus = 'aktif',
        string $loanItemStatus = 'dipinjam'
    ): int {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-INDEX-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'return_date' => $loanStatus === 'selesai' ? now()->format('Y-m-d') : null,
            'status' => $loanStatus,
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan index filter test',
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'return_date' => $loanItemStatus === 'dikembalikan' ? now()->format('Y-m-d') : null,
            'status' => $loanItemStatus,
            'late_days' => 0,
            'fine_amount' => 0,
            'return_condition' => $loanItemStatus === 'dikembalikan' ? 'baik' : null,
            'renewal_count' => 0,
            'notes' => null,
        ]);

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => in_array($loanItemStatus, ['dipinjam', 'terlambat'], true) ? 'dipinjam' : 'tersedia',
                'updated_at' => now(),
            ]));

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