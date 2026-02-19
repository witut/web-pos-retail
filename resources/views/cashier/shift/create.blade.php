<x-layouts.cashier title="Buka Register Kasir / Sesi Baru">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6 flex items-center">
                <a href="{{ route('cashier.shift.history') }}"
                    class="mr-4 p-2 rounded-full hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Buka Sesi</h1>
                    <p class="text-sm text-gray-500">Mulai shift kasir baru</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900">Detail Sesi</h3>
                </div>

                <form action="{{ route('cashier.shift.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Outlet removed as requested -->

                    <!-- Opening Cash -->
                    <div x-data="{
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
                        <label for="opening_cash" class="block text-sm font-medium text-gray-700 mb-1">
                            Uang Modal Awal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" x-model="displayValue" @input="update"
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2"
                                placeholder="0" required autofocus>
                            <input type="hidden" name="opening_cash" x-model="value">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Masukkan jumlah uang tunai yang tersedia di laci kasir
                            saat ini.</p>
                    </div>

                    <!-- Notes (Optional) -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Catatan opsional untuk sesi ini..."></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors">
                            Buka Sesi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.cashier>