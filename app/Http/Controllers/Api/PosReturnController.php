<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class PosReturnController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function storeWithPin(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'admin_pin' => 'required|string|digits:6',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:transaction_items,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|in:good,damaged',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'refund_method' => 'required|in:cash,store_credit'
        ]);

        try {
            // 1. Verify PIN
            $admins = User::admins()->active()->get();
            $validAdmin = null;

            /** @var \App\Models\User $admin */
            foreach ($admins as $admin) {
                if ($admin->hasPin() && $admin->verifyPin($validated['admin_pin'])) {
                    $validAdmin = $admin;
                    break;
                }
            }

            if (!$validAdmin) {
                return response()->json([
                    'success' => false,
                    'error' => 'PIN Supervisor/Admin tidak valid!'
                ], 422);
            }

            // FILTER OUT ZERO QTY ITEMS
            $itemsToReturn = array_filter($validated['items'], function ($item) {
                return (float) $item['qty'] > 0;
            });

            if (empty($itemsToReturn)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tidak ada barang yang dipilih untuk diretur.'
                ], 422);
            }

            // 2. Process Return via TransactionService
            $productReturn = $this->transactionService->processReturn(
                $transaction,
                $itemsToReturn,
                $validated['reason'],
                $validated['notes'] ?? '',
                $validated['refund_method'],
                auth()->id() // The cashier initiating the return
            );

            // 3. Attach Authorizer ID to the return record
            $productReturn->update([
                'authorized_by_id' => $validAdmin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Retur transaksi berhasil diproses dan disetujui oleh ' . $validAdmin->name,
                'return_number' => $productReturn->return_number
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
