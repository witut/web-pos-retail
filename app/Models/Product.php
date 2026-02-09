<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Product Model
 * 
 * Model untuk master data produk dengan support:
 * - Multiple barcodes
 * - Multi-unit (UOM system)
 * - Strict decimal typing untuk harga dan stok
 * - Auto-calculated HPP (weighted average)
 * 
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property int $category_id
 * @property string|null $brand
 * @property string $base_unit (pcs, kg, liter, box, dozen)
 * @property float $selling_price
 * @property float $cost_price (HPP - Auto calculated)
 * @property float $stock_on_hand
 * @property float $min_stock_alert
 * @property string $status (active, inactive)
 * @property string|null $image_path
 * @property float|null $tax_rate
 * @property int|null $created_by
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'brand',
        'base_unit',
        'selling_price',
        'cost_price',
        'stock_on_hand',
        'min_stock_alert',
        'status',
        'image_path',
        'tax_rate',
        'created_by',
    ];

    /**
     * Cast attributes ke tipe data yang benar
     * PENTING: decimal fields di-cast ke float untuk perhitungan
     */
    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_on_hand' => 'decimal:2',
        'min_stock_alert' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get kategori produk ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get semua barcode untuk produk ini
     * Contoh: 1 produk bisa punya barcode: "8991234567890", "SKU-12345"
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class);
    }

    /**
     * Get barcode utama (primary)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primaryBarcode()
    {
        return $this->hasOne(ProductBarcode::class)->where('is_primary', true);
    }

    /**
     * Get semua unit alternatif produk ini
     * Contoh: Base unit = pcs, alternate units = [Box (12 pcs), Karton (144 pcs)]
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Get unit alternatif yang aktif saja
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeUnits()
    {
        return $this->hasMany(ProductUnit::class)->where('is_active', true);
    }

    /**
     * Get user yang membuat produk ini
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get semua stock movements (history keluar-masuk) produk ini
     * Berguna untuk kartu stok
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get semua transaction items yang pernah dijual
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter produk aktif saja
     * Usage: Product::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter produk dengan stok rendah (low stock)
     * Usage: Product::lowStock()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_on_hand <= min_stock_alert');
    }

    /**
     * Scope untuk filter produk berdasarkan kategori
     * Usage: Product::ofCategory(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope untuk search produk (name, SKU, atau barcode)
     * Usage: Product::search('laptop')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%")
                ->orWhereHas('barcodes', function ($bq) use ($keyword) {
                    $bq->where('barcode', 'like', "%{$keyword}%");
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Format harga jual ke Rupiah
     * Usage: $product->formatted_price
     * Output: "Rp 15.000"
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->selling_price, 0, ',', '.')
        );
    }

    /**
     * Accessor: Format HPP ke Rupiah
     * Usage: $product->formatted_cost
     */
    protected function formattedCost(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->cost_price, 0, ',', '.')
        );
    }

    /**
     * Accessor: Hitung profit margin percentage
     * Usage: $product->profit_margin
     * Output: 25.5 (artinya 25.5%)
     */
    protected function profitMargin(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->cost_price == 0)
                    return 0;
                return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah stok produk rendah (low stock alert)
     * 
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock_on_hand <= $this->min_stock_alert;
    }

    /**
     * Check apakah produk out of stock
     * 
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_on_hand <= 0;
    }

    /**
     * Check apakah stok tersedia untuk qty tertentu
     * 
     * @param float $qty
     * @return bool
     */
    public function hasStock(float $qty): bool
    {
        return $this->stock_on_hand >= $qty;
    }

    /**
     * Get conversion rate for a specific unit name
     * 
     * @param string $unitName
     * @return float
     */
    public function getConversionRate(string $unitName): float
    {
        if ($unitName === $this->base_unit) {
            return 1;
        }

        $unit = $this->units()->where('unit_name', $unitName)->first();

        if ($unit) {
            return (float) $unit->conversion_rate;
        }

        return 1; // Default fallback if not found
    }

    /**
     * Get full image URL
     * 
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset('storage/' . $this->image_path);
    }
}
