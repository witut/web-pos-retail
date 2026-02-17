<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'points_balance',
        'total_spent',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    /**
     * Get all transactions for this customer
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all point transactions for this customer
     */
    public function points(): HasMany
    {
        return $this->hasMany(CustomerPoint::class);
    }

    /**
     * Scope to get active customers only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get top spenders
     */
    public function scopeTopSpenders(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('total_spent', 'desc')->limit($limit);
    }

    /**
     * Scope to search by name, phone, or email
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get available (non-expired) points
     */
    public function getAvailablePointsAttribute(): int
    {
        return $this->points()
            ->where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->sum('points');
    }

    /**
     * Get total transactions count
     */
    public function getTransactionsCountAttribute(): int
    {
        return $this->transactions()->where('status', 'completed')->count();
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute(): string
    {
        // Format: 0812-3456-7890
        $phone = $this->phone;
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }
        return $phone;
    }
}
