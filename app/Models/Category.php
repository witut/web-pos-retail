<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Category Model
 * 
 * Model untuk kategori produk dengan struktur hierarchical (parent-child).
 * Contoh: Elektronik > Laptop > Laptop Gaming
 * 
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get parent category (kategori induk)
     * Contoh: "Laptop Gaming" belongsTo "Laptop"
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get child categories (sub-kategori)
     * Contoh: "Elektronik" hasMany ["Laptop", "HP", "TV"]
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all products in this category
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk filter hanya kategori parent (top-level)
     * Usage: Category::parents()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk filter kategori berdasarkan parent
     * Usage: Category::ofParent(1)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $parentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check apakah kategori ini adalah parent (top-level)
     * 
     * @return bool
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check apakah kategori ini punya sub-kategori
     * 
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get full category path (breadcrumb)
     * Contoh: "Elektronik > Laptop > Laptop Gaming"
     * 
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
}
