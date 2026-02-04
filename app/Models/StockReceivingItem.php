<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StockReceivingItem Model
 * 
 * Model untuk detail item penerimaan stok
 * Setiap item akan:
 * 1. Menambah stock_on_hand produk
 * 2. Recalculate HPP dengan weighted average
 * 3. Create stock movement record
 * 
 * @property int $id
 * @property int $receiving_id
 * @property int $product_id
 * @property float $qty
 * @property string $unit_name
 * @property float $cost_per_unit (Harga beli per unit)
 * @property float $subtotal (qty × cost_per_unit)
 */
class StockReceivingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'product_id',
        'qty',
        'unit_name',
        'cost_per_unit',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get header penerimaan stok
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiving()
    {
        return $this->belongsTo(StockReceiving::class, 'receiving_id');
    }

    /**
     * Get produk yang diterima
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
