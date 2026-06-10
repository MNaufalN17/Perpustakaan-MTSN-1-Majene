<?php

namespace Tests\Feature;

use App\Models\LibraryVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LibraryEnhancementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_pustakawan_can_create_book_with_isbn_and_search_it(): void
    {
        $this->actingAsRole(1);

        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();
        $isbn = '978-602-1234-56-7';

        $response = $this->post(route('books.store'), [
            'title' => 'Buku ISBN Test',
            'isbn' => $isbn,
            'author' => 'Penulis ISBN',
            'author_code' => 'Pen',
            'title_code' => 'b',
            'publisher' => 'Penerbit ISBN',
            'publication_year' => 2026,
            'price' => 50000,
            'category_id' => $categoryId,
            'ddc_class_id' => $ddcClassId,
            'borrowing_status' => 'bisa dipinjam',
            'description' => 'Buku dengan ISBN',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'title' => 'Buku ISBN Test',
            'isbn' => $isbn,
        ]);

        $bookId = (int) DB::table('books')->where('isbn', $isbn)->value('id');

        $this->get(route('books.index', ['keyword' => $isbn]))
            ->assertOk()
            ->assertViewHas('books', function ($books) use ($bookId) {
                return $books->getCollection()
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->contains($bookId);
            });
    }

    public function test_pustakawan_can_download_loan_report_csv_with_isbn(): void
    {
        $user = $this->actingAsRole(1);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId);
        $bookId = $this->createBook('Buku Laporan Staff', [
            'isbn' => '978-602-9876-54-3',
        ]);
        $bookItemId = $this->createBookItem($bookId, 'CSV-REPORT-001', 1);

        $this->createLoanWithItem($memberId, $bookItemId, $user->id);

        $response = $this->get(route('loans.report.download', [
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'aktif',
        ]));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Kode Transaksi', $content);
        $this->assertStringContainsString('978-602-9876-54-3', $content);
        $this->assertStringContainsString('CSV-REPORT-001', $content);
    }

    public function test_pustakawan_can_manage_library_visits(): void
    {
        $user = $this->actingAsRole(1);

        $classId = $this->createStudentClass();
        $memberId = $this->createMember($classId, [
            'name' => 'Siswa Buku Tamu',
            'nis_nip' => 'NIS-VISIT-001',
        ]);

        $response = $this->post(route('visits.store'), [
            'member_id' => $memberId,
            'visitor_type' => 'siswa',
            'visit_purpose' => 'Membaca buku',
            'visit_date' => now()->format('Y-m-d'),
            'check_in_time' => '08:15',
            'notes' => 'Kunjungan test',
        ]);

        $response->assertRedirect();

        $visit = LibraryVisit::firstOrFail();

        $this->assertSame('Siswa Buku Tamu', $visit->visitor_name);
        $this->assertSame('NIS-VISIT-001', $visit->identity_number);
        $this->assertSame($user->id, $visit->recorded_by);

        $this->get(route('visits.index'))->assertOk();
        $this->get(route('visits.show', $visit))->assertOk();
        $this->get(route('visits.edit', $visit))->assertOk();

        $this->put(route('visits.update', $visit), [
            'visitor_name' => 'Pengunjung Update',
            'identity_number' => 'UMUM-001',
            'visitor_type' => 'umum',
            'visit_purpose' => 'Mencari referensi',
            'visit_date' => now()->format('Y-m-d'),
            'check_in_time' => '09:20',
            'notes' => 'Catatan update',
        ])->assertRedirect(route('visits.show', $visit));

        $this->assertDatabaseHas('library_visits', [
            'id' => $visit->id,
            'visitor_name' => 'Pengunjung Update',
            'visitor_type' => 'umum',
        ]);

        $this->delete(route('visits.destroy', $visit))
            ->assertRedirect(route('visits.index'));

        $this->assertDatabaseMissing('library_visits', [
            'id' => $visit->id,
        ]);
    }

    public function test_kepala_can_view_visits_but_cannot_manage_them(): void
    {
        $classId = $this->createStudentClass();
        $visitId = $this->createVisit($classId);

        $this->actingAsRole(2);

        $this->get(route('visits.index'))->assertOk();
        $this->get(route('visits.show', $visitId))->assertOk();
        $this->get(route('visits.create'))->assertForbidden();
        $this->post(route('visits.store'), [
            'visitor_name' => 'Tidak Boleh',
            'visitor_type' => 'umum',
            'visit_purpose' => 'Test',
            'visit_date' => now()->format('Y-m-d'),
        ])->assertForbidden();
    }

    public function test_admin_cannot_access_library_visits(): void
    {
        $this->actingAsRole(3);

        $this->get(route('visits.index'))->assertForbidden();
    }

    private function seedRoles(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        foreach ([
            1 => ['name' => 'Pustakawan', 'role_name' => 'Pustakawan', 'display_name' => 'Pustakawan', 'code' => 'pustakawan'],
            2 => ['name' => 'Kepala Sekolah', 'role_name' => 'Kepala Sekolah', 'display_name' => 'Kepala Sekolah', 'code' => 'kepala_sekolah'],
            3 => ['name' => 'Admin IT', 'role_name' => 'Admin IT', 'display_name' => 'Admin IT', 'code' => 'admin'],
        ] as $id => $payload) {
            DB::table('roles')->updateOrInsert(
                ['id' => $id],
                $this->filterColumns('roles', array_merge([
                    'id' => $id,
                    'guard_name' => 'web',
                    'description' => $payload['name'],
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
            'class_name' => 'VII Test',
            'name' => 'VII Test',
            'academic_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Test',
            'status' => 'aktif',
        ], $overrides));
    }

    private function createMember(?int $classId = null, array $overrides = []): int
    {
        return $this->insertRow('members', array_merge([
            'member_code' => 'AGT-' . uniqid(),
            'nis_nip' => 'NIS-' . random_int(10000, 99999),
            'name' => 'Anggota Test',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId ?? $this->createStudentClass(),
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
            'name' => 'Kategori Test ' . uniqid(),
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
        return $this->insertRow('books', array_merge([
            'category_id' => $this->createCategory(),
            'ddc_class_id' => $this->createDdcClass(),
            'title' => $title,
            'author' => 'Penulis Test',
            'author_code' => 'TES',
            'title_code' => 't',
            'publisher' => 'Penerbit Test',
            'publication_year' => 2026,
            'isbn' => '978-602-' . random_int(1000, 9999) . '-' . random_int(10, 99) . '-' . random_int(0, 9),
            'price' => 0,
            'description' => 'Deskripsi test',
            'is_borrowable' => 1,
        ], $overrides));
    }

    private function createBookItem(int $bookId, string $itemCode, int $copyNumber): int
    {
        return $this->insertRow('book_items', [
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
        ]);
    }

    private function createLoanWithItem(int $memberId, int $bookItemId, int $handledBy): int
    {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-CSV-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'aktif',
            'loan_type' => 'regular',
            'handled_by' => $handledBy,
            'user_id' => $handledBy,
            'created_by' => $handledBy,
            'librarian_id' => $handledBy,
            'notes' => 'Loan CSV test',
        ]);

        $this->insertRow('loan_items', [
            'loan_id' => $loanId,
            'book_item_id' => $bookItemId,
            'status' => 'dipinjam',
            'late_days' => 0,
            'fine_amount' => 0,
            'renewal_count' => 0,
        ]);

        DB::table('book_items')
            ->where('id', $bookItemId)
            ->update($this->filterColumns('book_items', [
                'status' => 'dipinjam',
                'updated_at' => now(),
            ]));

        return $loanId;
    }

    private function createVisit(int $classId): int
    {
        return $this->insertRow('library_visits', [
            'visitor_name' => 'Pengunjung Kepala',
            'identity_number' => 'VISIT-001',
            'visitor_type' => 'siswa',
            'student_class_id' => $classId,
            'visit_purpose' => 'Membaca buku',
            'visit_date' => now()->format('Y-m-d'),
            'check_in_time' => '08:00',
            'notes' => 'Data read only',
        ]);
    }

    private function insertRow(string $table, array $payload): int
    {
        if (Schema::hasColumn($table, 'created_at') && ! array_key_exists('created_at', $payload)) {
            $payload['created_at'] = Carbon::now();
        }

        if (Schema::hasColumn($table, 'updated_at') && ! array_key_exists('updated_at', $payload)) {
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
