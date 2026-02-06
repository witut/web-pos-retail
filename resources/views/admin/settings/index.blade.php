<x-layouts.admin :title="'Pengaturan Toko'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Toko</h2>
        <p class="text-sm text-gray-500">Konfigurasi informasi toko, keuangan, dan fitur sistem.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('POST')

        <div x-data="{ tab: 'general' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Tabs Header -->
            <div class="flex border-b border-gray-100 bg-gray-50/50">
                <button type="button" @click="tab = 'general'"
                    :class="tab === 'general' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Umum
                </button>
                <button type="button" @click="tab = 'financial'"
                    :class="tab === 'financial' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Keuangan
                </button>
                <button type="button" @click="tab = 'security'"
                    :class="tab === 'security' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Keamanan
                </button>
                <button type="button" @click="tab = 'inventory'"
                    :class="tab === 'inventory' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Inventaris
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="p-6">
                <!-- General Tab -->
                <div x-show="tab === 'general'" x-cloak>
                    <div class="grid grid-cols-1 gap-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko *</label>
                            <input type="text" name="store_name"
                                value="{{ old('store_name', $settings['store_name']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Nama ini akan dicetak di bagian atas struk belanja.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Toko</label>
                            <textarea name="store_address" rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">{{ old('store_address', $settings['store_address']) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" name="store_phone"
                                value="{{ old('store_phone', $settings['store_phone']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
                            <input type="text" name="receipt_footer"
                                value="{{ old('receipt_footer', $settings['receipt_footer']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                            <p class="text-xs text-gray-500 mt-1">Pesan singkat di bagian bawah struk (contoh: Terima
                                Kasih, Barang tidak dapat dikembalikan).</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Tab -->
                <div x-show="tab === 'financial'" x-cloak>
                    <div class="grid grid-cols-1 gap-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Simbol Mata Uang</label>
                            <input type="text" name="currency_symbol"
                                value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (Tax Rate) % *</label>
                            <div class="relative rounded-md shadow-sm sm:w-1/3">
                                <input type="number" name="tax_rate" step="0.1" min="0" max="100"
                                    value="{{ old('tax_rate', $settings['tax_rate']) }}"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm pr-10"
                                    required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Persentase PPn yang dikenakan pada setiap transaksi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div x-show="tab === 'security'" x-cloak>
                    <div class="grid grid-cols-1 gap-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batasan Waktu Void (Jam)
                                *</label>
                            <input type="number" name="void_time_limit" min="0"
                                value="{{ old('void_time_limit', $settings['void_time_limit']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Transaksi lama tidak dapat dibatalkan (void) setelah
                                melewati batas waktu ini. (0 = Tidak ada batas)</p>
                        </div>

                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-800 mb-2">Keamanan PIN Kasir</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Batas Percobaan
                                        Gagal</label>
                                    <input type="number" name="pin_attempt_limit" min="1" max="10"
                                        value="{{ old('pin_attempt_limit', $settings['pin_attempt_limit']) }}"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Kunci
                                        (Menit)</label>
                                    <input type="number" name="pin_lockout_duration" min="1"
                                        value="{{ old('pin_lockout_duration', $settings['pin_lockout_duration']) }}"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Akun kasir akan dikunci sementara jika salah
                                memasukkan PIN berulang kali.</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Tab -->
                <div x-show="tab === 'inventory'" x-cloak>
                    <div class="grid grid-cols-1 gap-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Low Stock Threshold
                                *</label>
                            <input type="number" name="low_stock_threshold" min="0"
                                value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Produk dianggap "Stok Menipis" jika jumlahnya di bawah
                                angka ini (kecuali diatur spesifik per produk).</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 font-medium flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin>