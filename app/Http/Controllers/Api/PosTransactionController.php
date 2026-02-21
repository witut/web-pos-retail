<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PosTransactionController extends Controller
{
    public function searchByInvoice(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string'
        ]);

        $transaction = Transaction::with(['items', 'returns.returnItems'])
            ->where('invoice_number', $request->invoice_number)
            ->where('status', 'completed')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau statusnya tidak valid untuk diretur.'
            ], 404);
        }

        // Format items with remaining returnable quantity
        $items = $transaction->items->map(function ($item) use ($transaction) {
            $returnedQty = 0;
            foreach ($transaction->returns as $ret) {
                // ReturnItems related to this specific transaction_item_id
                $returnItemsForThisTransactionItem = $ret->returnItems->where('transaction_item_id', $item->id);
                foreach ($returnItemsForThisTransactionItem as $match) {
                    $returnedQty += $match->quantity;
                }
            }

            $maxQty = max(0, $item->qty - $returnedQty);
            $netPrice = $item->qty > 0 ? ($item->subtotal / $item->qty) : 0;

            return [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'unit_price' => $item->unit_price,
                'net_price' => $netPrice,
                'max_qty' => $maxQty,
                'returned_qty' => $returnedQty,
                'original_qty' => $item->qty
            ];
        })->filter(function ($item) {
            return $item['max_qty'] > 0;
        })->values();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua barang pada transaksi ini sudah diretur.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'transaction_date' => $transaction->transaction_date->format('d/m/Y H:i'),
                'customer_name' => $transaction->customer ? $transaction->customer->name : 'Walk-in Customer',
                'subtotal' => $transaction->subtotal,
                'total' => $transaction->total,
                'items' => $items
            ]
        ]);
    }
}
