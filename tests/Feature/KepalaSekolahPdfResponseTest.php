<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class KepalaSekolahPdfResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        $this->seedSettings();
    }

    public function test_main_report_pdf_response_is_valid_pdf(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $response = $this->get(route('kepala_sekolah.reports.download'));

        $this->assertValidPdfResponse($response);
    }

    public function test_collections_report_pdf_response_is_valid_pdf(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $response = $this->get(route('kepala_sekolah.reports.collections.download'));

        $this->assertValidPdfResponse($response);
    }

    public function test_members_report_pdf_response_is_valid_pdf(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $response = $this->get(route('kepala_sekolah.reports.members.download'));

        $this->assertValidPdfResponse($response);
    }

    public function test_damaged_lost_report_pdf_response_is_valid_pdf(): void
    {
        $this->actingAsRole(2);

        $this->prepareReportData();

        $response = $this->get(route('kepala_sekolah.reports.damaged_lost.download'));

        $this->assertValidPdfResponse($response);
    }

    private function assertValidPdfResponse($response): void
    {
        $response->assertOk();

        $contentType = (string) $response->headers->get('content-type', '');

        $this->assertStringContainsString(
            'application/pdf',
            strtolower($contentType),
            'Response bukan application/pdf. Content-Type saat ini: ' . $contentType
        );

        $content = $this->responseContent($response);

        $this->assertNotEmpty($content, 'Isi PDF kosong.');

        $this->assertGreaterThan(
            500,
            strlen($content),
            'Ukuran PDF terlalu kecil. Kemungkinan bukan file PDF yang valid.'
        );

        $this->assertStringStartsWith(
            '%PDF',
            $content,
            'Isi response tidak diawali signature PDF.'
        );
    }

    private function responseContent($response): string
    {
        try {
            if ($response->baseResponse instanceof StreamedResponse) {
                return (string) $response->streamedContent();
            }
        } catch (\Throwable $e) {
            return '';
        }

        try {
            return (string) $response->getContent();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function prepareReportData(): void
    {
        $classId = $this->createStudentClass();

        $memberId = $this->createMember($classId, [
            'name' => 'Siswa PDF Test',
            'nis_nip' => 'NIS-PDF-001',
        ]);

        $bookId = $this->createBook('Buku PDF Test');

        $availableItemId = $this->createBookItem($bookId, 'PDF-001', 1, [
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $borrowedItemId = $this->createBookItem($bookId, 'PDF-002', 2, [
            'status' => 'dipinjam',
            'condition' => 'baik',
        ]);

        $this->createBookItem($bookId, 'PDF-003', 3, [
            'status' => 'rusak',
            'condition' => 'rusak berat',
        ]);

        $this->createBookItem($bookId, 'PDF-004', 4, [
            'status' => 'hilang',
            'condition' => 'hilang',
        ]);

        $this->createActiveLoan($memberId, $borrowedItemId);

        $this->createFinishedLoan($memberId, $availableItemId);
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
            'code' => $this->uniqueDdcCode(),
            'name' => 'DDC Test ' . uniqid(),
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
            'loan_code' => 'TRX-PDF-ACTIVE-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->subDays(2)->format('Y-m-d'),
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan aktif PDF test',
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

    private function createFinishedLoan(int $memberId, int $bookItemId): int
    {
        $loanId = $this->insertRow('loans', [
            'loan_code' => 'TRX-PDF-FINISHED-' . uniqid(),
            'member_id' => $memberId,
            'loan_date' => now()->subDays(14)->format('Y-m-d'),
            'due_date' => now()->subDays(7)->format('Y-m-d'),
            'return_date' => now()->subDays(6)->format('Y-m-d'),
            'status' => 'selesai',
            'handled_by' => auth()->id(),
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'librarian_id' => auth()->id(),
            'notes' => 'Loan selesai PDF test',
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