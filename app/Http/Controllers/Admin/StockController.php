<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockReceiving;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display list of stock receiving
     */
    public function receiving(Request $request)
    {
        $query = StockReceiving::with(['supplier', 'creator', 'items'])
            ->orderBy('receiving_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receiving_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('receiving_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('receiving_date', '<=', $request->end_date);
        }

        $receivings = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.stock.receiving.index', compact('receivings', 'suppliers'));
    }

    /**
     * Show form for creating new stock receiving
     */
    public function createReceiving()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()
            ->with('barcodes')
            ->orderBy('name')
            ->get();

        // Generate receiving number preview
        $receivingNumber = $this->stockService->generateReceivingNumber();

        return view('admin.stock.receiving.create', compact('suppliers', 'products', 'receivingNumber'));
    }

    /**
     * Store new stock receiving
     */
    public function storeReceiving(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:50',
            'receiving_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_name' => 'required|string|max:20',
            'items.*.cost_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            // Prepare items array for StockService
            $items = collect($validated['items'])->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_name' => $item['unit_name'],
                    'cost_per_unit' => $item['cost_per_unit'],
                ];
            })->toArray();

            // Prepare metadata
            $metadata = [
                'invoice_number' => $validated['invoice_number'],
                'receiving_date' => $validated['receiving_date'],
                'notes' => $validated['notes'],
            ];

            // Process using StockService
            $receiving = $this->stockService->receiveStock(
                $validated['supplier_id'],
                $items,
                $metadata,
                auth()->id()
            );

            return redirect()
                ->route('admin.stock.receiving.show', $receiving)
                ->with('success', 'Penerimaan stok berhasil disimpan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan penerimaan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show detail of stock receiving
     */
    public function showReceiving(StockReceiving $receiving)
    {
        $receiving->load(['supplier', 'creator', 'items.product']);

        return view('admin.stock.receiving.show', compact('receiving'));
    }

    /**
     * Display stock movements (kartu stok)
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $movements = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('admin.stock.movements.index', compact('movements', 'products'));
    }

    /**
     * Stock Opname (placeholder for future)
     */
    public function opname()
    {
        return view('admin.stock.opname.index');
    }
}
