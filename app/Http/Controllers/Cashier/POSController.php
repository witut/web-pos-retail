<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\User;
use App\Services\TransactionService;
use App\Services\CustomerService;
use App\Services\SettingService;
use App\Services\PromotionService;
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
use App\Services\PrinterService;

class POSController extends Controller
{
    protected TransactionService $transactionService;
    protected CustomerService $customerService;
    protected SettingService $settingService;
    protected PromotionService $promotionService;
    protected PrinterService $printerService;

    public function __construct(
        TransactionService $transactionService,
        CustomerService $customerService,
        SettingService $settingService,
        PromotionService $promotionService,
        PrinterService $printerService
    ) {
        $this->transactionService = $transactionService;
        $this->customerService = $customerService;
        $this->settingService = $settingService;
        $this->promotionService = $promotionService;
        $this->printerService = $printerService;
    }

    /**
     * Display POS terminal screen
     * Main interface untuk cashier
     */
    public function index()
    {
        // Get tax settings
        $taxRate = Setting::getTaxRate();
        $taxType = Setting::get('tax_type', 'exclusive');

        // Get printer settings
        $printerSettings = [
            'type' => Setting::get('printer.type', 'browser'),
            'server_url' => Setting::get('printer.server_url', 'http://localhost:9100'),
            'paper_width' => Setting::get('printer.paper_width', '58'),
        ];

        // Get today's transactions (untuk history view)
        $todayTransactions = Transaction::today()
            ->where('cashier_id', auth()->id())
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        // Get customer settings
        $cashierCanCreate = $this->settingService->getBool('customer.cashier_can_create', true);

        // Check for active session
        $shiftService = app(\App\Services\ShiftService::class);
        $session = $shiftService->getCurrentSession(auth()->user());

        return view('cashier.pos.index', compact('taxRate', 'taxType', 'printerSettings', 'todayTransactions', 'cashierCanCreate', 'session'));
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

        // Check stock (skip for service products)
        if ($product->product_type === 'inventory' && $product->isOutOfStock()) {
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
                'product_type' => $product->product_type,
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
                    'base_unit' => $product->base_unit,
                    'units' => $product->activeUnits->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'name' => $unit->unit_name,
                            'conversion_rate' => $unit->conversion_rate,
                            'selling_price' => $unit->selling_price,
                        ];
                    }),
                ];
            });

        return response()->json($products);
    }

    /**
     * Calculate cart totals with promotions (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request)
    {
        $items = $request->input('items', []);
        $couponCode = $request->input('coupon_code');

        // Transform items for service
        $cartItems = collect($items)->map(function ($item) {
            return [
                'product_id' => $item['id'], // POS JS uses 'id', Service uses 'product_id'
                'qty' => $item['qty'],
                'price' => $item['price'] ?? 0, // POS JS uses 'price'
                'unit_name' => $item['unit'] ?? null,
            ];
        })->toArray();

        // Calculate discount
        $result = $this->promotionService->calculateCartDiscount($cartItems, $couponCode);

        // Calculate Tax
        $subtotalNet = $result['final_total']; // After discount
        $taxRate = Setting::getTaxRate();
        $taxType = Setting::get('tax_type', 'exclusive');

        $taxAmount = 0;
        $grandTotal = $subtotalNet;

        if ($taxType === 'inclusive') {
            // Tax included
            $taxAmount = $subtotalNet - ($subtotalNet / (1 + ($taxRate / 100)));
        } else {
            // Tax excluded
            $taxAmount = $subtotalNet * ($taxRate / 100);
            $grandTotal = $subtotalNet + $taxAmount;
        }

        return response()->json([
            'subtotal' => $result['subtotal'],
            'discount_amount' => $result['discount_amount'],
            'tax_amount' => round($taxAmount),
            'grand_total' => round($grandTotal),
            'promotions' => $result['applied_promotions'],
            'items' => $result['items'],
        ]);
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
            'customer_id' => 'nullable|exists:customers,id',
            'points_to_redeem' => 'nullable|integer|min:0',
            'coupon_code' => 'nullable|string',
        ]);

        try {
            // Prepare cart items for PromotionService
            $cartItemsForPromo = collect($validated['items'])->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'unit_name' => $item['unit_name'],
                ];
            })->toArray();

            // Calculate discount
            $couponCode = $validated['coupon_code'] ?? null;
            $promoResult = $this->promotionService->calculateCartDiscount($cartItemsForPromo, $couponCode);

            $discountAmount = $promoResult['discount_amount'];
            $promotionId = null;
            $couponId = null;
            $couponDiscountAmount = 0;

            if (!empty($promoResult['applied_promotions'])) {
                foreach ($promoResult['applied_promotions'] as $promo) {
                    if (isset($promo['code'])) {
                        // This is a coupon
                        $couponDiscountAmount += $promo['amount'];
                    } else {
                        // This is a promotion
                        $promotionId = $promo['id'] ?? null;
                    }
                }
            }

            if ($couponCode) {
                $coupon = \App\Models\Coupon::active()->where('code', $couponCode)->first();
                $couponId = $coupon ? $coupon->id : null;
            }

            // Prepare items for TransactionService
            // Use promoResult items because they contain the calculated discount_amount per item
            $cartItems = array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'unit_name' => $item['unit_name'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                ];
            }, $promoResult['items']);

            // Prepare payment data
            $paymentData = [
                'method' => $validated['payment']['method'],
                'amount_paid' => $validated['payment']['amount_paid'],
            ];

            // Handle customer and points
            $customerId = $validated['customer_id'] ?? null;
            $pointsToRedeem = $validated['points_to_redeem'] ?? 0;
            $pointsDiscount = 0;

            // Validate and process points redemption
            if ($customerId && $pointsToRedeem > 0) {
                $customer = Customer::findOrFail($customerId);

                if ($pointsToRedeem > $customer->points_balance) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Poin tidak mencukupi'
                    ], 400);
                }

                $pointsDiscount = $this->customerService->convertPointsToDiscount($pointsToRedeem);
            }

            // Process transaction via service
            $transaction = $this->transactionService->createTransaction(
                $cartItems,
                $paymentData,
                auth()->id(),
                $customerId,
                $pointsDiscount,
                $pointsToRedeem,
                $discountAmount,
                $promotionId,
                $couponId,
                $couponDiscountAmount
            );

            // Calculate and award points if customer selected
            if ($customerId) {
                $customer = Customer::find($customerId);

                // Redeem points if any
                if ($pointsToRedeem > 0) {
                    $this->customerService->redeemPoints($customer, $pointsToRedeem, $transaction);
                }

                // Award points for this purchase
                $this->customerService->earnPoints($customer, $transaction);

                // Update total spent
                $customer->increment('total_spent', $transaction->total);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'invoice_number' => $transaction->invoice_number,
                'transaction_id' => $transaction->id,
                'total' => $transaction->total,
                'print_payload' => collect($this->printerService->generatePrintPayload($transaction))->toArray(),
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
     * Print receipt or faktur
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
        $storeAddress = Setting::get('store_address', 'Jl. Contoh No. 123');
        $storePhone = Setting::get('store_phone', '08123456789');
        $receiptFooter = Setting::getReceiptFooter();
        $taxType = Setting::get('tax_type', 'exclusive');

        $paperWidth = Setting::get('printer.paper_width', '80');

        if ($paperWidth === 'faktur') {
            return view('cashier.pos.faktur', compact('transaction', 'storeName', 'storeAddress', 'storePhone', 'receiptFooter', 'taxType'));
        }

        return view('cashier.pos.receipt', compact('transaction', 'storeName', 'storeAddress', 'storePhone', 'receiptFooter', 'taxType'));
    }

    /**
     * Void transaction (requires Admin PIN)
     * 
     * @param Request $request
     * @param Transaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function void(Request $request, Transaction $transaction)
    {
        try {
            $validated = $request->validate([
                'admin_pin' => 'required|string|digits:6',
                'void_reason' => 'required|string|max:100',
                'void_notes' => 'nullable|string|max:500',
            ]);

            // Find admin with valid PIN - check all admins
            $admins = User::admins()->active()->get();
            $validAdmin = null;

            foreach ($admins as $admin) {
                if ($admin->hasPin() && $admin->verifyPin($validated['admin_pin'])) {
                    $validAdmin = $admin;
                    break;
                }
            }

            if (!$validAdmin) {
                return response()->json([
                    'success' => false,
                    'error' => 'PIN admin tidak valid'
                ], 422);
            }

            $voidedTransaction = $this->transactionService->voidTransaction(
                $transaction,
                $validated['admin_pin'],
                $validated['void_reason'],
                $validated['void_notes'] ?? null,
                $validAdmin->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil di-void',
                'transaction' => [
                    'invoice_number' => $voidedTransaction->invoice_number,
                    'status' => $voidedTransaction->status,
                    'voided_at' => $voidedTransaction->voided_at->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Verify Admin PIN (AJAX)
     * Used for quick PIN validation before void
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|digits:6'
        ]);

        $admins = User::admins()->active()->get();

        foreach ($admins as $admin) {
            if ($admin->hasPin() && $admin->verifyPin($request->pin)) {
                return response()->json([
                    'valid' => true,
                    'admin_name' => $admin->name
                ]);
            }
        }

        return response()->json([
            'valid' => false,
            'error' => 'PIN tidak valid'
        ], 422);
    }
}
