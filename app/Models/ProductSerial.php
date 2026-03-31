<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProductSerial Model
 * 
 * Model untuk melacak nomor seri unik (1 SN = 1 unit)
 */
class ProductSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'serial_number',
        'status',
        'transaction_item_id',
    ];

    /**
     * Get produk induk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get item transaksi jika sudah terjual
     */
    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    /**
     * Scope untuk SN yang tersedia
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope untuk SN yang sudah terjual
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }
}
