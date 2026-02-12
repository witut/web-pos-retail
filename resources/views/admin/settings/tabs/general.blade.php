<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko *</label>
        <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? '') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
            required>
        <p class="text-xs text-gray-500 mt-1">Nama ini akan dicetak di bagian atas struk belanja.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Toko</label>
        <textarea name="store_address" rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
        <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
        <input type="text" name="receipt_footer" value="{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
        <p class="text-xs text-gray-500 mt-1">Pesan singkat di bagian bawah struk (contoh: Terima Kasih, Barang tidak
            dapat dikembalikan).</p>
    </div>
</div>