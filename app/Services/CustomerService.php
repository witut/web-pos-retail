<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Calculate points earned based on transaction amount
     * 
     * @param float $amount Transaction total
     * @return int Points earned
     */
    public function calculatePointsEarned(float $amount): int
    {
        // Check if loyalty is enabled
        if (!$this->settingService->getBool('customer.loyalty_enabled', true)) {
            return 0;
        }

        // Get points earn rate (e.g., "10000:1" means Rp 10k = 1 point)
        $rate = $this->settingService->get('customer.points_earn_rate', '10000:1');
        [$amountPerPoint, $pointsPerAmount] = explode(':', $rate);

        // Check minimum transaction
        $minTransaction = $this->settingService->getInt('customer.points_min_transaction', 0);
        if ($amount < $minTransaction) {
            return 0;
        }

        // Calculate points (floor to avoid decimal points)
        return (int) floor($amount / $amountPerPoint) * $pointsPerAmount;
    }

    /**
     * Convert points to discount amount
     * 
     * @param int $points Points to redeem
     * @return float Discount amount in Rupiah
     */
    public function convertPointsToDiscount(int $points): float
    {
        if ($points <= 0) {
            return 0;
        }

        // Get points redeem rate (e.g., "100:10000" means 100 points = Rp 10k)
        $rate = $this->settingService->get('customer.points_redeem_rate', '100:10000');
        [$pointsRequired, $discountAmount] = explode(':', $rate);

        // Calculate discount (proportional)
        if ($pointsRequired == 0)
            return 0;
        return floor(($points / $pointsRequired) * $discountAmount);
    }

    /**
     * Award points to customer after transaction
     * 
     * @param Customer $customer
     * @param Transaction $transaction
     * @return void
     */
    public function earnPoints(Customer $customer, Transaction $transaction): void
    {
        $points = $transaction->points_earned;

        if ($points <= 0) {
            return;
        }

        // Calculate expiry date
        $expiryDays = $this->settingService->getInt('customer.points_expiry_days', 365);
        $expiresAt = now()->addDays($expiryDays);

        // Create point transaction record
        CustomerPoint::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'points' => $points,
            'type' => 'earn',
            'description' => "Earned from transaction {$transaction->invoice_number}",
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        // Update customer balance
        $customer->increment('points_balance', $points);
    }

    /**
     * Redeem points for discount
     * 
     * @param Customer $customer
     * @param int $points Points to redeem
     * @param Transaction $transaction
     * @return float Discount amount
     * @throws \Exception If insufficient points
     */
    public function redeemPoints(Customer $customer, int $points, Transaction $transaction): float
    {
        if ($points <= 0) {
            throw new \Exception('Points to redeem must be greater than 0');
        }

        // Check if customer has enough points
        if ($customer->points_balance < $points) {
            throw new \Exception('Insufficient points balance');
        }

        // Calculate discount amount
        $discountAmount = $this->convertPointsToDiscount($points);

        // Create point transaction record (negative points)
        CustomerPoint::create([
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'points' => -$points,
            'type' => 'redeem',
            'description' => "Redeemed for discount on {$transaction->invoice_number}",
            'created_at' => now(),
        ]);

        // Update customer balance
        $customer->decrement('points_balance', $points);

        return $discountAmount;
    }

    /**
     * Get points history for a customer
     * 
     * @param Customer $customer
     * @param string|null $type Filter by type (earn, redeem, etc.)
     * @return Collection
     */
    public function getPointsHistory(Customer $customer, ?string $type = null): Collection
    {
        $query = $customer->points()->with('transaction')->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Expire old points (scheduled task)
     * 
     * @return int Number of points expired
     */
    public function expirePoints(): int
    {
        $expiredPoints = CustomerPoint::where('type', 'earn')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $totalExpired = 0;

        DB::beginTransaction();
        try {
            foreach ($expiredPoints as $point) {
                // Create expiry record
                CustomerPoint::create([
                    'customer_id' => $point->customer_id,
                    'transaction_id' => null,
                    'points' => -$point->points,
                    'type' => 'expire',
                    'description' => 'Points expired',
                    'created_at' => now(),
                ]);

                // Update customer balance
                $point->customer->decrement('points_balance', $point->points);

                $totalExpired += $point->points;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $totalExpired;
    }

    /**
     * Get top customers by total spent
     * 
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        return Customer::orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get new customers count in date range
     * 
     * @param Carbon $from
     * @param Carbon $to
     * @return int
     */
    public function getNewCustomersCount(Carbon $from, Carbon $to): int
    {
        return Customer::whereBetween('created_at', [$from, $to])->count();
    }

    /**
     * Adjust customer points (manual adjustment by admin)
     * 
     * @param Customer $customer
     * @param int $points Positive or negative
     * @param string $reason
     * @return void
     */
    public function adjustPoints(Customer $customer, int $points, string $reason): void
    {
        if ($points == 0) {
            return;
        }

        CustomerPoint::create([
            'customer_id' => $customer->id,
            'transaction_id' => null,
            'points' => $points,
            'type' => 'adjustment',
            'description' => $reason,
            'created_at' => now(),
        ]);

        // Update customer balance
        if ($points > 0) {
            $customer->increment('points_balance', $points);
        } else {
            $customer->decrement('points_balance', abs($points));
        }
    }
}
