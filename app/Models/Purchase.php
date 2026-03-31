<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Purchase Model
 * 
 * Model untuk transaksi pembelian (Procurement)
 * Mendukung manajemen hutang (Account Payable)
 */
class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'debt_amount',
        'payment_status',
        'status',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Relationship ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relationship ke User pembuat
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship ke Detail Item
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Scope untuk pembelian yang berstatus Hutang (unpaid/partial)
     */
    public function scopeDebt($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }

    /**
     * Scope untuk hutang yang sudah jatuh tempo
     */
    public function scopeOverdue($query)
    {
        return $query->debt()->where('due_date', '<=', now());
    }
}
