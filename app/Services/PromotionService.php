<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Calculate potential discount for a cart or transaction context.
     * This is complex as it needs to handle various promotion types.
     * 
     * @param array $cartItems Array of items ['product_id', 'qty', 'price']
     * @param string|null $couponCode
     * @return array Result with 'discount_amount', 'applied_promotions', 'final_total', 'items'
     */
    public function calculateCartDiscount(array $cartItems, ?string $couponCode = null)
    {
        $subtotal = 0;
        $itemsCollection = collect($cartItems)->map(function ($item) use (&$subtotal) {
            $item['subtotal'] = $item['qty'] * $item['price'];
            $item['discount_amount'] = 0; // Initialize per-item discount
            $subtotal += $item['subtotal'];
            return (object) $item;
        });

        $discountAmount = 0;
        $appliedPromotions = [];

        // 1. Check for Automatic Promotions (Active, Date valid)
        $promotions = Promotion::active()->get();

        foreach ($promotions as $promotion) {
            // Skip if minimum purchase not met
            if ($subtotal < $promotion->min_purchase) {
                continue;
            }

            // Check if promotion applies to specific products
            $applicableItems = $itemsCollection;
            if ($promotion->products()->count() > 0) {
                $productIds = $promotion->products->pluck('id')->toArray();
                $applicableItems = $itemsCollection->whereIn('product_id', $productIds);

                if ($applicableItems->isEmpty()) {
                    continue;
                }
            }

            // Calculate Discount based on Type
            $promoDiscount = 0;

            if ($promotion->type === 'percentage') {
                // Percentage of applicable items subtotal
                // Percentage of applicable items subtotal
                foreach ($applicableItems as $item) {
                    $itemDiscount = $item->subtotal * ($promotion->value / 100);
                    $item->discount_amount += $itemDiscount;
                    $promoDiscount += $itemDiscount;
                }
            } elseif ($promotion->type === 'fixed_amount') {
                // Fixed amount (once per transaction usually)
                // Fixed amount (once per transaction usually)
                $promoDiscount = $promotion->value;

                // Distribute fixed amount proportionally to applicable items (optional but good for refund logic)
                $itemsSubtotal = $applicableItems->sum('subtotal');
                if ($itemsSubtotal > 0) {
                    foreach ($applicableItems as $item) {
                        $ratio = $item->subtotal / $itemsSubtotal;
                        $item->discount_amount += $promoDiscount * $ratio;
                    }
                }
            } elseif ($promotion->type === 'buy_x_get_y') {
                // Simplified Buy X Get Y logic: Discount = Price of Y
                // Needs more complex structure to define "Buy what, Get what"
                // For now, assuming "Buy X qty of THIS product, get discount" 
                // This is a placeholder for complex logic
            }

            if ($promoDiscount > 0) {
                $discountAmount += $promoDiscount;
                $appliedPromotions[] = [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'amount' => $promoDiscount
                ];
            }
        }

        // 2. Check Coupon
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if ($coupon && $coupon->isValid()) {
                $couponDiscount = 0;

                if ($coupon->type === 'percentage') {
                    $couponDiscount = $subtotal * ($coupon->value / 100);
                } else {
                    $couponDiscount = $coupon->value;
                }

                $discountAmount += $couponDiscount;
                $appliedPromotions[] = [
                    'code' => $coupon->code,
                    'name' => 'Coupon: ' . $coupon->code,
                    'amount' => $couponDiscount
                ];
            }
        }

        // Cap discount at subtotal (cannot be negative)
        $discountAmount = min($discountAmount, $subtotal);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'final_total' => $subtotal - $discountAmount,

            'applied_promotions' => $appliedPromotions,
            'items' => $itemsCollection->map(function ($item) {
                return (array) $item;
            })->toArray()
        ];
    }
}
