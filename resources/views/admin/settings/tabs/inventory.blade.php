<div class="grid grid-cols-1 gap-6 max-w-3xl">
    {{-- Low Stock Threshold --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <label class="block text-sm font-semibold text-gray-800 mb-1">Batas Stok Menipis (Low Stock Alert)</label>
        <div class="flex items-center gap-3 mt-2">
            <input type="number" name="low_stock_threshold" min="0"
                value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}"
                class="block rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 text-sm w-28"
                required>
            <span class="text-sm text-gray-500">unit</span>
        </div>
        <p class="text-xs text-gray-500 mt-2">Produk dianggap "Stok Menipis" jika jumlahnya di bawah angka ini (kecuali diatur spesifik per produk).</p>
    </div>

    {{-- Allow Negative Stock --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm" x-data="{ allowNegative: {{ ($settings['allow_negative_stock'] ?? '0') == '1' ? 'true' : 'false' }} }">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-800">Izinkan Penjualan Stok Minus</h4>
                <p class="text-xs text-gray-500 mt-1">
                    Jika <strong>diaktifkan</strong>, kasir dapat menjual produk meskipun stok on-hand sudah <strong>0 atau minus</strong>.
                    Berguna saat stok fisik tersedia namun belum diinput ke sistem.
                </p>
                <div x-show="allowNegative" x-transition class="mt-3 flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs text-amber-700 font-medium">Perhatian: Stok pada-hand bisa bernilai minus. Pastikan stok diperbarui secara berkala untuk menghindari perbedaan dengan stok fisik.</p>
                </div>
            </div>
            <div class="flex-shrink-0 pt-0.5">
                {{-- Hidden input to send '0' when unchecked --}}
                <input type="hidden" name="allow_negative_stock" value="0">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="allow_negative_stock" value="1"
                        x-model="allowNegative"
                        {{ ($settings['allow_negative_stock'] ?? '0') == '1' ? 'checked' : '' }}
                        class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-700"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- C.1: Jenis Produk yang Dijual --}}
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <h4 class="text-sm font-semibold text-gray-800 mb-1">Jenis Produk yang Dijual Toko Ini</h4>
        <p class="text-xs text-gray-500 mb-4">Pilih jenis produk yang relevan. Pengaturan ini mempengaruhi opsi yang tersedia pada form tambah produk.</p>

        <div class="space-y-3">
            {{-- Regular --}}
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="hidden" name="product_type_regular" value="0">
                <input type="checkbox" name="product_type_regular" value="1"
                    {{ ($settings['product_type_regular'] ?? '1') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 mt-0.5 rounded text-slate-700 border-gray-300 focus:ring-slate-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">🛒 Produk Regular (Stok Biasa)</p>
                    <p class="text-xs text-gray-500 mt-0.5">Produk dengan pelacakan stok sederhana tanpa batch atau nomor seri. Cocok untuk: Minimarket, Toko Kelontong.</p>
                </div>
            </label>

            {{-- Batch / Expired Date --}}
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="hidden" name="product_type_batch" value="0">
                <input type="checkbox" name="product_type_batch" value="1"
                    {{ ($settings['product_type_batch'] ?? '0') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 mt-0.5 rounded text-slate-700 border-gray-300 focus:ring-slate-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">💊 Produk Batch & Expired Date</p>
                    <p class="text-xs text-gray-500 mt-0.5">Produk dengan nomor batch dan tanggal kadaluwarsa. Cocok untuk: Toko Obat (Apotek), Toko Roti/Bakery, Produk Makanan.</p>
                </div>
            </label>

            {{-- Serial Number --}}
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="hidden" name="product_type_serial" value="0">
                <input type="checkbox" name="product_type_serial" value="1"
                    {{ ($settings['product_type_serial'] ?? '0') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 mt-0.5 rounded text-slate-700 border-gray-300 focus:ring-slate-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">💻 Produk Serial Number (Elektronik)</p>
                    <p class="text-xs text-gray-500 mt-0.5">Setiap unit produk memiliki nomor seri unik. Cocok untuk: Toko Komputer, Toko HP, Toko Elektronik.</p>
                </div>
            </label>

            {{-- Service --}}
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="hidden" name="product_type_service" value="0">
                <input type="checkbox" name="product_type_service" value="1"
                    {{ ($settings['product_type_service'] ?? '0') == '1' ? 'checked' : '' }}
                    class="w-4 h-4 mt-0.5 rounded text-slate-700 border-gray-300 focus:ring-slate-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">🔧 Produk Jasa / Service</p>
                    <p class="text-xs text-gray-500 mt-0.5">Layanan non-fisik yang tidak memotong stok. Cocok untuk: Toko Komputer (servis), Laundry, Salon.</p>
                </div>
            </label>
        </div>

        <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-blue-700">Minimal satu jenis produk harus aktif. Opsi "Tipe Pelacakan" pada form produk hanya akan menampilkan jenis yang dipilih di sini.</p>
        </div>
    </div>
</div>