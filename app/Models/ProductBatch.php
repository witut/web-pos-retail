<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductBatch Model
 * 
 * Model untuk melacak stok per batch dan tanggal kadaluwarsa (ED)
 */
class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'expiry_date',
        'initial_quantity',
        'current_quantity',
        'cost_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'initial_quantity' => 'decimal:2',
        'current_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Get produk induk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope untuk filter batch yang belum expired
     */
    public function scopeAvailable($query)
    {
        return $query->where('current_quantity', '>', 0)
                     ->where(function($q) {
                         $q->whereNull('expiry_date')
                           ->orWhere('expiry_date', '>', now());
                     });
    }

    /**
     * Scope untuk batch yang sudah expired
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    /**
     * Scope untuk batch yang mendekati ED (Near ED)
     * @param int $days
     */
    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->where('expiry_date', '>', now())
                     ->where('expiry_date', '<=', now()->addDays($days));
    }
}
