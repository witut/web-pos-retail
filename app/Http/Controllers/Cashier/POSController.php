<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Setting;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * POSController
 * 
 * Controller untuk POS Terminal (Cashier Interface)
 * 
 * PENTING:
 * - Pre-Checkout: Cart hanya di session, kasir bebas edit/delete
 * - Post-Checkout: Stock deduction via TransactionService (atomic)
 * - Void requires Admin PIN via TransactionService
 */
class POSController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display POS terminal screen
     * Main interface untuk cashier
     */
    public function index()
    {
        // Get tax rate dari settings
        $taxRate = Setting::getTaxRate();

        // Get today's transactions (untuk history view)
        $todayTransactions = Transaction::today()
            ->where('cashier_id', auth()->id())
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        return view('cashier.pos.index', compact('taxRate', 'todayTransactions'));
    }

    /**
     * Search product by barcode or SKU (AJAX)
     * Digunakan oleh barcode scanner dan search form
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProduct(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['error' => 'Query kosong'], 400);
        }

        // Search by barcode first
        $product = Product::active()
            ->whereHas('barcodes', function ($q) use ($query) {
                $q->where('barcode', $query);
            })
            ->with(['barcodes', 'units', 'category'])
            ->first();

        // If not found, search by SKU
        if (!$product) {
            $product = Product::active()
                ->where('sku', $query)
                ->with(['barcodes', 'units', 'category'])
                ->first();
        }

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        // Check stock
        if ($product->isOutOfStock()) {
            return response()->json([
                'error' => 'Produk out of stock',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock_on_hand,
                ]
            ], 400);
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category->name ?? '-',
                'selling_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'stock_on_hand' => $product->stock_on_hand,
                'base_unit' => $product->base_unit,
                'image_url' => $product->getImageUrl(),
                'units' => $product->activeUnits->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->unit_name,
                        'conversion_rate' => $unit->conversion_rate,
                        'selling_price' => $unit->selling_price,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Autocomplete search untuk product name (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::active()
            ->search($query)
            ->with('category')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category->name ?? '-',
                    'price' => $product->selling_price,
                    'stock' => $product->stock_on_hand,
                ];
            });

        return response()->json($products);
    }

    /**
     * Process checkout (Create transaction)
     * Uses TransactionService for atomic checkout
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit_name' => 'required|string|max:20',
            'payment.method' => 'required|in:cash,card,qris,transfer',
            'payment.amount_paid' => 'required|numeric|min:0',
        ]);

        try {
            // Prepare cart items for TransactionService
            $cartItems = collect($validated['items'])->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'unit_name' => $item['unit_name'],
                ];
            })->toArray();

            // Prepare payment data
            $paymentData = [
                'method' => $validated['payment']['method'],
                'amount_paid' => $validated['payment']['amount_paid'],
            ];

            // Process transaction via service
            $transaction = $this->transactionService->createTransaction(
                $cartItems,
                $paymentData,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'invoice_number' => $transaction->invoice_number,
                'transaction_id' => $transaction->id,
                'total' => $transaction->total,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show transaction history
     */
    public function history()
    {
        $transactions = Transaction::where('cashier_id', auth()->id())
            ->with('items')
            ->latest()
            ->paginate(20);

        return view('cashier.pos.history', compact('transactions'));
    }

    /**
     * Show transaction detail (for reprint or void)
     * 
     * @param Transaction $transaction
     */
    public function show(Transaction $transaction)
    {
        // Ensure cashier can only view their own transactions
        // Admin can view all
        if (!auth()->user()->isAdmin() && $transaction->cashier_id != auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini');
        }

        $transaction->load('items.product', 'cashier');

        return view('cashier.pos.detail', compact('transaction'));
    }

    /**
     * Print receipt
     * 
     * @param Transaction $transaction
     */
    public function print(Transaction $transaction)
    {
        // Ensure cashier can only print their own transactions
        if (!auth()->user()->isAdmin() && $transaction->cashier_id != auth()->id()) {
            abort(403);
        }

        $transaction->load('items.product', 'cashier');
        $storeName = Setting::getStoreName();
        $receiptFooter = Setting::getReceiptFooter();

        return view('cashier.pos.receipt', compact('transaction', 'storeName', 'receiptFooter'));
    }

    /**
     * Void transaction (requires Admin PIN)
     * Will be implemented with TransactionService
     * 
     * @param Request $request
     * @param Transaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function void(Request $request, Transaction $transaction)
    {
        // TODO: Implement with TransactionService
        // - Verify Admin PIN
        // - Check void time limit
        // - Restore stock
        // - Create audit log

        return response()->json([
            'message' => 'Void akan diimplementasikan dengan TransactionService',
            'status' => 'pending'
        ]);
    }
}
