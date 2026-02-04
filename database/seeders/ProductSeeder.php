<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed sample products untuk testing
     */
    public function run(): void
    {
        // Get categories
        $minumanCat = Category::where('name', 'Minuman')->first();
        $snackCat = Category::where('name', 'Snack')->first();
        $handphoneCat = Category::where('name', 'Handphone')->first();

        if (!$minumanCat || !$snackCat || !$handphoneCat) {
            echo "⚠ Categories not found. Please run CategorySeeder first.\n";
            return;
        }

        // Sample Products - Minuman
        $cocaCola = Product::create([
            'sku' => 'MIN-COCA-001',
            'name' => 'Coca Cola 330ml',
            'category_id' => $minumanCat->id,
            'description' => 'Coca Cola kaleng 330ml',
            'base_unit' => 'pcs',
            'cost_price' => 4500.00,
            'selling_price' => 6000.00,
            'stock_on_hand' => 100,
            'min_stock_alert' => 20,
            'status' => 'active',
            'created_by' => 1, // Admin user
        ]);
        ProductBarcode::create([
            'product_id' => $cocaCola->id,
            'barcode' => '8992761111111',
            'is_primary' => true,
        ]);

        $aqua = Product::create([
            'sku' => 'MIN-AQUA-001',
            'name' => 'Aqua 600ml',
            'category_id' => $minumanCat->id,
            'description' => 'Air mineral Aqua botol 600ml',
            'base_unit' => 'pcs',
            'cost_price' => 2500.00,
            'selling_price' => 3500.00,
            'stock_on_hand' => 200,
            'min_stock_alert' => 50,
            'status' => 'active',
            'created_by' => 1,
        ]);
        ProductBarcode::create([
            'product_id' => $aqua->id,
            'barcode' => '8992761222222',
            'is_primary' => true,
        ]);

        // Sample Products - Snack
        $chitato = Product::create([
            'sku' => 'SNK-CHIT-001',
            'name' => 'Chitato Rasa Sapi Panggang',
            'category_id' => $snackCat->id,
            'description' => 'Chitato 68g',
            'base_unit' => 'pcs',
            'cost_price' => 8000.00,
            'selling_price' => 10000.00,
            'stock_on_hand' => 50,
            'min_stock_alert' => 10,
            'status' => 'active',
            'created_by' => 1,
        ]);
        ProductBarcode::create([
            'product_id' => $chitato->id,
            'barcode' => '8992761333333',
            'is_primary' => true,
        ]);

        $oreo = Product::create([
            'sku' => 'SNK-OREO-001',
            'name' => 'Oreo Vanilla 137g',
            'category_id' => $snackCat->id,
            'description' => 'Biskuit Oreo vanilla',
            'base_unit' => 'pcs',
            'cost_price' => 9500.00,
            'selling_price' => 12000.00,
            'stock_on_hand' => 30,
            'min_stock_alert' => 10,
            'status' => 'active',
            'created_by' => 1,
        ]);
        ProductBarcode::create([
            'product_id' => $oreo->id,
            'barcode' => '8992761444444',
            'is_primary' => true,
        ]);

        // Sample Product - Electronics (high value)
        $samsung = Product::create([
            'sku' => 'ELC-SAMS-001',
            'name' => 'Samsung Galaxy A54 8/256GB',
            'category_id' => $handphoneCat->id,
            'description' => 'Samsung Galaxy A54 RAM 8GB ROM 256GB',
            'base_unit' => 'unit',
            'cost_price' => 4500000.00,
            'selling_price' => 5200000.00,
            'stock_on_hand' => 5,
            'min_stock_alert' => 2,
            'tax_rate' => 11.00,
            'status' => 'active',
            'created_by' => 1,
        ]);
        ProductBarcode::create([
            'product_id' => $samsung->id,
            'barcode' => '8992761555555',
            'is_primary' => true,
        ]);

        // Low stock product untuk testing alert
        $sprite = Product::create([
            'sku' => 'MIN-SPRI-001',
            'name' => 'Sprite 330ml',
            'category_id' => $minumanCat->id,
            'description' => 'Sprite kaleng 330ml',
            'base_unit' => 'pcs',
            'cost_price' => 4500.00,
            'selling_price' => 6000.00,
            'stock_on_hand' => 5, // LOW STOCK
            'min_stock_alert' => 20,
            'status' => 'active',
            'created_by' => 1,
        ]);
        ProductBarcode::create([
            'product_id' => $sprite->id,
            'barcode' => '8992761666666',
            'is_primary' => true,
        ]);

        echo "✓ 6 sample products created\n";
        echo "  - Coca Cola 330ml (Rp 6.000, stock: 100)\n";
        echo "  - Aqua 600ml (Rp 3.500, stock: 200)\n";
        echo "  - Chitato (Rp 10.000, stock: 50)\n";
        echo "  - Oreo (Rp 12.000, stock: 30)\n";
        echo "  - Samsung A54 (Rp 5.200.000, stock: 5)\n";
        echo "  - Sprite (Rp 6.000, stock: 5 - LOW STOCK)\n";
    }
}
