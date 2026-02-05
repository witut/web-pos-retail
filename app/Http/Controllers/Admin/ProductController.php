<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductBarcode;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('sku', 'like', '%' . $request->search . '%')
                        ->orWhereHas('barcodes', function ($q) use ($request) {
                            $q->where('barcode', 'like', '%' . $request->search . '%');
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
            'sku' => 'nullable|string|max:20|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
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
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan produk: ' . $e->getMessage()]);
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

        $unitsData = $product->units->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->unit_name,
                'conversion_rate' => $u->conversion_rate,
                'selling_price' => $u->selling_price,
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
            'sku' => 'required|string|max:20|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'barcodes' => 'nullable|array',
            'barcodes.*.code' => 'required|string',
            'units' => 'nullable|array',
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
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate produk: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product (soft delete by setting inactive)
     */
    public function destroy(Product $product)
    {
        try {
            // Don't actually delete, just set to inactive
            $product->update(['status' => 'inactive']);

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil dinonaktifkan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus produk: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate unique SKU
     */
    private function generateSKU($categoryId)
    {
        $category = Category::find($categoryId);
        $prefix = strtoupper(substr($category->name ?? 'PROD', 0, 3));

        // Get last product with this prefix
        $lastProduct = Product::where('sku', 'like', $prefix . '%')
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->sku, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
