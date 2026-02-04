<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed categories with hierarchical structure:
     * - Elektronik
     *   - Laptop
     *   - HP & Tablet
     * - Fashion
     *   - Pakaian Pria
     *   - Pakaian Wanita
     * - Makanan & Minuman
     *   - Snack
     *   - Minuman
     */
    public function run(): void
    {
        // Parent Categories
        $elektronik = Category::create([
            'name' => 'Elektronik',
            'description' => 'Produk elektronik dan gadget',
            'parent_id' => null,
        ]);

        $fashion = Category::create([
            'name' => 'Fashion',
            'description' => 'Pakaian dan aksesoris',
            'parent_id' => null,
        ]);

        $makananMinuman = Category::create([
            'name' => 'Makanan & Minuman',
            'description' => 'Produk makanan dan minuman',
            'parent_id' => null,
        ]);

        $alat = Category::create([
            'name' => 'Alat Tulis',
            'description' => 'Alat tulis kantor dan sekolah',
            'parent_id' => null,
        ]);

        echo "✓ 4 Parent categories created\n";

        // Child Categories - Elektronik
        Category::create([
            'name' => 'Laptop',
            'description' => 'Laptop dan notebook',
            'parent_id' => $elektronik->id,
        ]);

        Category::create([
            'name' => 'HP & Tablet',
            'description' => 'Handphone dan tablet',
            'parent_id' => $elektronik->id,
        ]);

        Category::create([
            'name' => 'Aksesoris',
            'description' => 'Aksesoris elektronik',
            'parent_id' => $elektronik->id,
        ]);

        // Child Categories - Fashion
        Category::create([
            'name' => 'Pakaian Pria',
            'description' => 'Pakaian untuk pria',
            'parent_id' => $fashion->id,
        ]);

        Category::create([
            'name' => 'Pakaian Wanita',
            'description' => 'Pakaian untuk wanita',
            'parent_id' => $fashion->id,
        ]);

        Category::create([
            'name' => 'Sepatu',
            'description' => 'Sepatu pria dan wanita',
            'parent_id' => $fashion->id,
        ]);

        // Child Categories - Makanan & Minuman
        Category::create([
            'name' => 'Snack',
            'description' => 'Makanan ringan',
            'parent_id' => $makananMinuman->id,
        ]);

        Category::create([
            'name' => 'Minuman',
            'description' => 'Minuman kemasan',
            'parent_id' => $makananMinuman->id,
        ]);

        Category::create([
            'name' => 'Makanan Berat',
            'description' => 'Makanan instan dan berat',
            'parent_id' => $makananMinuman->id,
        ]);

        // Child Categories - Alat Tulis
        Category::create([
            'name' => 'Buku',
            'description' => 'Buku tulis dan catatan',
            'parent_id' => $alat->id,
        ]);

        Category::create([
            'name' => 'Pulpen & Pensil',
            'description' => 'Alat tulis pulpen dan pensil',
            'parent_id' => $alat->id,
        ]);

        echo "✓ 11 Child categories created\n";
        echo "✓ Total: 15 categories with hierarchical structure\n";
    }
}
