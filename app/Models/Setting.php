<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model
 * 
 * Model untuk menyimpan konfigurasi sistem yang dapat diubah
 * 
 * COMMON SETTINGS:
 * - tax_rate: Tarif pajak (PPN) dalam persen (contoh: 11)
 * - void_time_limit: Batas waktu void transaksi dalam jam (contoh: 24)
 * - receipt_footer: Text footer di struk (contoh: "Terima Kasih")
 * - low_stock_threshold: Threshold default untuk low stock alert
 * - currency_symbol: Simbol mata uang (contoh: "Rp")
 * - store_name: Nama toko
 * - store_address: Alamat toko
 * - store_phone: Telepon toko
 * 
 * @property int $id
 * @property string $key (Unique identifier setting)
 * @property string|null $value (Nilai setting, bisa JSON)
 * @property string|null $description (Penjelasan setting)
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk get setting by key
     * Usage: Setting::byKey('tax_rate')->first()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS (STATIC)
    |--------------------------------------------------------------------------
    */

    /**
     * Get setting value by key
     * Return default value if not found
     * 
     * Usage:
     * $taxRate = Setting::get('tax_rate', 11);
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Try to decode JSON
        $decoded = json_decode($setting->value, true);
        return $decoded ?? $setting->value;
    }

    /**
     * Set setting value
     * Auto-create if not exists, update if exists
     * 
     * Usage:
     * Setting::set('tax_rate', 11);
     * Setting::set('store_info', ['name' => 'Toko ABC', 'phone' => '08123456789']);
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return static
     */
    public static function set(string $key, $value, ?string $description = null): self
    {
        // Encode array/object to JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Check if setting exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Delete setting
     * 
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        return self::where('key', $key)->delete() > 0;
    }

    /**
     * Get all settings as associative array
     * 
     * Usage:
     * $settings = Setting::all Settings();
     * echo $settings['tax_rate']; // 11
     * 
     * @return array
     */
    public static function allSettings(): array
    {
        $settings = self::all();
        $result = [];

        foreach ($settings as $setting) {
            $decoded = json_decode($setting->value, true);
            $result[$setting->key] = $decoded ?? $setting->value;
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | QUICK ACCESS HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get tax rate setting (in percent)
     * Default: 11% (PPN Indonesia)
     * 
     * @return float
     */
    public static function getTaxRate(): float
    {
        return (float) self::get('tax_rate', 11);
    }

    /**
     * Get void time limit (in hours)
     * Default: 24 hours
     * 
     * @return int
     */
    public static function getVoidTimeLimit(): int
    {
        return (int) self::get('void_time_limit', 24);
    }

    /**
     * Get store name
     * 
     * @return string
     */
    public static function getStoreName(): string
    {
        return self::get('store_name', 'My Store');
    }

    /**
     * Get receipt footer text
     * 
     * @return string
     */
    public static function getReceiptFooter(): string
    {
        return self::get('receipt_footer', 'Terima Kasih Atas Kunjungan Anda');
    }

    /**
     * Get maximum active registers allowed
     * Default: 1 
     * 
     * @return int
     */
    public static function getMaxActiveRegisters(): int
    {
        return (int) self::get('max_active_registers', 1);
    }
}
