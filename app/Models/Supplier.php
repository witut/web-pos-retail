<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Supplier Model
 * 
 * Model untuk master data supplier/vendor
 * 
 * @property int $id
 * @property string $code (Auto: SUPP-00123)
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $contact_person
 * @property string|null $payment_terms
 * @property string|null $notes
 * @property string $status (active, inactive)
 */
class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'payment_terms',
        'notes',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get semua stock receiving dari supplier ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockReceivings()
    {
        return $this->hasMany(StockReceiving::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter supplier aktif saja
     * Usage: Supplier::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk search supplier
     * Usage: Supplier::search('toko')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
                ->orWhere('contact_person', 'like', "%{$keyword}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get total pembelian dari supplier ini
     * 
     * @return float
     */
    public function getTotalPurchases(): float
    {
        return $this->stockReceivings()->sum('total_cost');
    }
}
