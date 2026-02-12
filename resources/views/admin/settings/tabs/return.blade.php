<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Pengaturan retur dan refund. Fitur ini akan aktif setelah modul Return Management diimplementasikan.
                </p>
            </div>
        </div>
    </div>

    <div>
        <label class="flex items-center">
            <input type="checkbox" name="return.enabled" value="1" {{ old('return.enabled', $settings['return.enabled'] ?? '1') == '1' ? 'checked' : '' }}
                class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm font-medium text-gray-700">Aktifkan fitur retur</span>
        </label>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu Retur (Hari)</label>
        <input type="number" name="return.max_days" min="0"
            value="{{ old('return.max_days', $settings['return.max_days'] ?? '7') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
        <p class="text-xs text-gray-500 mt-1">Sejak tanggal pembelian (0 = tidak ada batas)</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Auto-Approve untuk Nilai ≤ Rp</label>
        <input type="number" name="return.auto_approve_limit" min="0"
            value="{{ old('return.auto_approve_limit', $settings['return.auto_approve_limit'] ?? '100000') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/2">
        <p class="text-xs text-gray-500 mt-1">Lebih dari ini butuh approval admin (0 = semua butuh approval)</p>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-3">Metode Refund</h4>
        <p class="text-xs text-gray-500 mb-2">Pilih metode refund yang diizinkan (comma-separated)</p>
        <input type="text" name="return.refund_methods"
            value="{{ old('return.refund_methods', $settings['return.refund_methods'] ?? 'cash,exchange,credit') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
            placeholder="cash,exchange,credit">
        <p class="text-xs text-gray-500 mt-1">Opsi: cash (tunai), exchange (tukar barang), credit (store credit)</p>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-3">Kebijakan Retur</h4>

        <div class="space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="return.restore_stock" value="1" {{ old('return.restore_stock', $settings['return.restore_stock'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Kembalikan stok otomatis saat retur approved</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="return.require_photo" value="1" {{ old('return.require_photo', $settings['return.require_photo'] ?? '0') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Require foto bukti untuk retur (rusak/cacat)</span>
            </label>
        </div>
    </div>
</div>