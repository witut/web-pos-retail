<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog Model
 * 
 * Model untuk mencatat semua aktivitas penting/sensitive di sistem
 * Berguna untuk tracking security dan compliance
 * 
 * ACTION TYPES yang dicatat:
 * - VOID_TRANSACTION: Pembatalan transaksi
 * - PRICE_CHANGE: Perubahan harga produk
 * - PIN_OVERRIDE: Penggunaan Admin PIN
 * - STOCK_ADJUSTMENT: Penyesuaian stok manual
 * - USER_LOGIN: Login user
 * - USER_CREATED: Pembuatan user baru
 * - ADMIN_ACTION: Aksi admin sensitif lainnya
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $action_type
 * @property string|null $table_name (Tabel yang terpengaruh)
 * @property string|null $record_id (ID record yang terpengaruh)
 * @property array|null $old_values (Nilai sebelum perubahan)
 * @property array|null $new_values (Nilai sesudah perubahan)
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get user yang melakukan action
     * Nullable karena bisa sistem otomatis (cron, dll)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter berdasarkan user
     * Usage: AuditLog::byUser(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan action type
     * Usage: AuditLog::ofType('VOID_TRANSACTION')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope untuk filter berdasarkan tabel
     * Usage: AuditLog::forTable('products')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tableName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope untuk filter berdasarkan periode
     * Usage: AuditLog::betweenDates('2026-02-01', '2026-02-28')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter sensitive actions saja
     * Usage: AuditLog::sensitiveActions()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSensitiveActions($query)
    {
        return $query->whereIn('action_type', [
            'VOID_TRANSACTION',
            'PIN_OVERRIDE',
            'PRICE_CHANGE',
            'STOCK_ADJUSTMENT',
            'USER_CREATED',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Static method untuk create audit log dengan mudah
     * 
     * Usage:
     * AuditLog::logAction('VOID_TRANSACTION', 'transactions', $transactionId, $oldData, $newData);
     * 
     * @param string $actionType
     * @param string|null $tableName
     * @param string|null $recordId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return static
     */
    public static function logAction(
        string $actionType,
        ?string $tableName = null,
        ?string $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get human-readable description of this audit log
     * 
     * @return string
     */
    public function getDescription(): string
    {
        $user = $this->user ? $this->user->name : 'System';

        return match ($this->action_type) {
            'VOID_TRANSACTION' => "{$user} membatalkan transaksi #{$this->record_id}",
            'PIN_OVERRIDE' => "{$user} menggunakan Admin PIN untuk override",
            'PRICE_CHANGE' => "{$user} mengubah harga produk #{$this->record_id}",
            'STOCK_ADJUSTMENT' => "{$user} melakukan penyesuaian stok",
            'USER_CREATED' => "{$user} membuat user baru #{$this->record_id}",
            default => "{$user} melakukan {$this->action_type}",
        };
    }
}
