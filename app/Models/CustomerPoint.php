<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CustomerPoint extends Model
{
    public $timestamps = false; // Only created_at

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'points',
        'type',
        'description',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the customer for this point transaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the transaction that generated this point
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Scope to get earned points only
     */
    public function scopeEarned(Builder $query): Builder
    {
        return $query->where('type', 'earn');
    }

    /**
     * Scope to get redeemed points only
     */
    public function scopeRedeemed(Builder $query): Builder
    {
        return $query->where('type', 'redeem');
    }

    /**
     * Scope to get expired points
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('type', 'expire')
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
            });
    }

    /**
     * Scope to get active (non-expired) points
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })->where('type', '!=', 'expire');
    }

    /**
     * Check if this point has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
