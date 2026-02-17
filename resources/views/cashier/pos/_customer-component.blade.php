{{-- Customer Selection Component --}}
<div class="bg-white rounded-xl shadow-sm p-4" x-data="customerComponent()">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-700">Pelanggan</h3>
        <span x-show="!selectedCustomer" class="text-xs text-gray-400">(Opsional)</span>
    </div>

    {{-- Selected Customer Display --}}
    <div x-show="selectedCustomer" x-cloak class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium text-gray-800" x-text="selectedCustomer?.name"></span>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="selectedCustomer?.phone"></p>
                <div class="flex items-center gap-3 mt-2">
                    <span
                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span x-text="formatNumber(selectedCustomer?.points_balance || 0)"></span> poin
                    </span>
                    <button @click="openRedeemModal()" x-show="selectedCustomer?.points_balance > 0"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Tukar Poin
                    </button>
                </div>
            </div>
            <button @click="clearCustomer()"
                class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        {{-- Redeemed Points Display --}}
        <div x-show="pointsToRedeem > 0" class="mt-3 pt-3 border-t border-slate-200">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Poin Ditukar:</span>
                <span class="font-medium text-orange-600">-<span x-text="formatNumber(pointsToRedeem)"></span>
                    poin</span>
            </div>
            <div class="flex justify-between items-center text-sm mt-1">
                <span class="text-gray-600">Diskon:</span>
                <span class="font-bold text-green-600" x-text="formatCurrency(pointsDiscount)"></span>
            </div>
        </div>
    </div>

    {{-- Customer Search --}}
    <div x-show="!selectedCustomer" class="relative">
        <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()"
            @focus="customerSearchFocused = true" placeholder="Cari pelanggan (nama/HP)..."
            class="w-full pl-3 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 text-sm">

        {{-- Customer Search Results --}}
        <div x-show="customerResults.length > 0 && customerSearchFocused" x-cloak
            @click.outside="customerSearchFocused = false"
            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
            <template x-for="customer in customerResults" :key="customer.id">
                <div @click="selectCustomer(customer)"
                    class="px-3 py-2 hover:bg-slate-50 cursor-pointer border-b border-gray-100 last:border-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-800 text-sm" x-text="customer.name"></p>
                            <p class="text-xs text-gray-500" x-text="customer.phone"></p>
                        </div>
                        <span class="text-xs font-medium text-green-600"
                            x-text="formatNumber(customer.points_balance) + ' pt'"></span>
                    </div>
                </div>
            </template>
        </div>

        @if($cashierCanCreate ?? true)
            {{-- Quick Add Button --}}
            <button @click="openQuickAddModal()"
                class="w-full mt-2 py-2 text-sm text-slate-600 hover:text-slate-800 hover:bg-slate-50 border border-dashed border-gray-300 rounded-lg transition-colors">
                + Tambah Pelanggan Baru
            </button>
        @endif
    </div>

    {{-- Quick Add Customer Modal --}}
    <div x-show="showQuickAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape="closeQuickAddModal()">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
            @click.outside="closeQuickAddModal()">
            <div class="px-6 py-4 bg-slate-800 text-white">
                <h3 class="text-lg font-bold">Tambah Pelanggan Baru</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span
                            class="text-red-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" x-ref="quickAddNameInput"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span
                            class="text-red-500">*</span></label>
                    <input type="text" x-model="newCustomer.phone" placeholder="08123456789"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div x-show="quickAddError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm" x-text="quickAddError"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex space-x-3">
                <button @click="closeQuickAddModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button @click="saveNewCustomer()" :disabled="isQuickAdding"
                    class="flex-1 py-2 bg-slate-800 text-white font-medium rounded-lg hover:bg-slate-900 disabled:opacity-50">
                    <span x-show="!isQuickAdding">Simpan</span>
                    <span x-show="isQuickAdding">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Redeem Points Modal --}}
    <div x-show="showRedeemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape="closeRedeemModal()">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
            @click.outside="closeRedeemModal()">
            <div class="px-6 py-4 bg-slate-800 text-white">
                <h3 class="text-lg font-bold">Tukar Poin</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-600">Poin Tersedia</p>
                    <p class="text-2xl font-bold text-green-600"
                        x-text="formatNumber(selectedCustomer?.points_balance || 0)"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Poin</label>
                    <input type="number" x-model.number="redeemAmount" @input="calculateRedeemDiscount()"
                        :max="selectedCustomer?.points_balance" placeholder="Masukkan jumlah poin"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">Diskon yang didapat</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="formatCurrency(tempPointsDiscount)"></p>
                </div>
                <div x-show="redeemError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm" x-text="redeemError"></p>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex space-x-3">
                <button @click="closeRedeemModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button @click="confirmRedeemPoints()"
                    :disabled="!redeemAmount || redeemAmount <= 0 || redeemAmount > (selectedCustomer?.points_balance || 0)"
                    class="flex-1 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>