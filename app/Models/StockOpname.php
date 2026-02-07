<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StockOpname Model
 * 
 * Model untuk stock taking/stock count (Header)
 * Digunakan untuk:
 * - Menghitung fisik stok gudang
 * - Membandingkan dengan stok sistem
 * - Adjustment jika ada selisih
 * 
 * @property int $id
 * @property string $opname_number (OPN/YYYY/MM/XXXXX)
 * @property date $opname_date
 * @property string|null $notes
 * @property int $created_by
 */
class StockOpname extends Model
{
    use HasFactory;

    protected $table = 'stock_opname';

    protected $fillable = [
        'opname_number',
        'opname_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get semua detail items opname ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(StockOpnameItem::class, 'opname_id');
    }

    /**
     * Get user yang melakukan opname
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
     * Usage: StockOpname::byDate('2026-02-04')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('opname_date', $date);
    }

    /**
     * Scope untuk filter berdasarkan periode
     * Usage: StockOpname::betweenDates('2026-02-01', '2026-02-28')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('opname_date', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get total variance value (nilai selisih)
     * Positif = Lebih (lebih banyak dari sistem)
     * Negatif = Kurang (berkurang dari sistem)
     * 
     * @return float
     */
    public function getTotalVarianceValue(): float
    {
        return $this->items()->sum('variance_value');
    }

    /**
     * Get jumlah produk yang di-opname
     * 
     * @return int
     */
    public function getTotalProducts(): int
    {
        return $this->items()->count();
    }

    /**
     * Get jumlah produk yang ada variance
     * 
     * @return int
     */
    public function getProductsWithVariance(): int
    {
        return $this->items()->where('variance', '!=', 0)->count();
    }
}
