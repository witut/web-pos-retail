<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $updateCount = 0;
    public $errors = [];

    /**
     * Process each row from the Excel file
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index

            try {
                // Validate row
                $validator = Validator::make($row->toArray(), [
                    'sku' => 'required|string|max:20',
                    'nama_produk' => 'required|string|max:200',
                    'product_type' => 'nullable|in:inventory,service',
                    'kategori' => 'nullable|string|max:100',
                    'brand' => 'nullable|string|max:100',
                    'harga_pokok_hpp' => 'nullable|numeric|min:0',
                    'harga_jual' => 'required|numeric|min:0',
                    'stok_awal' => 'nullable|numeric|min:0',
                    'min_stock_alert' => 'nullable|numeric|min:0',
                    'unit_dasar' => 'nullable|string|max:20',
                    'barcode' => 'nullable|string|max:50',
                    'status' => 'nullable|in:active,inactive',
                    'deskripsi' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'sku' => $row['sku'] ?? '-',
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                // Additional validation: Selling price >= Cost price
                $costPrice = (float) ($row['harga_pokok_hpp'] ?? 0);
                $sellingPrice = (float) $row['harga_jual'];

                if ($costPrice > 0 && $sellingPrice < $costPrice) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'sku' => $row['sku'],
                        'errors' => ['Harga Jual harus lebih besar atau sama dengan Harga Pokok'],
                    ];
                    continue;
                }

                // Get or create category
                $category = null;
                if (!empty($row['kategori'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row['kategori']],
                        [
                            'name' => $row['kategori'],
                            'description' => 'Auto-created from import',
                            'created_by' => auth()->id(),
                        ]
                    );
                }

                // Prepare product data
                $productType = $row['product_type'] ?? 'inventory';
                $status = $row['status'] ?? 'active';
                $unit = $row['unit_dasar'] ?? 'PCS';

                $productData = [
                    'sku' => $row['sku'],
                    'name' => $row['nama_produk'],
                    'product_type' => $productType,
                    'category_id' => $category?->id,
                    'brand' => $row['brand'] ?? null,
                    'base_unit' => $unit,
                    'selling_price' => $sellingPrice,
                    'cost_price' => $costPrice,
                    'stock_on_hand' => $productType === 'service' ? 0 : ($row['stok_awal'] ?? 0),
                    'min_stock_alert' => $productType === 'service' ? null : ($row['min_stock_alert'] ?? null),
                    'status' => $status,
                    'description' => $row['deskripsi'] ?? null,
                    'created_by' => auth()->id(),
                ];

                // Check if product exists
                $existingProduct = Product::where('sku', $row['sku'])->first();

                if ($existingProduct) {
                    // Update existing product
                    $existingProduct->update($productData);
                    $this->updateCount++;
                } else {
                    // Create new product
                    Product::create($productData);
                    $this->successCount++;
                }

                // Handle barcode if provided
                if (!empty($row['barcode'])) {
                    $product = Product::where('sku', $row['sku'])->first();

                    // Check if barcode already exists for this product
                    $existingBarcode = $product->barcodes()->where('barcode', $row['barcode'])->first();

                    if (!$existingBarcode) {
                        $product->barcodes()->create([
                            'barcode' => $row['barcode'],
                            'is_primary' => $product->barcodes()->count() === 0, // First barcode is primary
                        ]);
                    }
                }

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'sku' => $row['sku'] ?? '-',
                    'errors' => [$e->getMessage()],
                ];
            }
        }
    }

    /**
     * Get import summary
     */
    public function getSummary(): array
    {
        return [
            'success' => $this->successCount,
            'updated' => $this->updateCount,
            'failed' => count($this->errors),
            'total' => $this->successCount + $this->updateCount + count($this->errors),
            'errors' => $this->errors,
        ];
    }
}
