<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StockMovement Model
 * 
 * Model untuk tracking semua pergerakan stok (Kartu Stok)
 * Setiap ada perubahan stok (IN/OUT) akan tercatat di sini
 * 
 * MOVEMENT TYPES:
 * - IN: Stock masuk (dari receiving, return, adjustment)
 * - OUT: Stock keluar (dari penjualan, opname adjustment)
 * - ADJUSTMENT: Koreksi stok (opname)
 * - RETURN: Return barang (void transaction)
 * 
 * REFERENCE TYPES:
 * - SALE: Dari transaksi penjualan
 * - RECEIVING: Dari penerimaan barang
 * - OPNAME: Dari stock taking
 * - VOID: Dari void transaksi (stock restoration)
 * 
 * @property int $id
 * @property int $product_id
 * @property string $movement_type (IN, OUT, ADJUSTMENT, RETURN)
 * @property string $reference_type (SALE, RECEIVING, OPNAME, VOID)
 * @property string $reference_id (ID transaksi/receiving/opname)
 * @property float $qty (+ untuk IN, - untuk OUT)
 * @property string $unit_name
 * @property float $cost_price (HPP saat movement)
 * @property float $stock_before (Stok sebelum movement)
 * @property float $stock_after (Stok sesudah movement)
 * @property string|null $notes
 * @property int $user_id
 */
class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'qty',
        'unit_name',
        'cost_price',
        'stock_before',
        'stock_after',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get produk yang bergerak
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get user yang melakukan movement
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter movement IN saja
     * Usage: StockMovement::incoming()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncoming($query)
    {
        return $query->where('movement_type', 'IN');
    }

    /**
     * Scope untuk filter movement OUT saja
     * Usage: StockMovement::outgoing()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOutgoing($query)
    {
        return $query->where('movement_type', 'OUT');
    }

    /**
     * Scope untuk filter berdasarkan produk
     * Usage: StockMovement::forProduct(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope untuk filter berdasarkan periode (untuk Kartu Stok)
     * Usage: StockMovement::betweenDates('2026-02-01', '2026-02-28')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan reference type
     * Usage: StockMovement::fromSales()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $referenceType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReferenceType($query, $referenceType)
    {
        return $query->where('reference_type', $referenceType);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah movement ini adalah stock IN
     * 
     * @return bool
     */
    public function isIncoming(): bool
    {
        return $this->movement_type === 'IN';
    }

    /**
     * Check apakah movement ini adalah stock OUT
     * 
     * @return bool
     */
    public function isOutgoing(): bool
    {
        return $this->movement_type === 'OUT';
    }

    /**
     * Get variance (selisih stok)
     * 
     * @return float
     */
    public function getVariance(): float
    {
        return $this->stock_after - $this->stock_before;
    }
}
