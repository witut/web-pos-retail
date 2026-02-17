<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promotions = Promotion::withCount('products')->latest()->paginate(10);
        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $products = Product::active()->get();

        // Check if deadstock product ID passed
        $deadStockProductId = $request->query('dead_stock_product_id');
        $selectedProduct = null;
        if ($deadStockProductId) {
            $selectedProduct = Product::find($deadStockProductId);
        }

        return view('admin.promotions.create', compact('products', 'selectedProduct'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y,bundle',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $promotion = Promotion::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'type' => $validated['type'],
                    'value' => $validated['value'],
                    'min_purchase' => $validated['min_purchase'] ?? 0,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'is_active' => $validated['is_active'] ?? true,
                    'created_by' => auth()->id(),
                ]);

                if (!empty($validated['product_ids'])) {
                    $promotion->products()->attach($validated['product_ids']);
                }
            });

            return redirect()->route('admin.promotions.index')
                ->with('success', 'Promosi berhasil dibuat');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat promosi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        $promotion->load('products');
        $products = Product::active()->get();
        return view('admin.promotions.edit', compact('promotion', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y,bundle',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $promotion, $validated) {
                $promotion->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'type' => $validated['type'],
                    'value' => $validated['value'],
                    'min_purchase' => $validated['min_purchase'] ?? 0,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                if (isset($validated['product_ids'])) {
                    $promotion->products()->sync($validated['product_ids']);
                } else {
                    $promotion->products()->detach();
                }
            });

            return redirect()->route('admin.promotions.index')
                ->with('success', 'Promosi berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui promosi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        try {
            $promotion->delete();
            return redirect()->route('admin.promotions.index')
                ->with('success', 'Promosi berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus promosi: ' . $e->getMessage());
        }
    }
}
