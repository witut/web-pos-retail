<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Default Low Stock Threshold *</label>
        <input type="number" name="low_stock_threshold" min="0"
            value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3"
            required>
        <p class="text-xs text-gray-500 mt-1">Produk dianggap "Stok Menipis" jika jumlahnya di bawah angka ini (kecuali
            diatur spesifik per produk).</p>
    </div>
</div>