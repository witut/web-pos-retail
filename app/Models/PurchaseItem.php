<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseItem Model
 * 
 * Model untuk detail baris item dalam satu transaksi pembelian
 */
class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
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

    /**
     * Relationship ke Header Pembelian
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Relationship ke Produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
