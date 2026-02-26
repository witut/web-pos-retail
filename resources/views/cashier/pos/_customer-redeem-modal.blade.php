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