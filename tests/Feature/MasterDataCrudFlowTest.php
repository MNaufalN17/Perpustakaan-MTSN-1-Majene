<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDataCrudFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_category_can_be_created_updated_and_deleted(): void
    {
        $this->actingAsRole(1);

        $response = $this->post(route('categories.store'), [
            'name' => 'Kategori Test',
            'description' => 'Deskripsi kategori test',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Test',
        ]);

        $categoryId = (int) DB::table('categories')
            ->where('name', 'Kategori Test')
            ->value('id');

        $response = $this->put(route('categories.update', $categoryId), [
            'name' => 'Kategori Test Updated',
            'description' => 'Deskripsi kategori test updated',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'name' => 'Kategori Test Updated',
        ]);

        $response = $this->delete(route('categories.destroy', $categoryId));

        $response->assertRedirect();

        $this->assertDatabaseMissing('categories', [
            'id' => $categoryId,
        ]);
    }

    public function test_category_duplicate_name_is_rejected(): void
    {
        $this->actingAsRole(1);

        $this->createCategory('Kategori Duplikat');

        $response = $this->post(route('categories.store'), [
            'name' => 'Kategori Duplikat',
            'description' => 'Kategori yang harus ditolak',
        ]);

        $response->assertRedirect();

        $count = DB::table('categories')
            ->where('name', 'Kategori Duplikat')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_ddc_class_can_be_created_updated_and_deleted(): void
    {
        $this->actingAsRole(1);

        $response = $this->post(route('ddc.store'), [
            'code' => '499',
            'name' => 'Bahasa Test',
            'description' => 'DDC test',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ddc_classes', [
            'code' => '499',
            'name' => 'Bahasa Test',
        ]);

        $ddcId = (int) DB::table('ddc_classes')
            ->where('code', '499')
            ->value('id');

        $response = $this->put(route('ddc.update', $ddcId), [
            'code' => '498',
            'name' => 'Bahasa Test Updated',
            'description' => 'DDC test updated',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ddc_classes', [
            'id' => $ddcId,
            'code' => '498',
            'name' => 'Bahasa Test Updated',
        ]);

        $response = $this->delete(route('ddc.destroy', $ddcId));

        $response->assertRedirect();

        $this->assertDatabaseMissing('ddc_classes', [
            'id' => $ddcId,
        ]);
    }

    public function test_ddc_duplicate_code_is_rejected(): void
    {
        $this->actingAsRole(1);

        $this->createDdcClass('700', 'Seni');

        $response = $this->post(route('ddc.store'), [
            'code' => '700',
            'name' => 'Seni Duplikat',
            'description' => 'DDC yang harus ditolak',
        ]);

        $response->assertRedirect();

        $count = DB::table('ddc_classes')
            ->where('code', '700')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_class_can_be_created_updated_and_deleted(): void
    {
        $this->actingAsRole(1);

        $response = $this->post(route('classes.store'), $this->classRequestPayload([
            'level' => 7,
            'grade' => 7,
            'grade_level' => 7,
            'class_name' => 'VII B',
            'name' => 'VII B',
            'academic_year' => '2026/2027',
            'school_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Test',
            'teacher_name' => 'Wali Kelas Test',
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'class_name' => 'VII B',
            'academic_year' => '2026/2027',
        ]);

        $classId = (int) DB::table('classes')
            ->where('class_name', 'VII B')
            ->where('academic_year', '2026/2027')
            ->value('id');

        $response = $this->put(route('classes.update', $classId), $this->classRequestPayload([
            'level' => 7,
            'grade' => 7,
            'grade_level' => 7,
            'class_name' => 'VII C',
            'name' => 'VII C',
            'academic_year' => '2026/2027',
            'school_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Updated',
            'teacher_name' => 'Wali Kelas Updated',
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'id' => $classId,
            'class_name' => 'VII C',
            'academic_year' => '2026/2027',
        ]);

        $response = $this->delete(route('classes.destroy', $classId));

        $response->assertRedirect();

        $this->assertDatabaseMissing('classes', [
            'id' => $classId,
        ]);
    }

    public function test_class_duplicate_in_same_academic_year_is_rejected(): void
    {
        $this->actingAsRole(1);

        $this->createStudentClass([
            'level' => 8,
            'class_name' => 'VIII A',
            'name' => 'VIII A',
            'academic_year' => '2026/2027',
        ]);

        $response = $this->post(route('classes.store'), $this->classRequestPayload([
            'level' => 8,
            'grade' => 8,
            'grade_level' => 8,
            'class_name' => 'VIII A',
            'name' => 'VIII A',
            'academic_year' => '2026/2027',
            'school_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Duplikat',
            'teacher_name' => 'Wali Kelas Duplikat',
        ]));

        $response->assertRedirect();

        $count = DB::table('classes')
            ->where('class_name', 'VIII A')
            ->where('academic_year', '2026/2027')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_member_can_be_created_and_updated(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $response = $this->post(route('members.store'), [
            'member_code' => 'AGT-CRUD-001',
            'nis_nip' => 'NIS-CRUD-001',
            'name' => 'Anggota CRUD',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'class_id' => $classId,
            'phone' => '081234567800',
            'address' => 'Alamat anggota CRUD',
            'status' => 'aktif',
            'joined_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('members', [
            'nis_nip' => 'NIS-CRUD-001',
            'name' => 'Anggota CRUD',
            'status' => 'aktif',
        ]);

        $memberId = (int) DB::table('members')
            ->where('nis_nip', 'NIS-CRUD-001')
            ->value('id');

        $response = $this->put(route('members.update', $memberId), [
            'member_code' => 'AGT-CRUD-001',
            'nis_nip' => 'NIS-CRUD-001',
            'name' => 'Anggota CRUD Updated',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'class_id' => $classId,
            'phone' => '081234567899',
            'address' => 'Alamat anggota CRUD updated',
            'status' => 'aktif',
            'joined_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('members', [
            'id' => $memberId,
            'nis_nip' => 'NIS-CRUD-001',
            'name' => 'Anggota CRUD Updated',
            'phone' => '081234567899',
        ]);
    }

    public function test_member_duplicate_nis_nip_is_rejected(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $this->createMember($classId, [
            'nis_nip' => 'NIS-DUP-CRUD-001',
            'name' => 'Anggota Lama',
        ]);

        $response = $this->post(route('members.store'), [
            'member_code' => 'AGT-DUP-CRUD-002',
            'nis_nip' => 'NIS-DUP-CRUD-001',
            'name' => 'Anggota Baru',
            'gender' => 'perempuan',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'class_id' => $classId,
            'phone' => '081234567801',
            'address' => 'Alamat anggota duplikat',
            'status' => 'aktif',
            'joined_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $count = DB::table('members')
            ->where('nis_nip', 'NIS-DUP-CRUD-001')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_book_can_be_created_and_deleted_when_it_has_no_items(): void
    {
        $this->actingAsRole(1);

        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();

        $response = $this->post(route('books.store'), [
            'category_id' => $categoryId,
            'ddc_class_id' => $ddcClassId,
            'title' => 'Buku CRUD Test',
            'author' => 'Penulis CRUD',
            'author_code' => 'CRU',
            'title_code' => 'b',
            'title_initial' => 'b',
            'publisher' => 'Penerbit CRUD',
            'publication_year' => 2026,
            'isbn' => 'ISBN-CRUD-001',
            'price' => 0,
            'description' => 'Deskripsi buku CRUD',
            'classification_code' => '400',
            'borrowing_status' => 'bisa dipinjam',
            'is_borrowable' => 1,
            'copy_count' => 1,
            'total_copies' => 1,
            'location' => 'Rak Test',
            'acquisition_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'title' => 'Buku CRUD Test',
            'author' => 'Penulis CRUD',
        ]);

        $bookId = (int) DB::table('books')
            ->where('title', 'Buku CRUD Test')
            ->value('id');

        DB::table('book_items')
            ->where('book_id', $bookId)
            ->delete();

        $this->assertDatabaseMissing('book_items', [
            'book_id' => $bookId,
        ]);

        $response = $this->delete(route('books.destroy', $bookId));

        $response->assertRedirect();

        $this->assertDatabaseMissing('books', [
            'id' => $bookId,
        ]);
    }

    public function test_book_with_existing_items_cannot_be_deleted(): void
    {
        $this->actingAsRole(1);

        $bookId = $this->createBook('Buku Dengan Eksemplar');

        $this->createBookItem($bookId, 'BOOK-HAS-ITEM-001', 1);

        $response = $this->delete(route('books.destroy', $bookId));

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $bookId,
            'title' => 'Buku Dengan Eksemplar',
        ]);

        $this->assertDatabaseHas('book_items', [
            'book_id' => $bookId,
            'item_code' => 'BOOK-HAS-ITEM-001',
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

    private function createCategory(string $name = 'Buku Pelajaran'): int
    {
        return $this->insertRow('categories', [
            'name' => $name,
            'description' => 'Kategori test',
        ]);
    }

    private function createDdcClass(string $code = '400', string $name = 'Bahasa'): int
    {
        return $this->insertRow('ddc_classes', [
            'code' => $code,
            'name' => $name,
            'description' => 'Klasifikasi test',
        ]);
    }

    private function createStudentClass(array $overrides = []): int
    {
        return $this->insertRow('classes', array_merge([
            'level' => 7,
            'class_name' => 'VII A',
            'name' => 'VII A',
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

    private function createBook(string $title = 'Buku Test'): int
    {
        $categoryId = $this->createCategory();
        $ddcClassId = $this->createDdcClass();

        return $this->insertRow('books', [
            'category_id' => $categoryId,
            'ddc_class_id' => $ddcClassId,
            'title' => $title,
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

    private function classRequestPayload(array $overrides = []): array
    {
        return array_merge([
            'level' => 7,
            'grade' => 7,
            'grade_level' => 7,
            'class_name' => 'VII B',
            'name' => 'VII B',
            'academic_year' => '2026/2027',
            'school_year' => '2026/2027',
            'homeroom_teacher' => 'Wali Kelas Test',
            'teacher_name' => 'Wali Kelas Test',
            'capacity' => 32,
            'status' => 'aktif',
            'is_active' => 1,
        ], $overrides);
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