<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductUnit Model
 * 
 * Model untuk UOM (Unit of Measure) system
 * Contoh untuk produk Coca Cola:
 * - Base unit: pcs (1 botol) - Rp 5.000
 * - Unit alternatif: Box (24 pcs) - Rp 115.000
 * - Unit alternatif: Karton (288 pcs) - Rp 1.350.000
 * 
 * @property int $id
 * @property int $product_id
 * @property string $unit_name (Box, Karton, Lusin, dll)
 * @property float $conversion_rate (berapa base unit dalam 1 unit ini)
 * @property float $selling_price (harga jual untuk unit ini)
 * @property bool $is_base_unit
 * @property bool $is_active
 */
class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion_rate',
        'selling_price',
        'is_base_unit',
        'is_active',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_base_unit' => 'boolean',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get produk yang memiliki unit ini
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
     * Scope untuk filter unit aktif saja
     * Usage: ProductUnit::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter base unit
     * Usage: ProductUnit::baseUnit()->first()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBaseUnit($query)
    {
        return $query->where('is_base_unit', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Convert qty dari unit ini ke base unit
     * Contoh: 2 Box × 24 conversion = 48 pcs (base unit)
     * 
     * @param float $qty
     * @return float
     */
    public function convertToBaseUnit(float $qty): float
    {
        return $qty * $this->conversion_rate;
    }

    /**
     * Convert qty dari base unit ke unit ini
     * Contoh: 48 pcs ÷ 24 conversion = 2 Box
     * 
     * @param float $baseQty
     * @return float
     */
    public function convertFromBaseUnit(float $baseQty): float
    {
        return $baseQty / $this->conversion_rate;
    }

    /**
     * Get harga per base unit (untuk validasi pricing)
     * Contoh: Box Rp 115.000 ÷ 24 pcs = Rp 4.791/pcs
     * 
     * @return float
     */
    public function getPricePerBaseUnit(): float
    {
        if ($this->conversion_rate == 0)
            return 0;
        return $this->selling_price / $this->conversion_rate;
    }
}
