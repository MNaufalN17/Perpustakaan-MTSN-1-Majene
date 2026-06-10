<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RouteHealthAndShowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_critical_route_names_exist(): void
    {
        $criticalRoutes = [
            'dashboard',

            'admin.dashboard',
            'admin.settings.index',
            'admin.settings.update',

            'kepala_sekolah.dashboard',
            'kepala_sekolah.reports.index',
            'kepala_sekolah.reports.download',
            'kepala_sekolah.reports.collections',
            'kepala_sekolah.reports.collections.download',
            'kepala_sekolah.reports.members',
            'kepala_sekolah.reports.members.download',
            'kepala_sekolah.reports.damaged_lost',
            'kepala_sekolah.reports.damaged_lost.download',

            'loans.index',
            'loans.create',
            'loans.store',
            'loans.show',
            'loans.update',
            'loans.destroy',
            'loans.class_bulk.create',
            'loans.class_bulk.store',
            'loans.report.download',

            'visits.index',
            'visits.create',
            'visits.store',
            'visits.show',
            'visits.edit',
            'visits.update',
            'visits.destroy',

            'books.index',
            'books.create',
            'books.store',
            'books.show',
            'books.edit',
            'books.update',
            'books.destroy',

            'book_items.index',
            'book_items.create',
            'book_items.store',
            'book_items.show',
            'book_items.edit',
            'book_items.update',
            'book_items.destroy',
            'book_items.bulk_destroy',
            'book_items.restore_to_stock',

            'members.index',
            'members.create',
            'members.store',
            'members.show',
            'members.edit',
            'members.update',
            'members.destroy',
            'members.quick_store',

            'categories.index',
            'categories.create',
            'categories.store',
            'categories.edit',
            'categories.update',
            'categories.destroy',

            'ddc.index',
            'ddc.create',
            'ddc.store',
            'ddc.edit',
            'ddc.update',
            'ddc.destroy',

            'classes.index',
            'classes.create',
            'classes.store',
            'classes.edit',
            'classes.update',
            'classes.destroy',
        ];

        foreach ($criticalRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                'Route "' . $routeName . '" tidak ditemukan.'
            );
        }
    }

    public function test_pustakawan_can_open_main_show_pages_without_server_error(): void
    {
        $this->actingAsRole(1);

        [$bookId, $bookItemId, $memberId, $loanId] = $this->prepareShowPageData();

        $this->get(route('books.show', $bookId))
            ->assertOk();

        $this->get(route('book_items.show', $bookItemId))
            ->assertOk();

        $this->get(route('members.show', $memberId))
            ->assertOk();

        $this->get(route('loans.show', $loanId))
            ->assertOk();
    }

    public function test_kepala_sekolah_can_open_read_only_show_pages(): void
    {
        $this->actingAsRole(2);

        [$bookId, $bookItemId, $memberId] = $this->prepareShowPageData();

        $this->get(route('books.show', $bookId))
            ->assertOk();

        $this->get(route('book_items.show', $bookItemId))
            ->assertOk();

        $this->get(route('members.show', $memberId))
            ->assertOk();
    }

    public function test_kepala_sekolah_cannot_open_loan_show_page(): void
    {
        $this->actingAsRole(2);

        [, , , $loanId] = $this->prepareShowPageData();

        $this->get(route('loans.show', $loanId))
            ->assertForbidden();
    }

    public function test_admin_it_cannot_open_library_show_pages(): void
    {
        $this->actingAsRole(3);

        [$bookId, $bookItemId, $memberId, $loanId] = $this->prepareShowPageData();

        $this->get(route('books.show', $bookId))
            ->assertForbidden();

        $this->get(route('book_items.show', $bookItemId))
            ->assertForbidden();

        $this->get(route('members.show', $memberId))
            ->assertForbidden();

        $this->get(route('loans.show', $loanId))
            ->assertForbidden();
    }

    public function test_pustakawan_can_open_main_edit_pages_without_server_error(): void
    {
        $this->actingAsRole(1);

        [$bookId, $bookItemId, $memberId] = $this->prepareShowPageData();

        $categoryId = $this->createCategory('Kategori Edit Route');
        $ddcClassId = $this->createDdcClass(null, 'DDC Edit Route');
        $classId = $this->createStudentClass([
            'class_name' => 'VII Edit Route',
            'name' => 'VII Edit Route',
        ]);

        $this->get(route('books.edit', $bookId))
            ->assertOk();

        $this->get(route('book_items.edit', $bookItemId))
            ->assertOk();

        $this->get(route('members.edit', $memberId))
            ->assertOk();

        $this->get(route('categories.edit', $categoryId))
            ->assertOk();

        $this->get(route('ddc.edit', $ddcClassId))
            ->assertOk();

        $this->get(route('classes.edit', $classId))
            ->assertOk();
    }

    public function test_pustakawan_index_pages_do_not_return_server_error(): void
    {
        $this->actingAsRole(1);

        $this->prepareShowPageData();

        $this->get(route('loans.index'))
            ->assertOk();

        $this->get(route('books.index'))
            ->assertOk();

        $this->get(route('book_items.index'))
            ->assertOk();

        $this->get(route('members.index'))
            ->assertOk();

        $this->get(route('categories.index'))
            ->assertOk();

        $this->get(route('ddc.index'))
            ->assertOk();

        $this->get(route('classes.index'))
            ->assertOk();
    }

    private function prepareShowPageData(): array
    {
        $classId = $this->createStudentClass();

        $memberId = $this->createMember($classId, [
            'name' => 'Anggota Show Page',
            'nis_nip' => 'NIS-SHOW-' . random_int(10000, 99999),
        ]);

        $bookId = $this->createBook('Buku Show Page');

        $bookItemId = $this->createBookItem($bookId, 'SHOW-ITEM-' . uniqid(), 1, [
            'status' => 'dipinjam',
            'condition' => 'baik',
        ]);

        $loanId = $this->createActiveLoan($memberId, $bookItemId);

        return [
            $bookId,
            $bookItemId,
            $memberId,
            $loanId,
        ];
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

    private function createActiveLoan(int $memberId, int $bookItemId): int
    {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-SHOW-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'aktif',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan show page test',
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
