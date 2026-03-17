<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * ProductImport
 *
 * Template kolom (14 kolom):
 * SKU | Nama Produk | Product Type | Kategori | Brand |
 * Harga Pokok (HPP) | Harga Jual | Stok Awal | Min Stock Alert |
 * Konversi | Unit Dasar | Barcode | Status | Deskripsi
 *
 * Logika multi-UOM:
 * - Baris dengan Konversi = 1 (atau kosong)  → baris INDUK produk
 * - Baris dengan Konversi > 1 & SKU sama     → baris UOM TAMBAHAN
 *
 * Aturan duplikat (best practice POS):
 * - DUPLIKAT jika unit_name DAN conversion_rate KEDUANYA sudah identik → skip + report
 * - KONFLIK  jika unit_name sama tapi conversion_rate beda, atau sebaliknya → error + report
 */
class ProductImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;
    public int $updateCount  = 0;
    public int $uomAddedCount = 0;
    public array $errors = [];
    /** Menyimpan data baris asli yang gagal (beserta keterangan error) untuk download Excel */
    public array $failedRows = [];

    /**
     * Process each row from the Excel file
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2: header row + 0-based index

            try {
                // ── 1. Validate required fields ─────────────────────────────
                $validator = Validator::make($row->toArray(), [
                    'sku'              => 'required|string|max:100',
                    'nama_produk'      => 'required|string|max:200',
                    'product_type'     => 'nullable|in:inventory,service',
                    'kategori'         => 'nullable|string|max:100',
                    'brand'            => 'nullable|string|max:100',
                    'harga_pokok_hpp'  => 'nullable|numeric|min:0',
                    'harga_jual'       => 'required|numeric|min:0',
                    'stok_awal'        => 'nullable|numeric|min:0',
                    'min_stock_alert'  => 'nullable|numeric|min:0',
                    'konversi'         => 'nullable|numeric|min:1',
                    'unit_dasar'       => 'required|string|max:20',
                    'barcode'          => 'nullable|string|max:100',
                    'status'           => 'nullable|in:active,inactive',
                    'deskripsi'        => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->errors()->all();
                    $this->errors[] = [
                        'row'    => $rowNumber,
                        'sku'    => $row['sku'] ?? '-',
                        'errors' => $messages,
                    ];
                    $this->_captureFailedRow($row, $rowNumber, $messages);
                    continue;
                }

                // ── 2. Parse values ──────────────────────────────────────────
                $sku          = trim((string) $row['sku']);
                $konversi     = (float) ($row['konversi'] ?? 1);
                $unitDasar    = strtoupper(trim($row['unit_dasar']));
                $sellingPrice = (float) $row['harga_jual'];
                $costPrice    = (float) ($row['harga_pokok_hpp'] ?? 0);
                $productType  = $row['product_type'] ?? 'inventory';
                $status       = $row['status'] ?? 'active';
                $barcode      = !empty($row['barcode']) ? trim((string) $row['barcode']) : null;

                // Selling price >= cost price guard
                if ($costPrice > 0 && $sellingPrice < $costPrice) {
                    $messages = ['Harga Jual harus lebih besar atau sama dengan Harga Pokok'];
                    $this->errors[] = [
                        'row'    => $rowNumber,
                        'sku'    => $sku,
                        'errors' => $messages,
                    ];
                    $this->_captureFailedRow($row, $rowNumber, $messages);
                    continue;
                }

                // ── 3. Get or create category ────────────────────────────────
                $category = null;
                if (!empty($row['kategori'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row['kategori']],
                        [
                            'name'        => $row['kategori'],
                            'description' => 'Auto-created from import',
                            'created_by'  => auth()->id(),
                        ]
                    );
                }

                // ── 4. Determine row type ────────────────────────────────────
                $isBaseRow = ($konversi <= 1);   // konversi=1 or empty → induk/base row
                $isUomRow  = !$isBaseRow;         // konversi>1 → UOM tambahan

                // ── 5. Look up existing product ──────────────────────────────
                $product = Product::where('sku', $sku)->first();

                if (!$product) {
                    // ── KASUS A: Produk BARU ─────────────────────────────────
                    $productData = [
                        'sku'            => $sku,
                        'name'           => $row['nama_produk'],
                        'product_type'   => $productType,
                        'category_id'    => $category?->id,
                        'brand'          => $row['brand'] ?? null,
                        'base_unit'      => $unitDasar,
                        'selling_price'  => $sellingPrice,
                        'cost_price'     => $costPrice,
                        'stock_on_hand'  => $productType === 'service' ? 0 : (float) ($row['stok_awal'] ?? 0),
                        'min_stock_alert'=> $productType === 'service' ? null : ($row['min_stock_alert'] ?? null),
                        'status'         => $status,
                        'description'    => $row['deskripsi'] ?? null,
                        'created_by'     => auth()->id(),
                    ];

                    $product = Product::create($productData);
                    $this->successCount++;

                    // Create the base ProductUnit entry (konversi=1, is_base_unit=true)
                    // Use base unit name from the current row (should be PCS/etc.)
                    $baseUnitName = $isBaseRow ? $unitDasar : $product->base_unit;
                    ProductUnit::firstOrCreate(
                        ['product_id' => $product->id, 'is_base_unit' => true],
                        [
                            'product_id'      => $product->id,
                            'unit_name'       => $baseUnitName,
                            'conversion_rate' => 1,
                            'selling_price'   => $isBaseRow ? $sellingPrice : $product->selling_price,
                            'is_base_unit'    => true,
                            'is_active'       => true,
                        ]
                    );

                    // Handle barcode
                    if ($barcode) {
                        $product->barcodes()->create([
                            'barcode'    => $barcode,
                            'is_primary' => true,
                        ]);
                    }

                    // If this new product row already defines a UOM (konversi > 1),
                    // add it immediately after creating the product
                    if ($isUomRow) {
                        $this->addUomUnit($product, $unitDasar, $konversi, $sellingPrice, $barcode, $rowNumber, $sku);
                    }

                } else {
                    // ── KASUS B: Produk SUDAH ADA ────────────────────────────

                    if ($isBaseRow) {
                        // B-1: Baris induk → update data produk
                        $product->update([
                            'name'            => $row['nama_produk'],
                            'product_type'    => $productType,
                            'category_id'     => $category?->id ?? $product->category_id,
                            'brand'           => $row['brand'] ?? $product->brand,
                            'base_unit'       => $unitDasar,
                            'selling_price'   => $sellingPrice,
                            'cost_price'      => $costPrice ?: $product->cost_price,
                            'min_stock_alert' => $row['min_stock_alert'] ?? $product->min_stock_alert,
                            'status'          => $status,
                            'description'     => $row['deskripsi'] ?? $product->description,
                        ]);
                        $this->updateCount++;

                        // Update the base ProductUnit selling price too
                        $product->units()->where('is_base_unit', true)->update([
                            'unit_name'     => $unitDasar,
                            'selling_price' => $sellingPrice,
                        ]);

                        // Handle barcode
                        if ($barcode && !$product->barcodes()->where('barcode', $barcode)->exists()) {
                            $isPrimary = $product->barcodes()->count() === 0;
                            $product->barcodes()->create([
                                'barcode'    => $barcode,
                                'is_primary' => $isPrimary,
                            ]);
                        }
                    } else {
                        // B-2: Baris UOM tambahan → add/validate ProductUnit
                        $added = $this->addUomUnit($product, $unitDasar, $konversi, $sellingPrice, $barcode, $rowNumber, $sku);
                        // addUomUnit handles its own count/error tracking
                    }
                }

            } catch (\Exception $e) {
                $messages = [$e->getMessage()];
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'sku'    => $row['sku'] ?? '-',
                    'errors' => $messages,
                ];
                $this->_captureFailedRow($row, $rowNumber, $messages);
            }
        }
    }

    /**
     * Capture a failed row (original data + error messages) for later download.
     */
    private function _captureFailedRow($row, int $rowNumber, array $messages): void
    {
        $this->failedRows[] = [
            'baris'             => $rowNumber,
            'sku'               => $row['sku'] ?? '',
            'nama_produk'       => $row['nama_produk'] ?? '',
            'product_type'      => $row['product_type'] ?? '',
            'kategori'          => $row['kategori'] ?? '',
            'brand'             => $row['brand'] ?? '',
            'harga_pokok_hpp'   => $row['harga_pokok_hpp'] ?? '',
            'harga_jual'        => $row['harga_jual'] ?? '',
            'stok_awal'         => $row['stok_awal'] ?? '',
            'min_stock_alert'   => $row['min_stock_alert'] ?? '',
            'konversi'          => $row['konversi'] ?? '',
            'unit_dasar'        => $row['unit_dasar'] ?? '',
            'barcode'           => $row['barcode'] ?? '',
            'status'            => $row['status'] ?? '',
            'deskripsi'         => $row['deskripsi'] ?? '',
            'keterangan_error'  => implode('; ', $messages),
        ];
    }

    /**
     * Add a ProductUnit (UOM tambahan) to an existing product.
     * Handles duplicate / conflict detection.
     *
     * Returns true if unit was added, false if skipped/errored.
     */
    private function addUomUnit(
        Product $product,
        string  $unitName,
        float   $konversi,
        float   $sellingPrice,
        ?string $barcode,
        int     $rowNumber,
        string  $sku
    ): bool {
        // Guard: konversi=1 adalah satuan DASAR — sudah tersimpan di field base_unit produk,
        // tidak perlu dan tidak boleh ditambahkan ke tabel product_units sebagai UOM terpisah.
        if ($konversi <= 1) {
            return false;
        }

        $existingByName = $product->units()->where('unit_name', $unitName)->first();
        $existingByRate = $product->units()
            ->where('conversion_rate', $konversi)
            ->where('is_base_unit', false)
            ->first();


        if ($existingByName && abs((float) $existingByName->conversion_rate - $konversi) < 0.001) {
            // DUPLIKAT IDENTIK → skip
            $this->errors[] = [
                'row'    => $rowNumber,
                'sku'    => $sku,
                'errors' => ["Data duplikat: Unit [{$unitName}] dengan konversi [{$konversi}] sudah ada untuk produk ini, baris dilewati."],
            ];
            return false;
        }

        if ($existingByName && abs((float) $existingByName->conversion_rate - $konversi) >= 0.001) {
            // KONFLIK: nama sama, konversi beda
            $this->errors[] = [
                'row'    => $rowNumber,
                'sku'    => $sku,
                'errors' => ["Konflik unit: Unit [{$unitName}] sudah terdaftar dengan konversi berbeda ({$existingByName->conversion_rate}). Harap edit manual."],
            ];
            return false;
        }

        if ($existingByRate && $existingByRate->unit_name !== $unitName) {
            // KONFLIK: konversi sama, nama beda
            $this->errors[] = [
                'row'    => $rowNumber,
                'sku'    => $sku,
                'errors' => ["Konflik unit: Konversi [{$konversi}] sudah dipakai oleh unit [{$existingByRate->unit_name}]. Harap edit manual."],
            ];
            return false;
        }

        // AMAN: tambahkan unit baru
        ProductUnit::create([
            'product_id'      => $product->id,
            'unit_name'       => $unitName,
            'conversion_rate' => $konversi,
            'selling_price'   => $sellingPrice,
            'is_base_unit'    => false,
            'is_active'       => true,
        ]);

        $this->uomAddedCount++;
        return true;
    }

    /**
     * Get import summary
     */
    public function getSummary(): array
    {
        return [
            'success'    => $this->successCount,
            'updated'    => $this->updateCount,
            'uom_added'  => $this->uomAddedCount,
            'failed'     => count($this->errors),
            'total'      => $this->successCount + $this->updateCount + $this->uomAddedCount + count($this->errors),
            'errors'     => $this->errors,
        ];
    }
}
