<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StockOpnameItem Model
 * 
 * Model untuk detail item stock opname
 * Menyimpan perbandingan antara stok sistem vs stok fisik
 * 
 * @property int $id
 * @property int $opname_id
 * @property int $product_id
 * @property float $system_stock (Stok per sistem)
 * @property float $physical_stock (Stok hasil hitung fisik)
 * @property float $variance (Selisih: physical - system)
 * @property float $variance_value (Nilai rupiah selisih: variance × HPP)
 * @property string|null $notes (Keterangan jika ada selisih significant)
 */
class StockOpnameItem extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_items';

    protected $fillable = [
        'opname_id',
        'product_id',
        'system_stock',
        'physical_stock',
        'variance',
        'variance_value',
        'notes',
    ];

    protected $casts = [
        'system_stock' => 'decimal:2',
        'physical_stock' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_value' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get header opname
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    /**
     * Get produk yang di-opname
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter hanya item yang ada variance
     * Usage: StockOpnameItem::withVariance()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithVariance($query)
    {
        return $query->where('variance', '!=', 0);
    }

    /**
     * Scope untuk filter variance positif (lebih dari sistem)
     * Usage: StockOpnameItem::overstock()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverstock($query)
    {
        return $query->where('variance', '>', 0);
    }

    /**
     * Scope untuk filter variance negatif (kurang dari sistem)
     * Usage: StockOpnameItem::shortage()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShortage($query)
    {
        return $query->where('variance', '<', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah ada selisih
     * 
     * @return bool
     */
    public function hasVariance(): bool
    {
        return $this->variance != 0;
    }

    /**
     * Check apakah variance significant (lebih dari threshold)
     * 
     * @param float $threshold (dalam persen, default 5%)
     * @return bool
     */
    public function isSignificantVariance(float $threshold = 5): bool
    {
        if ($this->system_stock == 0)
            return false;

        $percentVariance = abs(($this->variance / $this->system_stock) * 100);
        return $percentVariance > $threshold;
    }

    /**
     * Get variance percentage
     * 
     * @return float
     */
    public function getVariancePercentage(): float
    {
        if ($this->system_stock == 0)
            return 0;
        return ($this->variance / $this->system_stock) * 100;
    }

    /**
     * Get variance status text
     * 
     * @return string
     */
    public function getVarianceStatus(): string
    {
        if ($this->variance > 0) {
            return 'Overstock (+' . $this->variance . ')';
        } elseif ($this->variance < 0) {
            return 'Shortage (' . $this->variance . ')';
        }
        return 'Match';
    }
}
