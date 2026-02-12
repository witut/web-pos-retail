<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Pengaturan sistem diskon dan promosi. Fitur ini akan aktif setelah modul Discount Management diimplementasikan.
                </p>
            </div>
        </div>
    </div>

    <div>
        <h4 class="font-medium text-gray-800 mb-3">Diskon Manual (Kasir)</h4>
        
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="discount.cashier_manual_allowed" value="1"
                    {{ old('discount.cashier_manual_allowed', $settings['discount.cashier_manual_allowed'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Izinkan kasir input diskon manual</span>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Diskon (%) Tanpa Approval</label>
                <input type="number" name="discount.cashier_max_percent" min="0" max="100"
                    value="{{ old('discount.cashier_max_percent', $settings['discount.cashier_max_percent'] ?? '10') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                <p class="text-xs text-gray-500 mt-1">Lebih dari ini butuh PIN admin</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Diskon (Rp) Tanpa Approval</label>
                <input type="number" name="discount.cashier_max_amount" min="0"
                    value="{{ old('discount.cashier_max_amount', $settings['discount.cashier_max_amount'] ?? '50000') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                <p class="text-xs text-gray-500 mt-1">Lebih dari ini butuh PIN admin</p>
            </div>
        </div>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-3">Kebijakan Diskon</h4>
        
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="discount.allow_stacking" value="1"
                    {{ old('discount.allow_stacking', $settings['discount.allow_stacking'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Izinkan diskon bertumpuk</span>
            </label>
            <p class="text-xs text-gray-500 mt-1 ml-6">Diskon produk + voucher + poin bisa digunakan bersamaan</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pembulatan Diskon</label>
            <div class="space-y-2">
                <div class="flex items-center">
                    <input id="rounding_down" name="discount.rounding" type="radio" value="down"
                        {{ old('discount.rounding', $settings['discount.rounding'] ?? 'down') == 'down' ? 'checked' : '' }}
                        class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                    <label for="rounding_down" class="ml-3 block text-sm text-gray-700">
                        Bulatkan ke bawah
                    </label>
                </div>
                <div class="flex items-center">
                    <input id="rounding_up" name="discount.rounding" type="radio" value="up"
                        {{ old('discount.rounding', $settings['discount.rounding'] ?? 'down') == 'up' ? 'checked' : '' }}
                        class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                    <label for="rounding_up" class="ml-3 block text-sm text-gray-700">
                        Bulatkan ke atas
                    </label>
                </div>
                <div class="flex items-center">
                    <input id="rounding_none" name="discount.rounding" type="radio" value="none"
                        {{ old('discount.rounding', $settings['discount.rounding'] ?? 'down') == 'none' ? 'checked' : '' }}
                        class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                    <label for="rounding_none" class="ml-3 block text-sm text-gray-700">
                        Tidak dibulatkan
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
