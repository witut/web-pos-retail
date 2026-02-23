<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // 1. Setup Test Data
    $productA = Product::create([
        'sku' => 'PROD-TEST-A',
        'name' => 'Produk Test A',
        'category_id' => 1,
        'purchase_price' => 10000,
        'selling_price' => 15000,
        'stock' => 100,
        'min_stock' => 10,
    ]);

    $productB = Product::create([
        'sku' => 'PROD-TEST-B',
        'name' => 'Produk Test B',
        'category_id' => 1,
        'purchase_price' => 20000,
        'selling_price' => 30000,
        'stock' => 100,
        'min_stock' => 10,
    ]);

    // 2. Setup Promotion (Fixed Amount - e.g., 2000 per qty)
    $promoFixed = Promotion::create([
        'name' => 'Promo Fixed 2000',
        'description' => 'Diskon 2000 per qty',
        'type' => 'fixed_amount',
        'value' => 2000,
        'min_purchase' => 0,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(5),
        'is_active' => true,
    ]);
    
    // Attach to Product A only
    $promoFixed->products()->attach($productA->id);

    // 3. Setup Promotion (Percentage - e.g., 10%)
    $promoPercent = Promotion::create([
        'name' => 'Promo Diskon 10%',
        'description' => 'Diskon 10% untuk B',
        'type' => 'percentage',
        'value' => 10,
        'min_purchase' => 0,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(5),
        'is_active' => true,
    ]);
    
    // Attach to Product B only
    $promoPercent->products()->attach($productB->id);

    // 4. Test Calculation
    $promotionService = app(PromotionService::class);
    
    $cartItems = [
        [
            'product_id' => $productA->id,
            'qty' => 3, // Should be 3 * 2000 = 6000 discount, price 15000 * 3 = 45000
            'price' => 15000,
        ],
        [
            'product_id' => $productB->id,
            'qty' => 2, // Should be 2 * 30000 * 10% = 6000 discount, price 30000 * 2 = 60000
            'price' => 30000,
        ]
    ];
    
    echo "--- Cart Items Input ---\n";
    print_r($cartItems);

    $result = $promotionService->calculateCartDiscount($cartItems);

    echo "\n--- Calculation Result ---\n";
    echo "Subtotal: " . $result['subtotal'] . "\n";
    echo "Total Discount: " . $result['discount_amount'] . "\n";
    echo "Final Total: " . $result['final_total'] . "\n";
    
    echo "\n--- Applied Promotions ---\n";
    print_r($result['applied_promotions']);

    echo "\n--- Item Breakdown ---\n";
    print_r($result['items']);
    
    // Validations
    $expectedSubtotal = (15000 * 3) + (30000 * 2); // 45000 + 60000 = 105000
    $expectedDiscount = (3 * 2000) + (60000 * 0.10); // 6000 + 6000 = 12000
    
    if ($result['subtotal'] == $expectedSubtotal && $result['discount_amount'] == $expectedDiscount) {
        echo "\n[SUCCESS] Promotion Calculation Edge Cases Passed!\n";
    } else {
        echo "\n[FAILED] Expected Subtotal: $expectedSubtotal, Got: " . $result['subtotal'] . "\n";
        echo "Expected Discount: $expectedDiscount, Got: " . $result['discount_amount'] . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

DB::rollBack();

