<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ShiftService
{
    /**
     * Get current active session for user
     */
    public function getCurrentSession(User $user)
    {
        return CashRegisterSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    /**
     * Open a new shift/session
     */
    public function openSession(User $user, float $openingCash, ?string $ipAddress = null)
    {
        // Check if already open
        if ($this->getCurrentSession($user)) {
            throw new Exception("Anda sudah memiliki sesi kasir yang aktif.");
        }

        return DB::transaction(function () use ($user, $openingCash, $ipAddress) {
            // 1. Create or Get Active Shift
            // Logic: If user has an active shift (attendance), reuse it. Else create new.
            // For simplicity, we'll create a new shift if none active for today
            $shift = Shift::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereDate('start_time', Carbon::today())
                ->first();

            if (!$shift) {
                $shift = Shift::create([
                    'user_id' => $user->id,
                    'start_time' => now(),
                    'status' => 'active',
                    'ip_address' => $ipAddress
                ]);
            }

            // 2. Create Session
            $session = CashRegisterSession::create([
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'opening_cash' => $openingCash,
                'opened_at' => now(),
                'status' => 'open'
            ]);

            return $session;
        });
    }

    /**
     * Close current session
     */
    public function closeSession(User $user, float $closingCash, ?string $notes = null)
    {
        $session = $this->getCurrentSession($user);
        if (!$session) {
            throw new Exception("Tidak ada sesi aktif untuk ditutup.");
        }

        return DB::transaction(function () use ($session, $closingCash, $notes) {
            // Calculate Expected Cash
            $report = $this->getReport($session);
            $expectedCash = $report['expected_cash'];

            $session->update([
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'variance' => $closingCash - $expectedCash,
                'closed_at' => now(),
                'status' => 'closed',
                'notes' => $notes
            ]);

            // Optional: Close shift if it's end of day? 
            // For now, let's keep shift active until explicit logout or cron job implies close
            // Or maybe just close it here for simplicity
            $session->shift->update([
                'end_time' => now(),
                'status' => 'closed'
            ]);

            return $session;
        });
    }

    /**
     * Generate report for a session
     */
    public function getReport(CashRegisterSession $session)
    {
        // Calculate Cash Sales
        $cashSales = $session->user->transactions()
            ->where('payment_method', 'cash')
            ->whereBetween('transaction_date', [$session->opened_at, now()]) // transaction_date is datetime
            ->completed() // Scope from Transaction model
            ->sum('amount_paid');

        // Calculate Returns (if implemented later) or Cash Out
        $cashOut = 0; // Placeholder

        $expectedCash = $session->opening_cash + $cashSales - $cashOut;

        return [
            'opening_cash' => $session->opening_cash,
            'cash_sales' => $cashSales,
            'cash_out' => $cashOut,
            'expected_cash' => $expectedCash
        ];
    }
}
