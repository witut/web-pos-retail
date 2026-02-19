<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ShiftService;
use Illuminate\Support\Facades\Auth;

class EnsureActiveSession
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->isCashier() || $user->isAdmin())) {
            // Check if user has active session
            $session = $this->shiftService->getCurrentSession($user);

            if (!$session) {
                // If checking for JSON (AJAX request), return error
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi kasir belum dibuka. Silakan buka register terlebih dahulu.',
                        'redirect' => route('cashier.shift.create')
                    ], 403);
                }

                // Redirect to Open Register page
                return redirect()->route('cashier.shift.create');
            }
        }

        return $next($request);
    }
}
