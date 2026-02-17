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
                    Pengaturan customer management dan sistem loyalty points. Fitur ini akan aktif setelah modul Customer Management diimplementasikan.
                </p>
            </div>
        </div>
    </div>

    <div>
        <label class="flex items-center">
            <input type="checkbox" name="customer.required" value="1"
                {{ old('customer.required', $settings['customer.required'] ?? '0') == '1' ? 'checked' : '' }}
                class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm font-medium text-gray-700">Wajibkan input pelanggan di setiap transaksi</span>
        </label>
        <p class="text-xs text-gray-500 mt-1 ml-6">Jika diaktifkan, kasir harus memilih pelanggan sebelum checkout.</p>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-3">Sistem Loyalty Points</h4>
        
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="customer.loyalty_enabled" value="1"
                    {{ old('customer.loyalty_enabled', $settings['customer.loyalty_enabled'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Aktifkan sistem poin loyalty</span>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konversi Pembelian ke Poin</label>
                <input type="text" name="customer.points_earn_rate"
                    value="{{ old('customer.points_earn_rate', $settings['customer.points_earn_rate'] ?? '10000:1') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
                    placeholder="10000:1">
                <p class="text-xs text-gray-500 mt-1">Format: Rp:Poin (contoh: 10000:1 = Rp 10.000 dapat 1 poin)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konversi Poin ke Diskon</label>
                <input type="text" name="customer.points_redeem_rate"
                    value="{{ old('customer.points_redeem_rate', $settings['customer.points_redeem_rate'] ?? '100:10000') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
                    placeholder="100:10000">
                <p class="text-xs text-gray-500 mt-1">Format: Poin:Rp (contoh: 100:10000 = 100 poin = Rp 10.000)</p>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Masa Berlaku Poin (Hari)</label>
            <input type="number" name="customer.points_expiry_days" min="0"
                value="{{ old('customer.points_expiry_days', $settings['customer.points_expiry_days'] ?? '365') }}"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
            <p class="text-xs text-gray-500 mt-1">0 = tidak ada batas waktu</p>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Transaksi untuk Dapat Poin (Rp)</label>
            <input type="number" name="customer.points_min_transaction" min="0" step="1000"
                value="{{ old('customer.points_min_transaction', $settings['customer.points_min_transaction'] ?? '0') }}"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
            <p class="text-xs text-gray-500 mt-1">Total belanja minimal agar pelanggan mendapatkan poin</p>
        </div>

        <div class="mt-4">
            <label class="flex items-center">
                <input type="checkbox" name="customer.points_with_discount" value="1"
                    {{ old('customer.points_with_discount', $settings['customer.points_with_discount'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Poin bisa digunakan bersamaan dengan diskon</span>
            </label>
        </div>
    </div>
</div>
