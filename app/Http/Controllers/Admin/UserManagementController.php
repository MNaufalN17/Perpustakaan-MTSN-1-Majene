<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'pustakawan', 'kepala_perpustakaan'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role pengguna tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'is_active.required' => 'Status akun wajib dipilih.',
        ]);

        User::create([
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success_title', 'User berhasil ditambahkan')
            ->with('success_message', 'Akun pengguna baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['admin', 'pustakawan', 'kepala_perpustakaan'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role pengguna tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'is_active.required' => 'Status akun wajib dipilih.',
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error_title', 'Role tidak bisa diubah')
                ->with('error_message', 'Anda tidak bisa mengubah role akun sendiri dari Admin menjadi role lain.');
        }

        if ($user->id === auth()->id() && !(bool) $validated['is_active']) {
            return back()
                ->withInput()
                ->with('error_title', 'Akun tidak bisa dinonaktifkan')
                ->with('error_message', 'Anda tidak bisa menonaktifkan akun yang sedang digunakan.');
        }

        $data = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'role' => $validated['role'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success_title', 'User berhasil diperbarui')
            ->with('success_message', 'Data akun "' . $user->name . '" berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error_title', 'User tidak bisa dihapus')
                ->with('error_message', 'Anda tidak bisa menghapus akun yang sedang digunakan.');
        }

        $name = $user->name;

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success_title', 'User berhasil dihapus')
            ->with('success_message', 'Akun "' . $name . '" berhasil dihapus dari sistem.');
    }
}