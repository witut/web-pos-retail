<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Setting;
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

        $allowStacking = Setting::get('discount.allow_stacking', '1') == '1';

        // 1. Check for Automatic Promotions (Active, Date valid)
        $promotions = Promotion::with('products')->active()->get();

        foreach ($itemsCollection as $item) {
            $item->best_discount = 0;
            $item->best_promo = null;
            $item->stack_discounts = []; // For allowStacking = true
            $item->eligible_promos = [];
        }

        foreach ($promotions as $promotion) {
            // Skip if minimum purchase not met
            if ($subtotal < $promotion->min_purchase) {
                continue;
            }

            // Check if promotion applies to specific products
            $productIds = $promotion->products->pluck('id')->toArray();
            if (empty($productIds))
                continue;

            $applicableItems = $itemsCollection->whereIn('product_id', $productIds);
            if ($applicableItems->isEmpty())
                continue;

            foreach ($applicableItems as $item) {
                $item->eligible_promos[] = $promotion;

                $itemDiscount = 0;
                if ($promotion->type === 'percentage') {
                    // Capped by item subtotal minus existing discount if stacking
                    $baseForDiscount = $allowStacking ? ($item->subtotal - array_sum(array_column($item->stack_discounts, 'amount'))) : $item->subtotal;
                    $itemDiscount = $baseForDiscount * ($promotion->value / 100);
                } elseif ($promotion->type === 'fixed_amount') {
                    $itemDiscount = min($promotion->value, $item->price) * $item->qty;
                } elseif ($promotion->type === 'buy_x_get_y') {
                    $buyQty = $promotion->buy_qty ?: 1;
                    $getQty = $promotion->get_qty ?: 1;

                    // Logic: You pay for `buyQty`, and the next `getQty` items are free.
                    // E.g. Buy 2 Get 1. qty=2 -> free=0. qty=3 -> free=1. qty=4 -> free=1. qty=6 -> free=2.
                    $discountQty = 0;
                    $remainingQty = $item->qty;
                    while ($remainingQty > $buyQty) {
                        $freeQty = min($remainingQty - $buyQty, $getQty);
                        $discountQty += $freeQty;
                        $remainingQty -= ($buyQty + $getQty);
                    }
                    $itemDiscount = $discountQty * $item->price;
                }

                if ($itemDiscount > 0) {
                    if ($allowStacking) {
                        $item->stack_discounts[] = [
                            'promo' => $promotion,
                            'amount' => $itemDiscount
                        ];
                    } else {
                        // Keep the best discount for this item
                        if ($itemDiscount > $item->best_discount) {
                            $item->best_discount = $itemDiscount;
                            $item->best_promo = $promotion;
                        }
                    }
                }
            }
        }

        $appliedPromotionsMap = [];

        foreach ($itemsCollection as $item) {
            if ($allowStacking) {
                foreach ($item->stack_discounts as $sd) {
                    $item->discount_amount += $sd['amount'];
                    $promoId = $sd['promo']->id;
                    if (!isset($appliedPromotionsMap[$promoId])) {
                        $appliedPromotionsMap[$promoId] = [
                            'id' => $sd['promo']->id,
                            'name' => $sd['promo']->name,
                            'amount' => 0
                        ];
                    }
                    $appliedPromotionsMap[$promoId]['amount'] += $sd['amount'];
                    $discountAmount += $sd['amount'];
                }
            } else {
                if ($item->best_discount > 0) {
                    $item->discount_amount = $item->best_discount;
                    $promoId = $item->best_promo->id;
                    if (!isset($appliedPromotionsMap[$promoId])) {
                        $appliedPromotionsMap[$promoId] = [
                            'id' => $item->best_promo->id,
                            'name' => $item->best_promo->name,
                            'amount' => 0
                        ];
                    }
                    $appliedPromotionsMap[$promoId]['amount'] += $item->best_discount;
                    $discountAmount += $item->best_discount;
                }
            }
        }

        $appliedPromotions = array_values($appliedPromotionsMap);

        // 2. Check Coupon
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if ($coupon && $coupon->isValid()) {
                $couponDiscount = 0;

                if (!$allowStacking) {
                    // Non-Stackable logic:
                    // Bucket B: Calculate eligible subtotal from items without any product promo
                    $eligibleSubtotal = collect($itemsCollection)->where('discount_amount', 0)->sum('subtotal');

                    if ($eligibleSubtotal > 0) {
                        if ($coupon->type === 'percentage') {
                            $couponDiscount = $eligibleSubtotal * ($coupon->value / 100);
                        } else {
                            $couponDiscount = min($coupon->value, $eligibleSubtotal);
                        }

                        $discountAmount += $couponDiscount;
                        $appliedPromotions[] = [
                            'code' => $coupon->code,
                            'name' => 'Coupon: ' . $coupon->code . ' (Non-Promo Items)',
                            'amount' => $couponDiscount
                        ];
                    } else {
                        $appliedPromotions[] = [
                            'code' => $coupon->code,
                            'name' => 'Coupon: ' . $coupon->code . ' (Gagal: Semua barang promosi)',
                            'amount' => 0
                        ];
                    }
                } else {
                    // Stackable logic:
                    // Coupon applies to the Net Subtotal of ALL items
                    $baseForCoupon = $subtotal - $discountAmount;
                    if ($baseForCoupon > 0) {
                        if ($coupon->type === 'percentage') {
                            $couponDiscount = $baseForCoupon * ($coupon->value / 100);
                        } else {
                            $couponDiscount = min($coupon->value, $baseForCoupon);
                        }

                        $discountAmount += $couponDiscount;
                        $appliedPromotions[] = [
                            'code' => $coupon->code,
                            'name' => 'Coupon: ' . $coupon->code,
                            'amount' => $couponDiscount
                        ];
                    } else {
                        $appliedPromotions[] = [
                            'code' => $coupon->code,
                            'name' => 'Coupon: ' . $coupon->code . ' (Gagal: Nilai keranjang 0)',
                            'amount' => 0
                        ];
                    }
                }
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
                $arrayItem = (array) $item;
                $promoName = null;
                if ($arrayItem['discount_amount'] > 0) {
                    if (!empty($arrayItem['stack_discounts'])) {
                        $promoName = $arrayItem['stack_discounts'][0]['promo']->name ?? null;
                    } elseif (!empty($arrayItem['best_promo'])) {
                        $promoName = $arrayItem['best_promo']->name ?? null;
                    }
                } elseif (!empty($arrayItem['eligible_promos'])) {
                    $promoName = $arrayItem['eligible_promos'][0]->name ?? null;
                }
                $arrayItem['promo_name'] = $promoName;
                unset($arrayItem['best_promo'], $arrayItem['stack_discounts'], $arrayItem['eligible_promos']);
                return $arrayItem;
            })->toArray()
        ];
    }
}
