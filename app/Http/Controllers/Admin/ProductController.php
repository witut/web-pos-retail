<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'barcodes'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('sku', 'like', '%'.$request->search.'%')
                        ->orWhereHas('barcodes', function ($q) use ($request) {
                            $q->where('barcode', 'like', '%'.$request->search.'%');
                        });
                });
            })
            ->when($request->category_id, function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest();

        $products = $query->paginate(20);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        // dd($request->barcodes);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'product_type' => 'required|in:inventory,service',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_on_hand' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'barcodes' => 'nullable|array',
            'barcodes.*.code' => 'required|string|unique:product_barcodes,barcode',
            'units' => 'nullable|array',
            'units.*.name' => 'required|string',
            'units.*.conversion_rate' => 'required|numeric|min:0',
            'units.*.selling_price' => 'required|numeric|min:0',
            'units.*.barcode' => 'nullable|string|unique:product_units,barcode',
        ]);

        DB::beginTransaction();
        try {
            // Auto-generate SKU if not provided
            if (empty($validated['sku'])) {
                $validated['sku'] = $this->generateSKU($validated['category_id']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image_path'] = $request->file('image')->store('products', 'public');
            }

            $validated['created_by'] = auth()->id();

            // Create product
            $product = Product::create($validated);

            // Create barcodes
            if ($request->barcodes) {
                foreach ($request->barcodes as $barcodeData) {
                    ProductBarcode::create([
                        'product_id' => $product->id,
                        'barcode' => $barcodeData['code'],
                        'is_primary' => isset($barcodeData['is_primary']),
                    ]);
                }
            }

            // Create units
            if ($request->units) {
                foreach ($request->units as $unitData) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_name' => $unitData['name'],
                        'conversion_rate' => $unitData['conversion_rate'],
                        'selling_price' => $unitData['selling_price'],
                        'barcode' => $unitData['barcode'] ?? null,
                        'is_base_unit' => false,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan produk: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $product->load(['barcodes', 'units']);
        $categories = Category::all();

        // Prepare data for Alpine.js
        $barcodesData = $product->barcodes->map(function ($b) {
            return [
                'id' => $b->id,
                'code' => $b->barcode,
                'is_primary' => $b->is_primary,
            ];
        })->values();

        $unitsData = $product->units
            ->where('is_base_unit', false)   // Satuan dasar TIDAK ditampilkan di UOM — sudah ada di "Satuan Dasar"
            ->map(function ($u) {
                return [
                    'id'              => $u->id,
                    'name'            => $u->unit_name,
                    'conversion_rate' => $u->conversion_rate,
                    'selling_price'   => $u->selling_price,
                ];
            })->values();


        return view('admin.products.edit', compact('product', 'categories', 'barcodesData', 'unitsData'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:100|unique:products,sku,'.$product->id,
            'product_type' => 'required|in:inventory,service',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'barcodes' => 'nullable|array',
            'barcodes.*.code' => 'required|string',
            'units' => 'nullable|array',
            'units.*.barcode' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $validated['image_path'] = $request->file('image')->store('products', 'public');
            }

            // Update product
            $product->update($validated);

            // Update barcodes - delete all and recreate
            $product->barcodes()->delete();
            if ($request->barcodes) {
                foreach ($request->barcodes as $barcodeData) {
                    ProductBarcode::create([
                        'product_id' => $product->id,
                        'barcode' => $barcodeData['code'],
                        'is_primary' => isset($barcodeData['is_primary']),
                    ]);
                }
            }

            // Update units - delete all and recreate
            $product->units()->delete();
            if ($request->units) {
                foreach ($request->units as $unitData) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_name' => $unitData['name'],
                        'conversion_rate' => $unitData['conversion_rate'],
                        'selling_price' => $unitData['selling_price'],
                        'barcode' => $unitData['barcode'] ?? null,
                        'is_base_unit' => false,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate produk: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified product.
     *
     * Produk HANYA bisa dihapus permanen dari database apabila tidak terkait
     * dengan tabel transaksi, pergerakan stok, penerimaan, opname, atau retur.
     * Jika sudah terkait → produk dinonaktifkan (status=inactive) saja.
     *
     * Tabel yang CASCADE (ikut terhapus): product_barcodes, product_units, promotion_products.
     * Tabel yang RESTRICT (mencegah hapus): transaction_items, stock_movements,
     *   stock_receiving_items, stock_opname_items, product_return_items.
     */
    public function destroy(Product $product)
    {
        // ── Cek relasi yang mencegah hard-delete ─────────────────────────────
        $hasTransactions     = $product->transactionItems()->exists();
        $hasStockMovements   = $product->stockMovements()->exists();
        $hasReceivingItems   = $product->stockReceivingItems()->exists();
        $hasOpnameItems      = $product->stockOpnameItems()->exists();
        $hasReturnItems      = $product->returnItems()->exists();

        $hasRelatedData = $hasTransactions || $hasStockMovements
                       || $hasReceivingItems || $hasOpnameItems
                       || $hasReturnItems;

        if ($hasRelatedData) {
            // Tidak bisa dihapus → nonaktifkan saja
            $product->update(['status' => 'inactive']);

            $reasons = [];
            if ($hasTransactions)   $reasons[] = 'transaksi penjualan';
            if ($hasStockMovements) $reasons[] = 'pergerakan stok';
            if ($hasReceivingItems) $reasons[] = 'penerimaan barang';
            if ($hasOpnameItems)    $reasons[] = 'stock opname';
            if ($hasReturnItems)    $reasons[] = 'retur barang';

            return redirect()->route('admin.products.index')
                ->with('warning',
                    "Produk \"{$product->name}\" tidak dapat dihapus karena terkait dengan: "
                    . implode(', ', $reasons)
                    . ". Produk telah dinonaktifkan."
                );
        }

        // ── Tidak ada relasi → hard delete ───────────────────────────────────
        try {
            DB::beginTransaction();
            // product_barcodes & product_units will CASCADE automatically.
            // promotion_products uses nullOnDelete for reward_product_id.
            $product->delete();
            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', "Produk \"{$product->name}\" berhasil dihapus permanen dari database.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus produk: ' . $e->getMessage()]);
        }
    }

    /**
     * Print label barcode untuk satu produk
     */
    public function showPrintLabel(Product $product)
    {
        $product->load(['barcodes', 'units']);
        $products = collect([$product]);
        $quantities = [$product->id => 1];

        return view('admin.products.print_labels', compact('products', 'quantities'));
    }

    /**
     * Print label barcode untuk banyak produk (bulk)
     * POST: product_ids[] + quantities[product_id]
     */
    public function printLabels(Request $request)
    {
        $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'quantities'    => 'nullable|array',
        ]);

        $products = Product::with(['barcodes', 'units'])
            ->whereIn('id', $request->product_ids)
            ->get();

        // quantities: [product_id => qty], default 1
        $quantities = [];
        foreach ($products as $p) {
            $quantities[$p->id] = (int) ($request->input("quantities.{$p->id}", 1));
            if ($quantities[$p->id] < 1) $quantities[$p->id] = 1;
            if ($quantities[$p->id] > 100) $quantities[$p->id] = 100;
        }

        return view('admin.products.print_labels', compact('products', 'quantities'));
    }

    /**
     * Generate unique SKU
     */
    private function generateSKU($categoryId)
    {
        $category = Category::find($categoryId);
        $prefix = strtoupper(substr($category->name ?? 'PROD', 0, 3));

        // Get last product with this prefix
        $lastProduct = Product::where('sku', 'like', $prefix.'%')
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->sku, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.'-'.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
