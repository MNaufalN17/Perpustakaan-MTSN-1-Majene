<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private array $roleMap = [
        'pustakawan' => 1,
        'kepala_sekolah' => 2,
        'kepala_perpustakaan' => 2,
        'admin' => 3,
    ];

    public function index()
    {
        $this->authorizeAdmin();

        $users = User::latest()
            ->paginate(10)
            ->withQueryString();

        return view($this->existingView([
            'users.index',
            'admin.users.index',
        ]), compact('users'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        $roles = $this->roleOptions();

        return view($this->existingView([
            'users.create',
            'admin.users.create',
        ]), compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'role' => ['nullable', 'string', Rule::in(['admin', 'pustakawan', 'kepala_sekolah', 'kepala_perpustakaan'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'role_id.in' => 'Role pengguna tidak valid.',
            'role.in' => 'Role pengguna tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'is_active.boolean' => 'Status akun tidak valid.',
        ]);

        $roleId = $this->resolveRoleId($request);

        if (!$roleId) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Role pengguna wajib dipilih.',
                ]);
        }

        $isActive = $request->has('is_active')
            ? (bool) $validated['is_active']
            : true;

        $user = new User();

        $this->fillUserData($user, [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'password' => Hash::make($validated['password']),
            'role_id' => $roleId,
            'role' => $this->roleNameForStorage($request, $roleId),
            'is_active' => $isActive,
            'status' => $isActive ? 'aktif' : 'nonaktif',
        ]);

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success_title', 'User berhasil ditambahkan')
            ->with('success_message', 'Akun pengguna baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();

        $roles = $this->roleOptions();

        return view($this->existingView([
            'users.edit',
            'admin.users.edit',
        ]), compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role_id' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'role' => ['nullable', 'string', Rule::in(['admin', 'pustakawan', 'kepala_sekolah', 'kepala_perpustakaan'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'role_id.in' => 'Role pengguna tidak valid.',
            'role.in' => 'Role pengguna tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'is_active.boolean' => 'Status akun tidak valid.',
        ]);

        $roleId = $this->resolveRoleId($request);

        if (!$roleId) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Role pengguna wajib dipilih.',
                ]);
        }

        $isActive = $request->has('is_active')
            ? (bool) $validated['is_active']
            : true;

        if ($user->id === auth()->id() && $roleId !== 3) {
            return back()
                ->withInput()
                ->with('error_title', 'Role tidak bisa diubah')
                ->with('error_message', 'Anda tidak bisa mengubah role akun sendiri dari Admin IT menjadi role lain.');
        }

        if ($user->id === auth()->id() && !$isActive) {
            return back()
                ->withInput()
                ->with('error_title', 'Akun tidak bisa dinonaktifkan')
                ->with('error_message', 'Anda tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        $payload = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'role_id' => $roleId,
            'role' => $this->roleNameForStorage($request, $roleId),
            'is_active' => $isActive,
            'status' => $isActive ? 'aktif' : 'nonaktif',
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $this->fillUserData($user, $payload);

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success_title', 'User berhasil diperbarui')
            ->with('success_message', 'Data akun "' . $user->name . '" berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error_title', 'User tidak bisa dihapus')
                ->with('error_message', 'Anda tidak bisa menghapus akun yang sedang digunakan.');
        }

        $name = $user->name;

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success_title', 'User berhasil dihapus')
            ->with('success_message', 'Akun "' . $name . '" berhasil dihapus dari sistem.');
    }

    private function authorizeAdmin(): void
    {
        if (!auth()->check()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $user = auth()->user();

        $isAdminByRoleId = Schema::hasColumn('users', 'role_id')
            && (int) $user->role_id === 3;

        $isAdminByRoleName = Schema::hasColumn('users', 'role')
            && (string) $user->role === 'admin';

        if (!$isAdminByRoleId && !$isAdminByRoleName) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    private function resolveRoleId(Request $request): ?int
    {
        if ($request->filled('role_id')) {
            $roleId = (int) $request->input('role_id');

            return in_array($roleId, [1, 2, 3], true) ? $roleId : null;
        }

        $role = (string) $request->input('role', '');

        return $this->roleMap[$role] ?? null;
    }

    private function roleNameForStorage(Request $request, int $roleId): string
    {
        if ($request->filled('role')) {
            return (string) $request->input('role');
        }

        return match ($roleId) {
            1 => 'pustakawan',
            2 => 'kepala_perpustakaan',
            3 => 'admin',
            default => 'pustakawan',
        };
    }

    private function roleOptions(): array
    {
        return [
            3 => 'Admin IT',
            1 => 'Pustakawan',
            2 => 'Kepala Sekolah',
        ];
    }

    private function fillUserData(User $user, array $payload): void
    {
        foreach ($payload as $column => $value) {
            if (Schema::hasColumn('users', $column)) {
                $user->{$column} = $value;
            }
        }
    }

    private function existingView(array $views): string
    {
        foreach ($views as $view) {
            if (view()->exists($view)) {
                return $view;
            }
        }

        return $views[0];
    }
}