<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Model untuk user (Admin dan Cashier)
 * 
 * ROLES:
 * - admin: Full access, punya PIN untuk supervisor override
 * - cashier: Terbatas untuk POS terminal saja
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role (admin, cashier)
 * @property string|null $pin (6 digit PIN untuk admin override)
 * @property string $status (active, inactive)
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pin',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin', // Hide PIN dari serialization
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get semua transaksi yang dibuat user ini (sebagai kasir)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    /**
     * Get semua transaksi yang di-void oleh user ini (sebagai admin)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voidedTransactions()
    {
        return $this->hasMany(Transaction::class, 'voided_by');
    }

    /**
     * Get semua produk yang dibuat user ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    /**
     * Get semua audit logs user ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get semua stock movements yang dilakukan user ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter user active saja
     * Usage: User::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter admin saja
     * Usage: User::admins()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope untuk filter cashier saja
     * Usage: User::cashiers()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCashiers($query)
    {
        return $query->where('role', 'cashier');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah user ini adalah admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check apakah user ini adalah cashier
     * 
     * @return bool
     */
    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Check apakah user ini active
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verify PIN admin
     * Digunakan untuk supervisor override (void transaction, dll)
     * 
     * @param string $pin
     * @return bool
     */
    public function verifyPin(string $pin): bool
    {
        return $this->pin === $pin;
    }

    /**
     * Check apakah user punya PIN
     * 
     * @return bool
     */
    public function hasPin(): bool
    {
        return !is_null($this->pin);
    }

    /**
     * Get total sales dari kasir ini (hari ini)
     * 
     * @return float
     */
    public function getTodaySales(): float
    {
        return $this->transactions()
            ->whereDate('transaction_date', today())
            ->where('status', 'completed')
            ->sum('total');
    }

    /**
     * Get jumlah transaksi hari ini
     * 
     * @return int
     */
    public function getTodayTransactionCount(): int
    {
        return $this->transactions()
            ->whereDate('transaction_date', today())
            ->where('status', 'completed')
            ->count();
    }
}
