<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleAccessAndQuickMemberFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_guest_is_redirected_to_login_when_accessing_protected_pages(): void
    {
        $this->get(route('loans.index'))
            ->assertRedirect(route('login'));

        $this->get(route('books.index'))
            ->assertRedirect(route('login'));

        $this->get(route('members.index'))
            ->assertRedirect(route('login'));
    }

    public function test_pustakawan_can_access_transaction_and_management_pages(): void
    {
        $this->actingAsRole(1);

        $this->get(route('loans.index'))
            ->assertOk();

        $this->get(route('loans.create'))
            ->assertOk();

        $this->get(route('books.index'))
            ->assertOk();

        $this->get(route('book_items.index'))
            ->assertOk();

        $this->get(route('members.index'))
            ->assertOk();
    }

    public function test_kepala_sekolah_can_access_read_only_collection_and_member_pages(): void
    {
        $this->actingAsRole(2);

        $this->get(route('books.index'))
            ->assertOk();

        $this->get(route('book_items.index'))
            ->assertOk();

        $this->get(route('members.index'))
            ->assertOk();
    }

    public function test_kepala_sekolah_cannot_access_pustakawan_management_actions(): void
    {
        $this->actingAsRole(2);

        $this->get(route('loans.index'))
            ->assertForbidden();

        $this->get(route('loans.create'))
            ->assertForbidden();

        $this->get(route('books.create'))
            ->assertForbidden();

        $this->get(route('book_items.create'))
            ->assertForbidden();

        $this->postJson(route('members.quick_store'), [
            'nis_nip' => 'KS-001',
            'name' => 'Tidak Boleh',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $this->createStudentClass(),
            'phone' => '081234567890',
            'status' => 'aktif',
        ])->assertForbidden();
    }

    public function test_admin_it_can_access_admin_settings_but_cannot_access_pustakawan_pages(): void
    {
        $this->actingAsRole(3);

        $this->get(route('admin.settings.index'))
            ->assertOk();

        $this->get(route('loans.index'))
            ->assertForbidden();

        $this->get(route('loans.create'))
            ->assertForbidden();

        $this->get(route('books.index'))
            ->assertForbidden();

        $this->get(route('members.index'))
            ->assertForbidden();
    }

    public function test_quick_member_registration_can_create_student_member(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $response = $this->postJson(route('members.quick_store'), [
            'nis_nip' => 'NIS-QUICK-001',
            'name' => 'Siswa Quick Test',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'phone' => '081234567890',
            'status' => 'aktif',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('members', [
            'nis_nip' => 'NIS-QUICK-001',
            'name' => 'Siswa Quick Test',
            'member_type' => 'siswa',
            'status' => 'aktif',
        ]);
    }

    public function test_quick_member_registration_can_create_teacher_without_class(): void
    {
        $this->actingAsRole(1);

        $response = $this->postJson(route('members.quick_store'), [
            'nis_nip' => 'NIP-QUICK-001',
            'name' => 'Guru Quick Test',
            'gender' => 'perempuan',
            'member_type' => 'guru',
            'student_class_id' => null,
            'phone' => '081234567891',
            'status' => 'aktif',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('members', [
            'nis_nip' => 'NIP-QUICK-001',
            'name' => 'Guru Quick Test',
            'member_type' => 'guru',
            'status' => 'aktif',
        ]);
    }

    public function test_quick_member_registration_rejects_duplicate_nis_nip(): void
    {
        $this->actingAsRole(1);

        $classId = $this->createStudentClass();

        $this->createMember($classId, [
            'nis_nip' => 'DUPLICATE-001',
            'name' => 'Anggota Lama',
        ]);

        $response = $this->postJson(route('members.quick_store'), [
            'nis_nip' => 'DUPLICATE-001',
            'name' => 'Anggota Baru',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => $classId,
            'phone' => '081234567892',
            'status' => 'aktif',
        ]);

        $response->assertStatus(422);

        $duplicateCount = DB::table('members')
            ->where('nis_nip', 'DUPLICATE-001')
            ->count();

        $this->assertSame(1, $duplicateCount);
    }

    public function test_quick_member_registration_rejects_student_without_class(): void
    {
        $this->actingAsRole(1);

        $response = $this->postJson(route('members.quick_store'), [
            'nis_nip' => 'NO-CLASS-001',
            'name' => 'Siswa Tanpa Kelas',
            'gender' => 'laki-laki',
            'member_type' => 'siswa',
            'student_class_id' => null,
            'phone' => '081234567893',
            'status' => 'aktif',
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('members', [
            'nis_nip' => 'NO-CLASS-001',
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