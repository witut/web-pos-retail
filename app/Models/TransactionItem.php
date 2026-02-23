<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TransactionItem Model
 * 
 * Model untuk detail item transaksi penjualan
 * 
 * PENTING:
 * - Menyimpan snapshot product_name, unit_price, cost_price saat transaksi
 * - Berguna untuk history jika produk diedit/dihapus di kemudian hari
 * - cost_price digunakan untuk hitung profit margin
 * 
 * @property int $id
 * @property int $transaction_id
 * @property int $product_id
 * @property string $product_name (Snapshot)
 * @property string $unit_name (pcs, box, kg, dll)
 * @property float $qty
 * @property float $unit_price (Harga jual saat transaksi)
 * @property float $subtotal (qty × unit_price)
 * @property float $cost_price (HPP saat transaksi, untuk hitung profit)
 */
class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'unit_name',
        'qty',
        'unit_price',
        'discount_amount',
        'subtotal',
        'cost_price',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get header transaksi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get produk yang dijual
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get rincian return untuk item ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function returnItems()
    {
        return $this->hasMany(ProductReturnItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Hitung profit untuk item ini
     * Profit = Subtotal (Revenue Bersih setelah diskon item) - Total HPP (Cost Price * Qty)
     * 
     * @return float
     */
    public function getProfit(): float
    {
        return $this->subtotal - ($this->cost_price * $this->qty);
    }

    /**
     * Hitung profit margin percentage
     * 
     * @return float
     */
    public function getProfitMargin(): float
    {
        $totalCost = $this->cost_price * $this->qty;
        if ($totalCost == 0)
            return 0;

        return (($this->subtotal - $totalCost) / $totalCost) * 100;
    }

    /**
     * Get formatted unit price
     * 
     * @return string
     */
    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Get formatted subtotal
     * 
     * @return string
     */
    public function getFormattedSubtotal(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
