<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StockReceiving Model
 * 
 * Model untuk penerimaan stok dari supplier (Header/Master)
 * Setiap penerimaan akan update HPP produk menggunakan weighted average
 * 
 * @property int $id
 * @property string $receiving_number (RCV/YYYY/MM/XXXXX)
 * @property int $supplier_id
 * @property string|null $invoice_number (Nomor faktur/DO supplier)
 * @property date $receiving_date
 * @property float $total_cost
 * @property string|null $notes
 * @property int $created_by
 */
class StockReceiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_number',
        'supplier_id',
        'invoice_number',
        'receiving_date',
        'total_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receiving_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get supplier yang mengirim stok ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get semua item yang diterima (detail penerimaan)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(StockReceivingItem::class, 'receiving_id');
    }

    /**
     * Get user yang membuat penerimaan ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter berdasarkan tanggal
     * Usage: StockReceiving::byDate('2026-02-04')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('receiving_date', $date);
    }

    /**
     * Scope untuk filter berdasarkan supplier
     * Usage: StockReceiving::ofSupplier(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $supplierId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope untuk filter berdasarkan periode
     * Usage: StockReceiving::betweenDates('2026-02-01', '2026-02-28')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('receiving_date', [$startDate, $endDate]);
    }
}
