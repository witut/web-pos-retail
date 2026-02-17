<x-layouts.pos :title="'POS Terminal'">
    <div class="h-full flex" x-data="posTerminal()" x-init="initPOS()" @keydown.window="handleKeyboard($event)" @points-updated.window="updatePoints($event.detail)" @customer-updated.window="updateCustomer($event.detail)">
        <!-- Left Panel: Product Search & Cart -->
        <div class="flex-1 flex flex-col p-4 space-y-4">
            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-sm p-4 relative">
                <div class="flex space-x-3">
                    <div class="flex-1 relative">
                        <input type="text" x-ref="searchInput" x-model="searchQuery"
                            @keydown.enter.prevent="handleSearchEnter()"
                            @keydown.arrow-down.prevent="navigateAutocomplete(1)"
                            @keydown.arrow-up.prevent="navigateAutocomplete(-1)" @keydown.escape="closeAutocomplete()"
                            @input.debounce.300ms="autocomplete()" placeholder="Scan barcode atau ketik nama produk..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 text-lg">
                        <svg class="w-6 h-6 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button @click="searchProduct()"
                        class="px-6 py-3 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors font-medium">
                        Cari
                    </button>
                </div>

                <!-- Autocomplete Dropdown -->
                <div x-show="autocompleteResults.length > 0" x-cloak
                    class="absolute left-4 right-4 z-50 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden">
                    <template x-for="(item, idx) in autocompleteResults" :key="item.id">
                        <div @click="selectProductFromAutocomplete(idx)"
                            :class="{ 'bg-slate-100': autocompleteIndex === idx }"
                            class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-gray-800" x-text="item.name"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="item.sku"></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-medium text-slate-700" x-text="formatCurrency(item.price)"></span>
                                    <span class="text-xs text-gray-500 ml-2">Stok: <span
                                            x-text="Math.floor(item.stock)"></span></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Error Message -->
                <div x-show="searchError" x-cloak class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm" x-text="searchError"></p>
                </div>
            </div>

            {{-- Customer Selection Component --}}
            @include('cashier.pos._customer-component', [
                'cashierCanCreate' => $cashierCanCreate
            ])

            <!-- Cart Table -->
            <div class="flex-1 bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Keranjang Belanja</h3>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full" x-show="cart.length > 0">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-24">Qty
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">Harga
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">
                                    Subtotal</th>
                                <th class="px-4 py-2 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <tr class="cart-item" :class="{ 'bg-blue-50': selectedCartIndex === index }">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800" x-text="item.name"></div>
                                        <div class="text-xs text-gray-500" x-text="item.sku"></div>

                                        <!-- Unit Selector -->
                                        <div x-show="item.available_units && item.available_units.length > 1"
                                            class="mt-1">
                                            <select x-model="item.unit" @change="changeUnit(index, $event.target.value)"
                                                class="text-xs border-gray-300 rounded focus:ring-slate-500 focus:border-slate-500 py-1 pl-2 pr-6 bg-slate-50 text-slate-700">
                                                <template x-for="unit in item.available_units" :key="unit.name">
                                                    <option :value="unit.name" x-text="unit.name"
                                                        :selected="unit.name === item.unit"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div x-show="!item.available_units || item.available_units.length <= 1"
                                            class="text-xs text-gray-400 mt-1">
                                            <span x-text="item.unit"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center space-x-1 relative">
                                            <!-- Stock Error Tooltip (Centered over controls) -->
                                            <div x-show="item.showStockError" x-transition.opacity.duration.200ms
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max px-2 py-1 bg-red-600 text-white text-xs rounded shadow-lg z-10 pointer-events-none">
                                                <span x-text="'Stok hanya ' + item.stock"></span>
                                                <!-- Arrow -->
                                                <div
                                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-red-600">
                                                </div>
                                            </div>

                                            <button @click="decrementQty(index)"
                                                class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded text-gray-700">
                                                -
                                            </button>
                                            <input type="number" x-model.number="item.qty" min="1"
                                                @change="validateManualQty(index)"
                                                class="w-12 text-center border border-gray-300 rounded py-1 text-sm">
                                            <button @click="incrementQty(index)"
                                                class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded text-gray-700">
                                                +
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm" x-text="formatCurrency(item.price)"></td>
                                    <td class="px-4 py-3 text-right font-medium" x-text="formatCurrency(item.subtotal)">
                                    </td>
                                    <td class="px-4 py-3">
                                        <button @click="removeItem(index)"
                                            class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Empty Cart -->
                    <div x-show="cart.length === 0"
                        class="flex-1 flex flex-col items-center justify-center py-16 text-gray-400">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-lg">Keranjang kosong</p>
                        <p class="text-sm">Scan barcode atau cari produk untuk memulai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Summary & Payment -->
        <div class="w-96 bg-white shadow-lg flex flex-col h-full overflow-hidden">
            <!-- Summary - Scrollable -->
            <div class="flex-1 p-6 overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Ringkasan</h3>

                <div class="space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Jumlah Item</span>
                        <span class="font-medium" x-text="totalItems + ' item'"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div x-show="pointsDiscount > 0" class="flex justify-between text-green-600">
                        <span>Diskon Poin</span>
                        <span class="font-medium">-<span x-text="formatCurrency(pointsDiscount)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>PPN ({{ $taxRate ?? 0 }}%)</span>
                        <span class="font-medium" x-text="formatCurrency(taxAmount)"></span>
                    </div>
                    <div class="border-t-2 border-gray-200 pt-3">
                        <div class="flex justify-between text-xl font-bold text-gray-800">
                            <span>TOTAL</span>
                            <span class="text-slate-700" x-text="formatCurrency(finalTotal)"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Cash Buttons -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-2">Uang Diterima (Tunai)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="amount in quickCashAmounts" :key="amount">
                            <button type="button" @click="setAmountPaid(amount)"
                                class="py-2 px-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium text-gray-700 transition-colors"
                                x-text="formatCurrency(amount)">
                            </button>
                        </template>
                    </div>
                    <input type="text" :value="formatNumber(amountPaid)" @input="updateAmountPaid($event.target.value)"
                        placeholder="Ketik jumlah..."
                        class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg text-right text-base font-medium focus:ring-2 focus:ring-slate-500">
                </div>

                <!-- Change -->
                <div class="mt-3 p-3 rounded-lg" :class="change >= 0 ? 'bg-green-50' : 'bg-red-50'"
                    x-show="amountPaid > 0">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Kembalian</span>
                        <span class="text-xl font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="formatCurrency(change)"></span>
                    </div>
                </div>
            </div>

            <!-- Payment Buttons - Sticky Footer -->
            <div class="flex-shrink-0 p-3 bg-gray-50 border-t border-gray-200 space-y-2">
                <button type="button" @click="openPaymentModal()" :disabled="cart.length === 0"
                    class="w-full py-3 bg-emerald-600 text-white text-base font-bold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        BAYAR (F2)
                    </span>
                </button>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click.prevent="console.log('Batal clicked'); confirmClearCart()"
                        class="py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition-colors cursor-pointer">
                        Batal (ESC)
                    </button>
                    <a href="{{ route('pos.history') }}"
                        class="py-2 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors text-center">
                        Riwayat (F9)
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.prevent="closePaymentModal()">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden"
                @click.outside="closePaymentModal()">
                <div class="px-6 py-4 bg-slate-800 text-white">
                    <h3 class="text-xl font-bold">Konfirmasi Pembayaran</h3>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-gray-700'"
                                class="py-3 rounded-lg font-medium transition-colors">
                                Tunai
                            </button>
                            <button @click="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-gray-700'"
                                class="py-3 rounded-lg font-medium transition-colors">
                                Kartu
                            </button>
                            <button @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-gray-700'"
                                class="py-3 rounded-lg font-medium transition-colors">
                                QRIS
                            </button>
                            <button @click="paymentMethod = 'transfer'"
                                :class="paymentMethod === 'transfer' ? 'bg-slate-800 text-white' : 'bg-gray-100 text-gray-700'"
                                class="py-3 rounded-lg font-medium transition-colors">
                                Transfer
                            </button>
                        </div>
                    </div>

                    <!-- Amount Summary -->
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                        <div x-show="pointsDiscount > 0" class="flex justify-between text-green-600">
                            <span>Diskon Poin</span>
                            <span class="font-medium">-<span x-text="formatCurrency(pointsDiscount)"></span></span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-800 font-bold">TOTAL BAYAR</span>
                            <span class="font-bold text-xl" x-text="formatCurrency(finalTotal)"></span>
                        </div>
                        <div class="flex justify-between" x-show="paymentMethod === 'cash'">
                            <span class="text-gray-600">Uang Diterima</span>
                            <span class="font-medium" x-text="formatCurrency(amountPaid)"></span>
                        </div>
                        <div class="flex justify-between border-t pt-2" x-show="paymentMethod === 'cash'">
                            <span class="text-gray-600">Kembalian</span>
                            <span class="font-bold text-lg" :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
                                x-text="formatCurrency(change)"></span>
                        </div>
                    </div>

                    <!-- Cash Input (for cash payment) -->
                    <div x-show="paymentMethod === 'cash'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima</label>
                        <input type="text" x-ref="amountPaidInput" :value="formatNumber(amountPaid)"
                            @input="updateAmountPaid($event.target.value)" @keydown.enter.prevent="processCheckout()"
                            placeholder="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg text-right focus:ring-2 focus:ring-slate-500">
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex space-x-3">
                    <button @click="closePaymentModal()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button @click="processCheckout()"
                        :disabled="isProcessing || (paymentMethod === 'cash' && change < 0)"
                        class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">Proses Pembayaran</span>
                        <span x-show="isProcessing">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h3>
                <p class="text-gray-500 mb-6">Transaksi telah disimpan</p>
                <p class="text-sm text-gray-600 mb-1">No. Invoice</p>
                <p class="text-xl font-mono font-bold text-slate-700 mb-6" x-text="lastInvoice"></p>
                <div class="space-y-3">
                    <button @click="printReceipt()" x-ref="printReceiptBtn"
                        class="w-full py-3 bg-slate-800 text-white font-medium rounded-lg hover:bg-slate-900 transition-colors">
                        Cetak Struk
                    </button>
                    <button @click="newTransaction()"
                        class="w-full py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal (Custom) -->
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.prevent="showConfirmModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2" x-text="confirmTitle"></h3>
                    <p class="text-gray-500 mb-6" x-text="confirmMessage"></p>
                    <div class="flex space-x-3">
                        <button @click="showConfirmModal = false"
                            class="flex-1 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button @click="executeConfirmAction()"
                            class="flex-1 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
                            Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            /* Customer Component for POS */
            console.log('LOADING CUSTOMER COMPONENT SCRIPT INLINED');

            function customerComponent() {
                return {
                    // Customer Data
                    selectedCustomer: null,
                    customerSearch: '',
                    customerResults: [],
                    customerSearchFocused: false,

                    // Points Management
                    pointsToRedeem: 0,
                    pointsDiscount: 0,
                    tempPointsDiscount: 0,
                    redeemAmount: 0,

                    // Quick Add
                    showQuickAddModal: false,
                    newCustomer: {
                        name: '',
                        phone: ''
                    },
                    isQuickAdding: false,
                    quickAddError: '',

                    // Redeem Modal
                    showRedeemModal: false,
                    redeemError: '',

                    // Customer Search
                    async searchCustomers() {
                        if (this.customerSearch.length < 2) {
                            this.customerResults = [];
                            return;
                        }

                        try {
                            const response = await fetch(`/pos/customers/search?q=${encodeURIComponent(this.customerSearch)}`);
                            const data = await response.json();
                            this.customerResults = data;
                        } catch (error) {
                            console.error('Customer search error:', error);
                            this.customerResults = [];
                        }
                    },

                    selectCustomer(customer) {
                        this.selectedCustomer = customer;
                        this.customerSearch = '';
                        this.customerResults = [];
                        this.customerSearchFocused = false;
                        
                        // Reset points on customer change
                        this.pointsToRedeem = 0;
                        this.pointsDiscount = 0;
                        this.$dispatch('points-updated', { discount: 0, redeem: 0 });
                        this.$dispatch('customer-updated', { customer: customer });
                    },

                    clearCustomer() {
                        this.selectedCustomer = null;
                        this.pointsToRedeem = 0;
                        this.pointsDiscount = 0;
                        this.$dispatch('points-updated', { discount: 0, redeem: 0 });
                        this.$dispatch('customer-updated', { customer: null });
                    },

                    // Quick Add Customer
                    openQuickAddModal() {
                        this.showQuickAddModal = true;
                        this.newCustomer = { name: '', phone: '' };
                        this.quickAddError = '';
                        this.$nextTick(() => {
                            if (this.$refs.quickAddNameInput) {
                                this.$refs.quickAddNameInput.focus();
                            }
                        });
                    },

                    closeQuickAddModal() {
                        this.showQuickAddModal = false;
                        this.newCustomer = { name: '', phone: '' };
                        this.quickAddError = '';
                    },

                    async saveNewCustomer() {
                        if (!this.newCustomer.name || !this.newCustomer.phone) {
                            this.quickAddError = 'Nama dan No. HP wajib diisi';
                            return;
                        }

                        this.isQuickAdding = true;
                        this.quickAddError = '';

                        try {
                            const response = await fetch('/pos/customers/quick-add', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(this.newCustomer)
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                this.quickAddError = data.message || 'Gagal menambahkan pelanggan';
                                return;
                            }

                            this.selectedCustomer = data.customer;
                            this.$dispatch('points-updated', { discount: 0, redeem: 0 });
                            this.$dispatch('customer-updated', { customer: data.customer });
                            this.closeQuickAddModal();

                        } catch (error) {
                            this.quickAddError = 'Terjadi kesalahan: ' + error.message;
                            console.error(error);
                        } finally {
                            this.isQuickAdding = false;
                        }
                    },

                    // Redeem Points
                    openRedeemModal() {
                        this.showRedeemModal = true;
                        this.redeemAmount = 0;
                        this.tempPointsDiscount = 0;
                        this.redeemError = '';
                    },

                    closeRedeemModal() {
                        this.showRedeemModal = false;
                        this.redeemAmount = 0;
                        this.tempPointsDiscount = 0;
                        this.redeemError = '';
                    },

                    calculateRedeemDiscount() {
                        if (!this.redeemAmount || this.redeemAmount <= 0) {
                            this.tempPointsDiscount = 0;
                            return;
                        }
                        // Rate: 100 points = Rp 10,000 (configurable via settings)
                        // Simplified: 1 point = Rp 100
                        this.tempPointsDiscount = this.redeemAmount * 100;
                    },

                    confirmRedeemPoints() {
                        if (!this.redeemAmount || this.redeemAmount <= 0) {
                            this.redeemError = 'Masukkan jumlah poin';
                            return;
                        }

                        if (this.redeemAmount > (this.selectedCustomer?.points_balance || 0)) {
                            this.redeemError = 'Poin tidak mencukupi';
                            return;
                        }

                        this.pointsToRedeem = this.redeemAmount;
                        this.pointsDiscount = this.tempPointsDiscount;
                        this.$dispatch('points-updated', { discount: this.pointsDiscount, redeem: this.pointsToRedeem });
                        this.closeRedeemModal();
                    },

                    // Utility
                    formatCurrency(value) {
                        const num = parseInt(value) || 0;
                        return 'Rp ' + new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(num);
                    },

                    formatNumber(value) {
                        if (!value) return '0';
                        return new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        </script>
        <script>
            function posTerminal() {
                return {
                    // Data
                    searchQuery: '',
                    searchError: '',
                    autocompleteResults: [],
                    autocompleteIndex: -1,
                    cart: [],
                    selectedCartIndex: -1,
                    taxRate: {{ $taxRate ?? 0 }},
                    taxType: '{{ $taxType ?? 'exclusive' }}',
                    amountPaid: 0,
                    paymentMethod: 'cash',
                    showPaymentModal: false,
                    showSuccessModal: false,
                    isProcessing: false,
                    lastInvoice: '',
                    lastTransactionId: null,
                    quickCashAmounts: [10000, 20000, 50000, 100000, 200000, 500000],

                    // Custom Confirm Modal
                    showConfirmModal: false,
                    confirmTitle: '',
                    confirmMessage: '',
                    confirmAction: null,

                    // Points Data (from event)
                    pointsDiscount: 0,
                    pointsToRedeem: 0,
                    customerId: null,

                    // localStorage key for cart persistence
                    storageKey: 'pos_cart_data',

                    // Initialize
                    initPOS() {
                        // Load cart from localStorage
                        this.loadCartFromStorage();

                        // Watch cart changes and save to localStorage
                        this.$watch('cart', (value) => {
                            this.saveCartToStorage();
                        }, { deep: true });

                        this.$refs.searchInput.focus();
                    },

                    // localStorage methods
                    saveCartToStorage() {
                        try {
                            const data = {
                                cart: this.cart,
                                amountPaid: this.amountPaid,
                                paymentMethod: this.paymentMethod,
                                savedAt: new Date().toISOString()
                            };
                            localStorage.setItem(this.storageKey, JSON.stringify(data));
                        } catch (e) {
                            console.warn('Failed to save cart to localStorage:', e);
                        }
                    },

                    loadCartFromStorage() {
                        try {
                            const stored = localStorage.getItem(this.storageKey);
                            if (stored) {
                                const data = JSON.parse(stored);
                                // Only restore if saved within last 24 hours
                                const savedAt = new Date(data.savedAt);
                                const hoursSinceSave = (Date.now() - savedAt.getTime()) / (1000 * 60 * 60);

                                if (hoursSinceSave < 24 && data.cart && data.cart.length > 0) {
                                    this.cart = data.cart;
                                    this.amountPaid = data.amountPaid || 0;
                                    this.paymentMethod = data.paymentMethod || 'cash';
                                    console.log('Cart restored from localStorage:', this.cart.length, 'items');
                                }
                            }
                        } catch (e) {
                            console.warn('Failed to load cart from localStorage:', e);
                        }
                    },

                    clearCartStorage() {
                        try {
                            localStorage.removeItem(this.storageKey);
                        } catch (e) {
                            console.warn('Failed to clear cart from localStorage:', e);
                        }
                    },

                    // Computed - menggunakan parseInt untuk memastikan nilai bulat
                    get totalItems() {
                        return this.cart.reduce((sum, item) => sum + parseInt(item.qty || 0), 0);
                    },
                    get totalCartValue() {
                        return this.cart.reduce((sum, item) => {
                            const subtotal = parseInt(item.subtotal || 0);
                            return sum + subtotal;
                        }, 0);
                    },
                    get subtotal() {
                        if (this.taxType === 'inclusive') {
                            return Math.round(this.totalCartValue - this.taxAmount);
                        }
                        return Math.round(this.totalCartValue);
                    },
                    get taxAmount() {
                        if (this.taxType === 'inclusive') {
                            // Tax included in price
                            return Math.round(this.totalCartValue - (this.totalCartValue / (1 + this.taxRate / 100)));
                        }
                        // Tax added on top
                        return Math.round(this.subtotal * (this.taxRate / 100));
                    },
                    get grandTotal() {
                        if (this.taxType === 'inclusive') {
                            return Math.round(this.totalCartValue);
                        }
                        return Math.round(this.subtotal + this.taxAmount);
                    },
                    get change() {
                        return Math.round(parseInt(this.amountPaid || 0) - this.finalTotal);
                    },
                    get finalTotal() {
                        return Math.max(0, Math.round(this.grandTotal - this.pointsDiscount));
                    },

                    // Methods
                    updatePoints(detail) {
                        this.pointsDiscount = detail.discount || 0;
                        this.pointsToRedeem = detail.redeem || 0;
                    },
                    updateCustomer(detail) {
                        this.customerId = detail.customer ? detail.customer.id : null;
                    },
                    handleSearchEnter() {
                        // Jika ada autocomplete dan ada item terpilih, pilih item tersebut
                        if (this.autocompleteResults.length > 0 && this.autocompleteIndex >= 0) {
                            this.selectProductFromAutocomplete(this.autocompleteIndex);
                        } else if (this.autocompleteResults.length > 0) {
                            // Jika ada hasil tapi tidak ada yang dipilih, pilih yang pertama
                            this.selectProductFromAutocomplete(0);
                        } else {
                            // Jika tidak ada hasil, lakukan search barcode/SKU
                            this.searchProduct();
                        }
                    },

                    navigateAutocomplete(direction) {
                        if (this.autocompleteResults.length === 0) return;

                        this.autocompleteIndex += direction;

                        // Wrap around
                        if (this.autocompleteIndex < 0) {
                            this.autocompleteIndex = this.autocompleteResults.length - 1;
                        } else if (this.autocompleteIndex >= this.autocompleteResults.length) {
                            this.autocompleteIndex = 0;
                        }
                    },

                    closeAutocomplete() {
                        this.autocompleteResults = [];
                        this.autocompleteIndex = -1;
                    },

                    async searchProduct() {
                        if (!this.searchQuery.trim()) return;

                        this.searchError = '';
                        this.autocompleteResults = [];
                        this.autocompleteIndex = -1;

                        try {
                            const response = await fetch(`/pos/search-product?q=${encodeURIComponent(this.searchQuery)}`);
                            const data = await response.json();

                            if (!response.ok) {
                                this.searchError = data.error || 'Produk tidak ditemukan';
                                return;
                            }

                            this.addToCart(data.product);
                            this.searchQuery = '';
                        } catch (error) {
                            this.searchError = 'Gagal mencari produk';
                            console.error(error);
                        }
                    },

                    async autocomplete() {
                        this.autocompleteIndex = -1;

                        if (this.searchQuery.length < 2) {
                            this.autocompleteResults = [];
                            return;
                        }

                        try {
                            const response = await fetch(`/pos/autocomplete?q=${encodeURIComponent(this.searchQuery)}`);
                            this.autocompleteResults = await response.json();
                        } catch (error) {
                            this.autocompleteResults = [];
                        }
                    },

                    selectProductFromAutocomplete(index) {
                        const item = this.autocompleteResults[index];
                        if (!item) return;

                        this.addToCart({
                            id: item.id,
                            sku: item.sku,
                            name: item.name,
                            selling_price: parseInt(item.price) || 0,
                            stock_on_hand: parseInt(item.stock) || 0,
                            base_unit: item.base_unit || 'pcs',
                            units: item.units || []
                        });
                        this.searchQuery = '';
                        this.autocompleteResults = [];
                        this.autocompleteIndex = -1;
                        this.$refs.searchInput.focus();
                    },

                    addToCart(product) {
                        const price = parseInt(product.selling_price) || 0;
                        const stock = parseInt(product.stock_on_hand) || 0;
                        const baseUnit = product.base_unit || 'pcs';
                        const productType = product.product_type || 'inventory';

                        // Prepare units array
                        let availableUnits = [{
                            name: baseUnit,
                            price: price,
                            conversion: 1,
                            is_base: true
                        }];

                        if (product.units && Array.isArray(product.units)) {
                            product.units.forEach(u => {
                                availableUnits.push({
                                    name: u.name,
                                    price: parseInt(u.selling_price),
                                    conversion: parseFloat(u.conversion_rate),
                                    is_base: false
                                });
                            });
                        }

                        // Check if item exists with the SAME unit (Base Unit)
                        const existing = this.cart.find(item => item.id === product.id && item.unit === baseUnit);

                        if (existing) {
                            // Skip stock check for service products
                            if (productType === 'service' || existing.qty < stock) {
                                existing.qty++;
                                existing.subtotal = Math.round(existing.qty * existing.price);
                            } else {
                                this.searchError = 'Stok tidak mencukupi';
                            }
                        } else {
                            this.cart.push({
                                id: product.id,
                                sku: product.sku,
                                name: product.name,
                                product_type: productType,
                                price: price,
                                qty: 1,
                                subtotal: price,
                                stock: stock,
                                unit: baseUnit,
                                available_units: availableUnits,
                                conversion: 1, // Base unit conversion is 1
                                showStockError: false // For tooltip
                            });
                        }
                        this.$refs.searchInput.focus();
                    },

                    changeUnit(index, unitName) {
                        const item = this.cart[index];
                        const unit = item.available_units.find(u => u.name === unitName);

                        if (unit) {
                            item.unit = unit.name;
                            item.price = unit.price;
                            item.conversion = unit.conversion; // Update conversion
                            this.updateSubtotal(index);
                            this.validateManualQty(index); // Re-validate stock with new unit
                        }
                    },

                    incrementQty(index) {
                        const item = this.cart[index];

                        // Validasi Stok dengan Conversion
                        const conversion = item.conversion || 1;
                        const qtyInBaseKey = (item.qty + 1) * conversion;

                        if (qtyInBaseKey > item.stock) {
                            // Show Tooltip
                            item.showStockError = true;

                            // Hide after 2 seconds
                            setTimeout(() => {
                                item.showStockError = false;
                            }, 2000);

                            return;
                        }

                        item.qty++;
                        this.updateSubtotal(index);
                    },

                    validateManualQty(index) {
                        const item = this.cart[index];
                        let qty = parseInt(item.qty);

                        // Ensure valid number
                        if (isNaN(qty) || qty < 1) {
                            qty = 1;
                        }

                        // Check against stock with conversion
                        const conversion = item.conversion || 1;
                        const qtyInBase = qty * conversion;

                        if (qtyInBase > item.stock) {
                            // Calculate max possible qty (floor)
                            const maxQty = Math.floor(item.stock / conversion);
                            qty = maxQty > 0 ? maxQty : 1; // Minimal 1 jika stock ada tapi < 1 unit (edge case)

                            // Show Tooltip
                            item.showStockError = true;
                            setTimeout(() => {
                                item.showStockError = false;
                            }, 2000);
                        }

                        item.qty = qty;
                        this.updateSubtotal(index);
                    },

                    decrementQty(index) {
                        const item = this.cart[index];
                        if (item.qty > 1) {
                            item.qty--;
                            this.updateSubtotal(index);
                        }
                    },

                    updateSubtotal(index) {
                        const item = this.cart[index];
                        item.subtotal = Math.round(item.qty * item.price);
                    },

                    removeItem(index) {
                        this.cart.splice(index, 1);

                    },

                    // Custom confirmation modal helper
                    showConfirm(title, message, action) {
                        this.confirmTitle = title;
                        this.confirmMessage = message;
                        this.confirmAction = action;
                        this.showConfirmModal = true;
                    },

                    executeConfirmAction() {
                        if (this.confirmAction) {
                            this.confirmAction();
                        }
                        this.showConfirmModal = false;
                        this.confirmAction = null;
                    },

                    confirmClearCart() {
                        console.log('confirmClearCart called, cart length:', this.cart.length);
                        if (this.cart.length === 0) {
                            // Show empty cart message with custom modal
                            this.showConfirm(
                                'Keranjang Kosong',
                                'Tidak ada item di keranjang.',
                                () => { }
                            );
                            return;
                        }

                        // Show confirmation modal for clearing cart
                        this.showConfirm(
                            'Kosongkan Keranjang?',
                            'Semua item di keranjang akan dihapus. Lanjutkan?',
                            () => {
                                this.cart = [];
                                this.amountPaid = 0;
                                this.searchError = '';
                                this.clearCartStorage(); // Clear localStorage too
                                this.$refs.searchInput.focus();
                            }
                        );
                    },

                    setAmountPaid(amount) {
                        this.amountPaid = parseInt(amount) || 0;
                        // Focus back to input after clicking quick cash
                        this.$nextTick(() => {
                            if (this.$refs.amountPaidInput) {
                                this.$refs.amountPaidInput.focus();
                            }
                        });
                    },

                    openPaymentModal() {
                        if (this.cart.length === 0) return;
                        if (this.amountPaid < this.finalTotal && this.paymentMethod === 'cash') {
                            this.amountPaid = this.finalTotal;
                        }
                        this.showPaymentModal = true;

                        // Auto-focus and select the amount input
                        this.$nextTick(() => {
                            if (this.$refs.amountPaidInput) {
                                this.$refs.amountPaidInput.focus();
                                this.$refs.amountPaidInput.select();
                            }
                        });
                    },

                    updateAmountPaid(value) {
                        // Strip non-digit characters
                        const number = value.replace(/[^0-9]/g, '');
                        this.amountPaid = parseInt(number) || 0;
                    },

                    formatNumber(value) {
                        if (!value) return '';
                        return new Intl.NumberFormat('id-ID').format(value);
                    },



                    closePaymentModal() {
                        this.showPaymentModal = false;
                    },

                    async processCheckout() {
                        if (this.paymentMethod === 'cash' && this.change < 0) {
                            alert('Uang tidak cukup!');
                            return;
                        }

                        if (this.isProcessing) return;
                        this.isProcessing = true;

                        try {
                            const payload = {
                                items: this.cart.map(item => ({
                                    product_id: item.id,
                                    qty: parseInt(item.qty),
                                    price: parseInt(item.price),
                                    unit_name: item.unit || 'pcs'
                                })),
                                payment: {
                                    method: this.paymentMethod,
                                    amount_paid: this.paymentMethod === 'cash'
                                        ? parseInt(this.amountPaid)
                                        : this.finalTotal
                                },
                                customer_id: this.customerId,
                                points_to_redeem: this.pointsToRedeem
                            };

                            console.log('Sending checkout:', payload);

                            const response = await fetch('/pos/checkout', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();
                            console.log('Checkout response:', data);

                            if (!response.ok || data.success === false) {
                                alert(data.error || data.message || 'Gagal memproses pembayaran');
                                this.isProcessing = false;
                                return;
                            }

                            this.lastInvoice = data.invoice_number || 'INV-TEMP';
                            this.lastTransactionId = data.transaction_id;
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;

                            // Auto-focus print receipt button
                            this.$nextTick(() => {
                                if (this.$refs.printReceiptBtn) {
                                    this.$refs.printReceiptBtn.focus();
                                }
                            });

                        } catch (error) {
                            console.error('Checkout error:', error);
                            alert('Terjadi kesalahan: ' + error.message);
                        } finally {
                            this.isProcessing = false;
                        }
                    },

                    printReceipt() {
                        if (this.lastTransactionId) {
                            window.open(`/pos/transaction/${this.lastTransactionId}/print`, '_blank');
                        }
                    },

                    newTransaction() {
                        this.cart = [];
                        this.amountPaid = 0;
                        this.paymentMethod = 'cash';
                        this.showSuccessModal = false;
                        this.clearCartStorage(); // Clear localStorage after checkout
                        this.$refs.searchInput.focus();
                    },

                    handleKeyboard(event) {
                        // F1 - Focus search
                        if (event.key === 'F1') {
                            event.preventDefault();
                            this.$refs.searchInput.focus();
                        }
                        // F2 - Open payment
                        if (event.key === 'F2') {
                            event.preventDefault();
                            this.openPaymentModal();
                        }
                        // F9 - History
                        if (event.key === 'F9') {
                            event.preventDefault();
                            window.location.href = '/pos/history';
                        }
                        // ESC - Cancel/Close modals or clear cart
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            if (this.showPaymentModal) {
                                this.closePaymentModal();
                            } else if (this.showSuccessModal) {
                                this.newTransaction();
                            } else if (this.autocompleteResults.length > 0) {
                                this.closeAutocomplete();
                            } else if (this.cart.length > 0) {
                                this.confirmClearCart();
                            }
                        }
                        // DEL - Remove selected item
                        if (event.key === 'Delete' && this.selectedCartIndex >= 0) {
                            event.preventDefault();
                            this.removeItem(this.selectedCartIndex);
                            this.selectedCartIndex = -1;
                        }
                    },

                    formatCurrency(value) {
                        const num = parseInt(value) || 0;
                        return 'Rp ' + new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(num);
                    },

                    formatNumber(value) {
                        if (!value) return '';
                        return new Intl.NumberFormat('id-ID').format(value);
                    },

                    updateAmountPaid(value) {
                        // Remove non-numeric chars
                        const numericValue = value.replace(/\D/g, '');
                        this.amountPaid = numericValue ? parseInt(numericValue) : 0;
                    }
                };
            }

            console.log('POS Script Loaded');
        </script>
    @endpush
</x-layouts.pos>