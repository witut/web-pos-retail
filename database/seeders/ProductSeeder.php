<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductUnit;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed 25 realistic products with barcodes and UOMs
     */
    public function run(): void
    {
        // Get categories for assignment
        $laptop = Category::where('name', 'Laptop')->first();
        $hp = Category::where('name', 'HP & Tablet')->first();
        $aksesoris = Category::where('name', 'Aksesoris')->first();
        $priaPakaian = Category::where('name', 'Pakaian Pria')->first();
        $wanitaPakaian = Category::where('name', 'Pakaian Wanita')->first();
        $sepatu = Category::where('name', 'Sepatu')->first();
        $snack = Category::where('name', 'Snack')->first();
        $minuman = Category::where('name', 'Minuman')->first();
        $buku = Category::where('name', 'Buku')->first();
        $pulpen = Category::where('name', 'Pulpen & Pensil')->first();

        $products = [
            // Elektronik - Laptop
            [
                'category_id' => $laptop->id,
                'sku' => 'LAPTOP-00001',
                'name' => 'Laptop Asus ROG Strix G15',
                'description' => 'Gaming laptop with RTX 3060, Intel i7',
                'base_unit' => 'pcs',
                'cost_price' => 15000000,
                'selling_price' => 17500000,
                'stock_on_hand' => 5,
                'min_stock_alert' => 2,
                'status' => 'active',
                'barcodes' => ['8886419350590'],
                'uoms' => [],
            ],
            [
                'category_id' => $laptop->id,
                'sku' => 'LAPTOP-00002',
                'name' => 'MacBook Air M2',
                'description' => 'Apple MacBook Air with M2 chip, 8GB RAM, 256GB SSD',
                'base_unit' => 'pcs',
                'cost_price' => 16000000,
                'selling_price' => 18500000,
                'stock_on_hand' => 3,
                'min_stock_alert' => 1,
                'status' => 'active',
                'barcodes' => ['194253081470'],
                'uoms' => [],
            ],

            // Elektronik - HP & Tablet
            [
                'category_id' => $hp->id,
                'sku' => 'HP-00001',
                'name' => 'iPhone 14 Pro 256GB',
                'description' => 'Apple iPhone 14 Pro, Deep Purple, 256GB',
                'base_unit' => 'pcs',
                'cost_price' => 17000000,
                'selling_price' => 19500000,
                'stock_on_hand' => 8,
                'min_stock_alert' => 3,
                'status' => 'active',
                'barcodes' => ['194253395706'],
                'uoms' => [],
            ],
            [
                'category_id' => $hp->id,
                'sku' => 'HP-00002',
                'name' => 'Samsung Galaxy S23 Ultra',
                'description' => 'Samsung Galaxy S23 Ultra 12GB/256GB',
                'base_unit' => 'pcs',
                'cost_price' => 15500000,
                'selling_price' => 17800000,
                'stock_on_hand' => 12,
                'min_stock_alert' => 5,
                'status' => 'active',
                'barcodes' => ['8806094823875'],
                'uoms' => [],
            ],

            // Elektronik - Aksesoris
            [
                'category_id' => $aksesoris->id,
                'sku' => 'AKSESORIS-00001',
                'name' => 'Charger iPhone 20W',
                'description' => 'Apple 20W USB-C Power Adapter',
                'base_unit' => 'pcs',
                'cost_price' => 250000,
                'selling_price' => 350000,
                'stock_on_hand' => 50,
                'min_stock_alert' => 10,
                'status' => 'active',
                'barcodes' => ['194252158630'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 10, 'selling_price' => 3200000],
                ],
            ],
            [
                'category_id' => $aksesoris->id,
                'sku' => 'AKSESORIS-00002',
                'name' => 'Kabel USB-C to Lightning',
                'description' => 'Apple USB-C to Lightning Cable 1m',
                'base_unit' => 'pcs',
                'cost_price' => 200000,
                'selling_price' => 280000,
                'stock_on_hand' => 75,
                'min_stock_alert' => 20,
                'status' => 'active',
                'barcodes' => ['194252031346'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 12, 'selling_price' => 3000000],
                ],
            ],

            // Fashion - Pakaian Pria
            [
                'category_id' => $priaPakaian->id,
                'sku' => 'PRIAPAK-00001',
                'name' => 'Kemeja Batik Pria',
                'description' => 'Kemeja batik motif parang, size M-XXL',
                'base_unit' => 'pcs',
                'cost_price' => 150000,
                'selling_price' => 225000,
                'stock_on_hand' => 30,
                'min_stock_alert' => 10,
                'status' => 'active',
                'barcodes' => ['8991234567890'],
                'uoms' => [],
            ],
            [
                'category_id' => $priaPakaian->id,
                'sku' => 'PRIAPAK-00002',
                'name' => 'Kaos Polo Lacoste',
                'description' => 'Kaos polo Lacoste original, berbagai warna',
                'base_unit' => 'pcs',
                'cost_price' => 450000,
                'selling_price' => 650000,
                'stock_on_hand' => 25,
                'min_stock_alert' => 8,
                'status' => 'active',
                'barcodes' => ['3614037909576'],
                'uoms' => [],
            ],

            // Fashion - Pakaian Wanita
            [
                'category_id' => $wanitaPakaian->id,
                'sku' => 'WANITAPAK-00001',
                'name' => 'Dress Casual Wanita',
                'description' => 'Dress casual motif floral, katun premium',
                'base_unit' => 'pcs',
                'cost_price' => 180000,
                'selling_price' => 275000,
                'stock_on_hand' => 20,
                'min_stock_alert' => 5,
                'status' => 'active',
                'barcodes' => ['8992345678901'],
                'uoms' => [],
            ],
            [
                'category_id' => $wanitaPakaian->id,
                'sku' => 'WANITAPAK-00002',
                'name' => 'Hijab Segi Empat',
                'description' => 'Hijab voal premium, berbagai warna',
                'base_unit' => 'pcs',
                'cost_price' => 35000,
                'selling_price' => 55000,
                'stock_on_hand' => 100,
                'min_stock_alert' => 30,
                'status' => 'active',
                'barcodes' => ['8993456789012'],
                'uoms' => [
                    ['unit_name' => 'lusin', 'qty_in_base_unit' => 12, 'selling_price' => 600000],
                ],
            ],

            // Fashion - Sepatu
            [
                'category_id' => $sepatu->id,
                'sku' => 'SEPATU-00001',
                'name' => 'Sepatu Nike Air Max',
                'description' => 'Nike Air Max 270, size 40-45',
                'base_unit' => 'pcs',
                'cost_price' => 1200000,
                'selling_price' => 1700000,
                'stock_on_hand' => 15,
                'min_stock_alert' => 5,
                'status' => 'active',
                'barcodes' => ['194953014358'],
                'uoms' => [],
            ],
            [
                'category_id' => $sepatu->id,
                'sku' => 'SEPATU-00002',
                'name' => 'Sandal Jepit Swallow',
                'description' => 'Sandal jepit Swallow original',
                'base_unit' => 'pcs',
                'cost_price' => 15000,
                'selling_price' => 25000,
                'stock_on_hand' => 200,
                'min_stock_alert' => 50,
                'status' => 'active',
                'barcodes' => ['8994567890123'],
                'uoms' => [
                    ['unit_name' => 'lusin', 'qty_in_base_unit' => 12, 'selling_price' => 280000],
                ],
            ],

            // Makanan - Snack
            [
                'category_id' => $snack->id,
                'sku' => 'SNACK-00001',
                'name' => 'Chitato Rasa Sapi Panggang',
                'description' => 'Keripik kentang Chitato 68g',
                'base_unit' => 'pcs',
                'cost_price' => 8000,
                'selling_price' => 12000,
                'stock_on_hand' => 150,
                'min_stock_alert' => 50,
                'status' => 'active',
                'barcodes' => ['8992753820013'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 20, 'selling_price' => 220000],
                ],
            ],
            [
                'category_id' => $snack->id,
                'sku' => 'SNACK-00002',
                'name' => 'Oreo Original',
                'description' => 'Biskuit Oreo Original 137g',
                'base_unit' => 'pcs',
                'cost_price' => 10000,
                'selling_price' => 15000,
                'stock_on_hand' => 120,
                'min_stock_alert' => 40,
                'status' => 'active',
                'barcodes' => ['8850127001011'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 24, 'selling_price' => 340000],
                ],
            ],
            [
                'category_id' => $snack->id,
                'sku' => 'SNACK-00003',
                'name' => 'Tango Wafer Cokelat',
                'description' => 'Wafer Tango rasa cokelat',
                'base_unit' => 'pcs',
                'cost_price' => 1500,
                'selling_price' => 2500,
                'stock_on_hand' => 300,
                'min_stock_alert' => 100,
                'status' => 'active',
                'barcodes' => ['8992753820204'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 60, 'selling_price' => 140000],
                ],
            ],

            // Makanan - Minuman
            [
                'category_id' => $minuman->id,
                'sku' => 'MINUMAN-00001',
                'name' => 'Aqua Botol 600ml',
                'description' => 'Air mineral Aqua 600ml',
                'base_unit' => 'pcs',
                'cost_price' => 3000,
                'selling_price' => 5000,
                'stock_on_hand' => 500,
                'min_stock_alert' => 100,
                'status' => 'active',
                'barcodes' => ['8992761111014'],
                'uoms' => [
                    ['unit_name' => 'dus', 'qty_in_base_unit' => 24, 'selling_price' => 110000],
                ],
            ],
            [
                'category_id' => $minuman->id,
                'sku' => 'MINUMAN-00002',
                'name' => 'Coca Cola 330ml Kaleng',
                'description' => 'Coca Cola kaleng 330ml',
                'base_unit' => 'pcs',
                'cost_price' => 5500,
                'selling_price' => 8000,
                'stock_on_hand' => 200,
                'min_stock_alert' => 50,
                'status' => 'active',
                'barcodes' => ['5000112637724'],
                'uoms' => [
                    ['unit_name' => 'tray', 'qty_in_base_unit' => 24, 'selling_price' => 180000],
                ],
            ],
            [
                'category_id' => $minuman->id,
                'sku' => 'MINUMAN-00003',
                'name' => 'Teh Botol Sosro 450ml',
                'description' => 'Teh botol Sosro 450ml',
                'base_unit' => 'pcs',
                'cost_price' => 4000,
                'selling_price' => 6500,
                'stock_on_hand' => 250,
                'min_stock_alert' => 80,
                'status' => 'active',
                'barcodes' => ['8992753820501'],
                'uoms' => [
                    ['unit_name' => 'dus', 'qty_in_base_unit' => 24, 'selling_price' => 145000],
                ],
            ],

            // Alat Tulis - Buku
            [
                'category_id' => $buku->id,
                'sku' => 'BUKU-00001',
                'name' => 'Buku Tulis Sinar Dunia 38 Lembar',
                'description' => 'Buku tulis Sinar Dunia 38 lembar',
                'base_unit' => 'pcs',
                'cost_price' => 3500,
                'selling_price' => 5500,
                'stock_on_hand' => 200,
                'min_stock_alert' => 50,
                'status' => 'active',
                'barcodes' => ['8995678901234'],
                'uoms' => [
                    ['unit_name' => 'pack', 'qty_in_base_unit' => 10, 'selling_price' => 52000],
                ],
            ],
            [
                'category_id' => $buku->id,
                'sku' => 'BUKU-00002',
                'name' => 'Buku Gambar A4',
                'description' => 'Buku gambar ukuran A4',
                'base_unit' => 'pcs',
                'cost_price' => 8000,
                'selling_price' => 12000,
                'stock_on_hand' => 80,
                'min_stock_alert' => 20,
                'status' => 'active',
                'barcodes' => ['8996789012345'],
                'uoms' => [],
            ],

            // Alat Tulis - Pulpen
            [
                'category_id' => $pulpen->id,
                'sku' => 'PULPEN-00001',
                'name' => 'Pulpen Snowman',
                'description' => 'Pulpen Snowman hitam/biru/merah',
                'base_unit' => 'pcs',
                'cost_price' => 2000,
                'selling_price' => 3500,
                'stock_on_hand' => 500,
                'min_stock_alert' => 100,
                'status' => 'active',
                'barcodes' => ['8997890123456'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 12, 'selling_price' => 40000],
                ],
            ],
            [
                'category_id' => $pulpen->id,
                'sku' => 'PULPEN-00002',
                'name' => 'Pensil 2B Faber Castell',
                'description' => 'Pensil 2B Faber Castell',
                'base_unit' => 'pcs',
                'cost_price' => 3000,
                'selling_price' => 5000,
                'stock_on_hand' => 300,
                'min_stock_alert' => 80,
                'status' => 'active',
                'barcodes' => ['4005401191209'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 12, 'selling_price' => 57000],
                ],
            ],
            [
                'category_id' => $pulpen->id,
                'sku' => 'PULPEN-00003',
                'name' => 'Penghapus Steadtler',
                'description' => 'Penghapus putih Steadtler',
                'base_unit' => 'pcs',
                'cost_price' => 4000,
                'selling_price' => 6500,
                'stock_on_hand' => 150,
                'min_stock_alert' => 40,
                'status' => 'active',
                'barcodes' => ['4007817525203'],
                'uoms' => [
                    ['unit_name' => 'box', 'qty_in_base_unit' => 24, 'selling_price' => 145000],
                ],
            ],

            // Low stock sample
            [
                'category_id' => $laptop->id,
                'sku' => 'LAPTOP-00003',
                'name' => 'Dell XPS 13',
                'description' => 'Dell XPS 13, Intel i7, 16GB RAM',
                'base_unit' => 'pcs',
                'cost_price' => 18000000,
                'selling_price' => 21000000,
                'stock_on_hand' => 1, // Low stock
                'min_stock_alert' => 3,
                'status' => 'active',
                'barcodes' => ['884116330493'],
                'uoms' => [],
            ],
        ];

        $count = 0;
        foreach ($products as $productData) {
            // Extract barcodes and uoms
            $barcodes = $productData['barcodes'];
            $uoms = $productData['uoms'];
            unset($productData['barcodes'], $productData['uoms']);

            // Create product
            $product = Product::create($productData);

            // Create barcodes
            foreach ($barcodes as $barcode) {
                ProductBarcode::create([
                    'product_id' => $product->id,
                    'barcode' => $barcode,
                ]);
            }

            // Create UOMs
            foreach ($uoms as $uom) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_name' => $uom['unit_name'],
                    'conversion_rate' => $uom['qty_in_base_unit'],
                    'selling_price' => $uom['selling_price'],
                ]);
            }

            $count++;
        }

        echo "✓ {$count} Products created with barcodes and UOMs\n";
        echo "✓ Categories covered: Elektronik, Fashion, Makanan/Minuman, Alat Tulis\n";
        echo "✓ Stock range: 1 to 500 units\n";
    }
}
