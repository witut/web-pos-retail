<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Setting;

class PrinterService
{
    /**
     * Generate structured JSON payload for ESC/POS Print Server
     */
    public function generatePrintPayload(Transaction $transaction)
    {
        $transaction->load('items.product', 'cashier', 'customer');
        
        return [
            'store' => [
                'name' => Setting::getStoreName(),
                'address' => Setting::get('store_address', 'Jl. Contoh No. 123'),
                'phone' => Setting::get('store_phone', '08123456789'),
            ],
            'receipt' => [
                'invoice_number' => $transaction->invoice_number,
                'date' => $transaction->created_at->format('d/m/Y H:i:s'),
                'cashier' => $transaction->cashier->name ?? 'Admin',
                'customer' => $transaction->customer->name ?? null,
                'header' => Setting::get('receipt_header', ''),
                'footer' => Setting::getReceiptFooter(),
            ],
            'items' => $transaction->items->map(function ($item) {
                return [
                    'name' => current(explode('|', $item->product_name)),
                    'qty' => (float) $item->quantity,
                    'price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => (float) $transaction->subtotal,
                'global_discount' => (float) ($transaction->discount_amount + $transaction->points_discount_amount + $transaction->coupon_discount_amount),
                'tax' => (float) $transaction->tax_amount,
                'grand_total' => (float) $transaction->total,
            ],
            'payment' => [
                'method' => strtoupper($transaction->payment_method),
                'amount_paid' => (float) $transaction->amount_paid,
                'change' => (float) ($transaction->amount_paid - $transaction->total),
            ],
            'settings' => [
                'paper_width' => Setting::get('printer.paper_width', '58'),
                'auto_cut' => (bool) Setting::get('printer.auto_cut', '1'),
                'open_drawer' => (bool) Setting::get('printer.open_drawer', '1'),
            ]
        ];
    }
}
