<x-layouts.app title="Tutup Register Kasir">
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center sm:text-left">
                Tutup Register Kasir (Z-Report)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Ringkasan Sesi</h3>
                    
                    <dl class="space-y-4">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Waktu Buka</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $session->opened_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Modal Awal</dt>
                            <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($report['opening_cash'], 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Total Penjualan Tunai</dt>
                            <dd class="text-sm font-bold text-green-600">+ Rp {{ number_format($report['cash_sales'], 0, ',', '.') }}</dd>
                        </div>
                        @if($report['cash_out'] > 0)
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Pengeluaran Kas</dt>
                            <dd class="text-sm font-bold text-red-600">- Rp {{ number_format($report['cash_out'], 0, ',', '.') }}</dd>
                        </div>
                        @endif
                        
                        <div class="pt-4 border-t flex justify-between items-center">
                            <dt class="text-base font-bold text-gray-900">Total Ekspektasi Sistem</dt>
                            <dd class="text-lg font-bold text-gray-900">Rp {{ number_format($report['expected_cash'], 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Input Form -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Input Fisik Uang</h3>
                    
                    <form action="{{ route('cashier.shift.update') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="closing_cash" class="block text-sm font-medium text-gray-700">Total Uang di Laci</label>
                                <div class="mt-1 relative rounded-md shadow-sm" x-data="{ 
                                    displayValue: '', 
                                    value: '',
                                    format(val) {
                                        if (!val) return '';
                                        return new Intl.NumberFormat('id-ID').format(val);
                                    },
                                    update(e) {
                                        let val = e.target.value.replace(/\D/g, '');
                                        this.value = val;
                                        this.displayValue = this.format(val);
                                    }
                                }">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-lg">Rp</span>
                                    </div>
                                    <input type="text" x-model="displayValue" @input="update" required autofocus
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-lg border-gray-300 rounded-md py-3"
                                        placeholder="0">
                                    <input type="hidden" name="closing_cash" x-model="value">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Hitung semua uang tunai di laci kasir.</p>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                                <textarea name="notes" id="notes" rows="3" 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Alasan selisih, dll..."></textarea>
                            </div>

                            <div class="pt-4">
                                <button type="submit" onclick="return confirm('Apakah Anda yakin data sudah benar? Sesi akan ditutup.')"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Tutup Register & Logout
                                </button>
                                
                                <a href="{{ route('pos.index') }}" class="mt-3 w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                    Batal (Kembali ke POS)
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
