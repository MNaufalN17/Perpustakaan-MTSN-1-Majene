<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (isset($user->is_active) && !$user->is_active) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Staff IT Admin.',
                ]);
        }

        $allowedRoleIds = collect($roles)
            ->map(function ($role) {
                return match ($role) {
                    'pustakawan' => 1,
                    'kepala', 'kepala_sekolah', 'kepala_perpustakaan' => 2,
                    'admin', 'it', 'staff_it' => 3,
                    default => (int) $role,
                };
            })
            ->filter()
            ->values()
            ->all();

        if (!in_array((int) $user->role_id, $allowedRoleIds, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}