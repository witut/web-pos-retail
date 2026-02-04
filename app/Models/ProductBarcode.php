<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductBarcode Model
 * 
 * Model untuk menyimpan multiple barcodes per produk
 * Contoh: 1 produk bisa punya:
 * - Barcode EAN-13: "8991234567890" (primary)
 * - SKU barcode: "SKU-LAPTOP-001"
 * - Barcode alternatif: "ALT-12345"
 * 
 * @property int $id
 * @property int $product_id
 * @property string $barcode
 * @property bool $is_primary
 */
class ProductBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'barcode',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get produk yang memiliki barcode ini
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
     * Scope untuk filter barcode utama saja
     * Usage: ProductBarcode::primary()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope untuk search barcode
     * Usage: ProductBarcode::searchBarcode('8991234')->first()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $barcode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchBarcode($query, $barcode)
    {
        return $query->where('barcode', 'like', "%{$barcode}%");
    }
}
