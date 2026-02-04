<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed sample categories dengan struktur hierarchical
     */
    public function run(): void
    {
        // Parent categories
        $electronics = Category::create([
            'name' => 'Elektronik',
            'description' => 'Produk elektronik dan gadget',
            'parent_id' => null,
        ]);

        $food = Category::create([
            'name' => 'Makanan & Minuman',
            'description' => 'Produk makanan dan minuman',
            'parent_id' => null,
        ]);

        $fashion = Category::create([
            'name' => 'Fashion',
            'description' => 'Pakaian dan aksesoris',
            'parent_id' => null,
        ]);

        $home = Category::create([
            'name' => 'Rumah Tangga',
            'description' => 'Perlengkapan rumah tangga',
            'parent_id' => null,
        ]);

        // Child categories - Elektronik
        Category::create([
            'name' => 'Handphone',
            'description' => 'Smartphone dan accessories',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'Laptop & Komputer',
            'description' => 'Laptop, PC, dan aksesoris',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'Audio & Video',
            'description' => 'Speaker, headphone, TV',
            'parent_id' => $electronics->id,
        ]);

        // Child categories - Makanan & Minuman
        Category::create([
            'name' => 'Minuman',
            'description' => 'Soft drink, juice, air mineral',
            'parent_id' => $food->id,
        ]);

        Category::create([
            'name' => 'Snack',
            'description' => 'Keripik, kue, permen',
            'parent_id' => $food->id,
        ]);

        Category::create([
            'name' => 'Makanan Instan',
            'description' => 'Mie instan, bumbu instan',
            'parent_id' => $food->id,
        ]);

        // Child categories - Fashion
        Category::create([
            'name' => 'Pakaian Pria',
            'description' => 'Kemeja, celana, jaket pria',
            'parent_id' => $fashion->id,
        ]);

        Category::create([
            'name' => 'Pakaian Wanita',
            'description' => 'Blouse, rok, dress',
            'parent_id' => $fashion->id,
        ]);

        // Child categories - Rumah Tangga
        Category::create([
            'name' => 'Alat Dapur',
            'description' => 'Panci, wajan, pisau',
            'parent_id' => $home->id,
        ]);

        Category::create([
            'name' => 'Pembersih',
            'description' => 'Sabun cuci, detergen, pembersih lantai',
            'parent_id' => $home->id,
        ]);

        echo "✓ 14 categories created (4 parent + 10 child)\n";
        echo "  - Elektronik (Handphone, Laptop, Audio)\n";
        echo "  - Makanan & Minuman (Minuman, Snack, Instan)\n";
        echo "  - Fashion (Pria, Wanita)\n";
        echo "  - Rumah Tangga (Dapur, Pembersih)\n";
    }
}
