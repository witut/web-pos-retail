<x-layouts.pos :title="'POS Terminal'">
    <div class="h-full flex" x-data="posTerminal()" x-init="initPOS()" @keydown.window="handleKeyboard($event)">
        <!-- Left Panel: Product Search & Cart -->
        <div class="flex-1 flex flex-col p-4 space-y-4">
            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex space-x-3">
                    <div class="flex-1 relative">
                        <input type="text" x-ref="searchInput" x-model="searchQuery" @keydown.enter="searchProduct()"
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
                    class="absolute z-50 w-full max-w-2xl mt-2 bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden">
                    <template x-for="(item, idx) in autocompleteResults" :key="item.id">
                        <div @click="selectProduct(item)"
                            class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-gray-800" x-text="item.name"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="item.sku"></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-medium text-slate-700" x-text="formatCurrency(item.price)"></span>
                                    <span class="text-xs text-gray-500 ml-2">Stok: <span
                                            x-text="item.stock"></span></span>
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
                                <tr class="cart-item" :class="{ 'bg-blue-50': selectedIndex === index }">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800" x-text="item.name"></div>
                                        <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center space-x-1">
                                            <button @click="decrementQty(index)"
                                                class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded text-gray-700">
                                                -
                                            </button>
                                            <input type="number" x-model.number="item.qty" min="1"
                                                @change="updateSubtotal(index)"
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
        <div class="w-96 bg-white shadow-lg flex flex-col">
            <!-- Summary -->
            <div class="flex-1 p-6 flex flex-col">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Ringkasan</h3>

                <div class="space-y-4 flex-1">
                    <div class="flex justify-between text-gray-600">
                        <span>Jumlah Item</span>
                        <span class="font-medium" x-text="totalItems + ' item'"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>PPN ({{ $taxRate ?? 0 }}%)</span>
                        <span class="font-medium" x-text="formatCurrency(taxAmount)"></span>
                    </div>
                    <div class="border-t-2 border-gray-200 pt-4">
                        <div class="flex justify-between text-xl font-bold text-gray-800">
                            <span>TOTAL</span>
                            <span class="text-slate-700" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Cash Buttons -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-3">Uang Diterima (Tunai)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="amount in quickCashAmounts" :key="amount">
                            <button @click="setAmountPaid(amount)"
                                class="py-2 px-3 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors"
                                x-text="formatCurrency(amount)">
                            </button>
                        </template>
                    </div>
                    <input type="number" x-model.number="amountPaid" placeholder="Atau ketik jumlah..."
                        class="w-full mt-3 px-4 py-3 border border-gray-300 rounded-lg text-right text-lg font-medium focus:ring-2 focus:ring-slate-500">
                </div>

                <!-- Change -->
                <div class="mt-4 p-4 rounded-lg" :class="change >= 0 ? 'bg-green-50' : 'bg-red-50'"
                    x-show="amountPaid > 0">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Kembalian</span>
                        <span class="text-2xl font-bold" :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="formatCurrency(change)"></span>
                    </div>
                </div>
            </div>

            <!-- Payment Buttons -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-3">
                <button @click="openPaymentModal()" :disabled="cart.length === 0"
                    class="w-full py-4 bg-emerald-600 text-white text-lg font-bold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        BAYAR (F2)
                    </span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="clearCart()"
                        class="py-3 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition-colors">
                        Batal (ESC)
                    </button>
                    <a href="{{ route('pos.history') }}"
                        class="py-3 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors text-center">
                        Riwayat (F9)
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape="closePaymentModal()">
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
                            <span class="text-gray-600">Total Belanja</span>
                            <span class="font-bold text-lg" x-text="formatCurrency(grandTotal)"></span>
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
                        <input type="number" x-model.number="amountPaid"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg text-right focus:ring-2 focus:ring-slate-500">
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex space-x-3">
                    <button @click="closePaymentModal()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button @click="processCheckout()" :disabled="paymentMethod === 'cash' && change < 0"
                        class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Proses Pembayaran
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
                    <button @click="printReceipt()"
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
    </div>

    @push('scripts')
        <script>
            function posTerminal() {
                return {
                    // Data
                    searchQuery: '',
                    searchError: '',
                    autocompleteResults: [],
                    cart: [],
                    selectedIndex: -1,
                    taxRate: {{ $taxRate ?? 0 }},
                    amountPaid: 0,
                    paymentMethod: 'cash',
                    showPaymentModal: false,
                    showSuccessModal: false,
                    lastInvoice: '',
                    lastTransactionId: null,
                    quickCashAmounts: [10000, 20000, 50000, 100000, 200000, 500000],

                    // Initialize
                    initPOS() {
                        this.$refs.searchInput.focus();
                    },

                    // Computed
                    get totalItems() {
                        return this.cart.reduce((sum, item) => sum + item.qty, 0);
                    },
                    get subtotal() {
                        return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
                    },
                    get taxAmount() {
                        return Math.round(this.subtotal * (this.taxRate / 100));
                    },
                    get grandTotal() {
                        return this.subtotal + this.taxAmount;
                    },
                    get change() {
                        return this.amountPaid - this.grandTotal;
                    },

                    // Methods
                    async searchProduct() {
                        if (!this.searchQuery.trim()) return;

                        this.searchError = '';
                        this.autocompleteResults = [];

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
                        }
                    },

                    async autocomplete() {
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

                    selectProduct(item) {
                        this.addToCart({
                            id: item.id,
                            sku: item.sku,
                            name: item.name,
                            selling_price: item.price,
                            stock_on_hand: item.stock,
                            base_unit: 'pcs'
                        });
                        this.searchQuery = '';
                        this.autocompleteResults = [];
                    },

                    addToCart(product) {
                        const existing = this.cart.find(item => item.id === product.id);
                        if (existing) {
                            if (existing.qty < product.stock_on_hand) {
                                existing.qty++;
                                existing.subtotal = existing.qty * existing.price;
                            } else {
                                this.searchError = 'Stok tidak mencukupi';
                            }
                        } else {
                            this.cart.push({
                                id: product.id,
                                sku: product.sku,
                                name: product.name,
                                price: product.selling_price,
                                qty: 1,
                                subtotal: product.selling_price,
                                stock: product.stock_on_hand,
                                unit: product.base_unit
                            });
                        }
                        this.$refs.searchInput.focus();
                    },

                    incrementQty(index) {
                        const item = this.cart[index];
                        if (item.qty < item.stock) {
                            item.qty++;
                            this.updateSubtotal(index);
                        }
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
                        item.subtotal = item.qty * item.price;
                    },

                    removeItem(index) {
                        this.cart.splice(index, 1);
                    },

                    clearCart() {
                        if (confirm('Yakin ingin mengosongkan keranjang?')) {
                            this.cart = [];
                            this.amountPaid = 0;
                        }
                    },

                    setAmountPaid(amount) {
                        this.amountPaid = amount;
                    },

                    openPaymentModal() {
                        if (this.cart.length === 0) return;
                        if (this.amountPaid < this.grandTotal && this.paymentMethod === 'cash') {
                            this.amountPaid = this.grandTotal;
                        }
                        this.showPaymentModal = true;
                    },

                    closePaymentModal() {
                        this.showPaymentModal = false;
                    },

                    async processCheckout() {
                        if (this.paymentMethod === 'cash' && this.change < 0) {
                            alert('Uang tidak cukup!');
                            return;
                        }

                        try {
                            const response = await fetch('/pos/checkout', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    items: this.cart.map(item => ({
                                        product_id: item.id,
                                        qty: item.qty,
                                        price: item.price,
                                        unit_name: item.unit
                                    })),
                                    payment: {
                                        method: this.paymentMethod,
                                        amount_paid: this.paymentMethod === 'cash' ? this.amountPaid : this.grandTotal
                                    }
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                alert(data.error || 'Gagal memproses pembayaran');
                                return;
                            }

                            this.lastInvoice = data.invoice_number || 'INV/2026/02/00001';
                            this.lastTransactionId = data.transaction_id;
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;

                        } catch (error) {
                            alert('Terjadi kesalahan');
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
                        // ESC - Cancel/Close
                        if (event.key === 'Escape') {
                            if (this.showPaymentModal) {
                                this.closePaymentModal();
                            } else if (this.showSuccessModal) {
                                this.newTransaction();
                            }
                        }
                        // DEL - Remove selected item
                        if (event.key === 'Delete' && this.selectedIndex >= 0) {
                            event.preventDefault();
                            this.removeItem(this.selectedIndex);
                            this.selectedIndex = -1;
                        }
                    },

                    formatCurrency(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
                    }
                }
            }
        </script>
    @endpush
</x-layouts.pos>