<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function store(Request $request, Transaction $transaction)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|in:good,damaged',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'refund_method' => 'required|in:cash,store_credit'
        ]);

        try {
            // Filter out items with 0 qty
            $itemsToReturn = [];
            foreach ($request->items as $id => $data) {
                if ($data['qty'] > 0) {
                    $itemsToReturn[] = [
                        'id' => $id,
                        'qty' => $data['qty'],
                        'condition' => $data['condition']
                    ];
                }
            }

            if (empty($itemsToReturn)) {
                return back()->with('error', 'Minimal 1 item harus diatur kuantitas return-nya > 0.');
            }

            $this->transactionService->processReturn(
                $transaction,
                $itemsToReturn,
                $request->reason,
                $request->notes,
                $request->refund_method,
                auth()->id()
            );

            return back()->with('success', 'Retur berhasil diproses.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }
    }
}
