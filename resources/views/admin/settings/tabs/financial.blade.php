<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Simbol Mata Uang</label>
        <input type="text" name="currency_symbol"
            value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'Rp') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (Tax Rate) % *</label>
        <div class="relative rounded-md shadow-sm sm:w-1/3">
            <input type="number" name="tax_rate" step="0.1" min="0" max="100"
                value="{{ old('tax_rate', $settings['tax_rate'] ?? 11) }}"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm pr-10"
                required>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">%</span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Persentase PPn yang dikenakan pada setiap transaksi.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pajak *</label>
        <div class="mt-2 space-y-2">
            <div class="flex items-center">
                <input id="tax_type_exclusive" name="tax_type" type="radio" value="exclusive"
                    {{ old('tax_type', $settings['tax_type'] ?? 'exclusive') == 'exclusive' ? 'checked' : '' }}
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="tax_type_exclusive" class="ml-3 block text-sm font-medium text-gray-700">
                    Exclusive (Harga Belum Termasuk Pajak)
                </label>
            </div>
            <p class="text-xs text-gray-500 ml-7">Harga Jual + PPN = Total Bayar</p>

            <div class="flex items-center">
                <input id="tax_type_inclusive" name="tax_type" type="radio" value="inclusive"
                    {{ old('tax_type', $settings['tax_type'] ?? 'exclusive') == 'inclusive' ? 'checked' : '' }}
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="tax_type_inclusive" class="ml-3 block text-sm font-medium text-gray-700">
                    Inclusive (Harga Sudah Termasuk Pajak)
                </label>
            </div>
            <p class="text-xs text-gray-500 ml-7">Harga Jual = Total Bayar (PPN dihitung dari harga jual)</p>
        </div>
    </div>
</div>
