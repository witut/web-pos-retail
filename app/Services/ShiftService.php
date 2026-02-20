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
    public function openSession(User $user, float $openingCash, ?string $ipAddress = null, ?int $cashRegisterId = null)
    {
        // Check if already open for this user
        if ($this->getCurrentSession($user)) {
            throw new Exception("Anda sudah memiliki sesi kasir yang aktif.");
        }

        // Enforce global active session limit
        $maxRegisters = \App\Models\Setting::getMaxActiveRegisters();
        $activeSessionsCount = CashRegisterSession::where('status', 'open')->count();

        if ($activeSessionsCount >= $maxRegisters) {
            throw new Exception("Batas maksimal {$maxRegisters} register aktif telah tercapai. Harap tutup sesi kasir lain terlebih dahulu.");
        }

        // Validate Cash Register if maxRegisters > 1 and it's provided
        if ($maxRegisters > 1 && !$cashRegisterId) {
            throw new Exception("Anda harus memilih mesin kasir.");
        }

        if ($cashRegisterId) {
            $isRegisterInUse = CashRegisterSession::where('status', 'open')
                ->where('cash_register_id', $cashRegisterId)
                ->exists();

            if ($isRegisterInUse) {
                throw new Exception("Mesin kasir ini sedang digunakan oleh kasir lain.");
            }
        }

        return DB::transaction(function () use ($user, $openingCash, $ipAddress, $cashRegisterId) {
            // 1. Create or Get Active Shift
            // Logic: If user has an active shift (attendance), reuse it. Else create new.
            // For simplicity, we'll create a new shift if none active for today
            $shift = Shift::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereDate('start_time', Carbon::today())
                ->first();

            if (!$shift) {
                // Cek Batas Pergantian Shift per Hari
                $maxShifts = (int) \App\Models\Setting::get('shift.shifts_per_day', 3);
                $todayShiftsCount = Shift::where('user_id', $user->id)
                    ->whereDate('start_time', Carbon::today())
                    ->count();

                if ($todayShiftsCount >= $maxShifts) {
                    throw new Exception("Anda telah mencapai batas maksimal pergantian shift hari ini ({$maxShifts} kali).");
                }

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
                'cash_register_id' => $cashRegisterId,
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
