<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Show session history
     */
    public function history()
    {
        $sessions = Auth::user()->cashRegisterSessions()
            ->latest()
            ->paginate(10);

        return view('cashier.shift.history', compact('sessions'));
    }

    /**
     * Show session details
     */
    public function show($id)
    {
        $session = Auth::user()->cashRegisterSessions()->findOrFail($id);
        $report = $this->shiftService->getReport($session); // Re-calculate report for display

        return view('cashier.shift.show', compact('session', 'report'));
    }

    /**
     * Show form to open register
     */
    public function create()
    {
        $user = Auth::user();

        // If already open, redirect to POS
        if ($this->shiftService->getCurrentSession($user)) {
            return redirect()->route('pos.index');
        }

        // Check global active sessions limit
        $maxRegisters = \App\Models\Setting::getMaxActiveRegisters();
        $activeSessionsCount = \App\Models\CashRegisterSession::where('status', 'open')->count();
        $maxReached = $activeSessionsCount >= $maxRegisters;

        // Optionally get the active users to display who is currently using it
        $activeUsers = [];
        if ($maxReached) {
            $activeUsers = \App\Models\CashRegisterSession::where('status', 'open')
                ->with(['user', 'cashRegister'])
                ->get()
                ->map(function ($session) {
                    $regName = $session->cashRegister ? " ({$session->cashRegister->name})" : "";
                    return $session->user->name . $regName;
                })
                ->toArray();
        }

        // Get available registers that aren't currently open
        $availableRegisters = collect();
        if (!$maxReached) {
            // Get IDs of registers currently in use
            $usedRegisterIds = \App\Models\CashRegisterSession::where('status', 'open')
                ->whereNotNull('cash_register_id')
                ->pluck('cash_register_id')
                ->toArray();

            $availableRegisters = \App\Models\CashRegister::where('status', 'active')
                ->whereNotIn('id', $usedRegisterIds)
                ->orderBy('name')
                ->get();
        }

        return view('cashier.shift.create', compact('maxReached', 'maxRegisters', 'activeUsers', 'availableRegisters'));
    }

    /**
     * Handle opening register
     */
    public function store(Request $request)
    {
        $maxRegisters = \App\Models\Setting::getMaxActiveRegisters();
        $rules = [
            'opening_cash' => 'required|numeric|min:0',
        ];

        if ($maxRegisters > 1) {
            $rules['cash_register_id'] = 'required|exists:cash_registers,id';
        }

        $request->validate($rules);

        try {
            $this->shiftService->openSession(
                Auth::user(),
                $request->opening_cash,
                $request->ip(),
                $request->cash_register_id ?? null
            );

            return redirect()->route('pos.index')->with('success', 'Register berhasil dibuka!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get session summary for JSON (POS Overlay)
     */
    public function summary()
    {
        $user = Auth::user();
        $session = $this->shiftService->getCurrentSession($user);

        if (!$session) {
            return response()->json(['error' => 'No active session'], 404);
        }

        $report = $this->shiftService->getReport($session);

        return response()->json([
            'session' => $session,
            'report' => $report
        ]);
    }

    /**
     * Show form to close register (Z-Report)
     */
    public function edit()
    {
        $user = Auth::user();
        $session = $this->shiftService->getCurrentSession($user);

        if (!$session) {
            return redirect()->route('cashier.shift.create')->with('error', 'Tidak ada sesi aktif.');
        }

        // Generate preliminary report
        $report = $this->shiftService->getReport($session);

        return view('cashier.shift.close', compact('session', 'report'));
    }

    /**
     * Handle closing register
     */
    public function update(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $session = $this->shiftService->closeSession(
                Auth::user(),
                $request->closing_cash,
                $request->notes
            );

            return redirect()->route('cashier.shift.show', $session->id)->with('success', 'Register berhasil ditutup. Z-Report tersimpan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
