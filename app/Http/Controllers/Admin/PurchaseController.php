<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Exception;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'creator'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Simple search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Show form for creating a new purchase
     */
    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()
            ->with(['barcodes', 'units'])
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'base_unit', 'tracking_type', 'cost_price']);

        $purchaseNumber = $this->purchaseService->generatePurchaseNumber();

        return view('admin.purchases.create', compact('suppliers', 'products', 'purchaseNumber'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:purchase_date',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_name' => 'required|string|max:20',
            'items.*.cost_per_unit' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.serials' => 'nullable|string',
        ]);

        try {
            $purchase = $this->purchaseService->processPurchase($validated, auth()->id());

            return redirect()
                ->route('admin.purchases.index')
                ->with('success', "Pembelian {$purchase->purchase_number} berhasil disimpan!");
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan pembelian: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'items.product']);
        return view('admin.purchases.show', compact('purchase'));
    }
}
