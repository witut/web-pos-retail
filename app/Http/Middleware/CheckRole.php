<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 * 
 * Middleware untuk memvalidasi role user
 * Digunakan untuk proteksi route admin/cashier
 * 
 * Usage di routes:
 * Route::middleware(['auth', 'role:admin'])->group(...)
 * Route::middleware(['auth', 'role:cashier'])->group(...)
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  List of allowed roles (admin, cashier)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();

        // Check if user is active
        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
