<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Batasan Waktu Void (Jam) *</label>
        <input type="number" name="void_time_limit" min="0"
            value="{{ old('void_time_limit', $settings['void_time_limit'] ?? 24) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3"
            required>
        <p class="text-xs text-gray-500 mt-1">Transaksi lama tidak dapat dibatalkan (void) setelah melewati batas waktu
            ini. (0 = Tidak ada batas)</p>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-2">Keamanan PIN Kasir</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Percobaan Gagal</label>
                <input type="number" name="pin_attempt_limit" min="1" max="10"
                    value="{{ old('pin_attempt_limit', $settings['pin_attempt_limit'] ?? 3) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Kunci (Menit)</label>
                <input type="number" name="pin_lockout_duration" min="1"
                    value="{{ old('pin_lockout_duration', $settings['pin_lockout_duration'] ?? 15) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Akun kasir akan dikunci sementara jika salah memasukkan PIN berulang kali.
        </p>
    </div>
</div>