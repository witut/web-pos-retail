<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Transaction Model
 * 
 * Model untuk transaksi penjualan (Header/Master)
 * 
 * BUSINESS LOGIC PENTING:
 * - Pre-Checkout: Kasir bebas edit/delete item (belum ada record di DB)
 * - Post-Checkout: Void transaction requires Admin PIN
 * - Stock deduction terjadi saat checkout (atomic transaction)
 * - Invoice number sequential: INV/YYYY/MM/XXXXX (reset monthly)
 * 
 * @property int $id
 * @property string $invoice_number
 * @property datetime $transaction_date
 * @property int $cashier_id
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $discount_amount
 * @property float $total
 * @property string $payment_method
 * @property float $amount_paid
 * @property float $change_amount
 * @property string $status (completed, void)
 * @property string|null $void_reason
 * @property string|null $void_notes
 * @property int|null $voided_by (Admin user ID)
 * @property datetime|null $voided_at
 * @property string|null $notes
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'transaction_date',
        'cashier_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'payment_method',
        'amount_paid',
        'change_amount',
        'status',
        'void_reason',
        'void_notes',
        'voided_by',
        'voided_at',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get kasir yang melakukan transaksi ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get admin yang melakukan void (jika di-void)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Get semua items dalam transaksi ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter transaksi completed saja
     * Usage: Transaction::completed()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope untuk filter transaksi yang sudah di-void
     * Usage: Transaction::voided()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVoided($query)
    {
        return $query->where('status', 'void');
    }

    /**
     * Scope untuk filter transaksi hari ini
     * Usage: Transaction::today()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', now()->toDateString());
    }

    /**
     * Scope untuk filter berdasarkan kasir
     * Usage: Transaction::byCashier(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $cashierId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    /**
     * Scope untuk filter berdasarkan periode
     * Usage: Transaction::betweenDates('2026-02-01', '2026-02-28')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan payment method
     * Usage: Transaction::byPaymentMethod('cash')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Format total ke Rupiah
     * Usage: $transaction->formatted_total
     * Output: "Rp 150.000"
     */
    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->total, 0, ',', '.')
        );
    }

    /**
     * Accessor: Format subtotal ke Rupiah
     */
    protected function formattedSubtotal(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->subtotal, 0, ',', '.')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah transaksi ini sudah di-void
     * 
     * @return bool
     */
    public function isVoided(): bool
    {
        return $this->status === 'void';
    }

    /**
     * Check apakah transaksi ini completed
     * 
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check apakah transaksi bisa di-void (dalam batas waktu)
     * Contoh: Void hanya bisa dilakukan dalam 24 jam
     * 
     * @param int $hourLimit (default dari settings)
     * @return bool
     */
    public function canBeVoided(int $hourLimit = 24): bool
    {
        if ($this->isVoided()) {
            return false; // Sudah void, tidak bisa void lagi
        }

        $timeDiff = now()->diffInHours($this->transaction_date);
        return $timeDiff <= $hourLimit;
    }

    /**
     * Get total profit dari transaksi ini
     * Profit = Selling Price - Cost Price (HPP)
     * 
     * @return float
     */
    public function getTotalProfit(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->qty;
        });
    }

    /**
     * Get jumlah total items dalam transaksi
     * 
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->items->sum('qty');
    }
}
