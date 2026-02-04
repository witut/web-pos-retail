<x-layouts.pos :title="'POS Terminal'">
    <div x-data="posApp()" x-init="init()" class="flex h-full">
        <!-- Left Panel: Shopping Cart (60%) -->
        <div class="w-3/5 flex flex-col bg-white border-r border-gray-200">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Keranjang Belanja</h2>
                <button @click="clearCart()" x-show="cart.length > 0" class="text-sm text-red-600 hover:text-red-800">
                    Kosongkan
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        :class="{ 'ring-2 ring-blue-500': selectedIndex === index }" @click="selectedIndex = index">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800" x-text="item.name"></p>
                            <p class="text-xs text-gray-500" x-text="item.sku"></p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click.stop="decreaseQty(index)"
                                class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                <span class="text-lg font-bold">−</span>
                            </button>
                            <input type="number" x-model.number="item.qty" min="1"
                                @change="updateQty(index, $event.target.value)"
                                class="w-16 text-center border border-gray-300 rounded-lg py-1">
                            <button @click.stop="increaseQty(index)"
                                class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center">
                                <span class="text-lg font-bold">+</span>
                            </button>
                        </div>
                        <div class="w-32 text-right ml-4">
                            <p class="font-semibold text-gray-800" x-text="formatCurrency(item.price * item.qty)"></p>
                            <p class="text-xs text-gray-500" x-text="'@' + formatCurrency(item.price)"></p>
                        </div>
                        <button @click.stop="removeItem(index)"
                            class="ml-3 p-2 text-red-500 hover:bg-red-50 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-lg font-medium">Keranjang Kosong</p>
                    <p class="text-sm">Scan barcode atau cari produk untuk mulai</p>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal (<span x-text="totalItems"></span> item)</span>
                        <span x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>PPN (11%)</span>
                        <span x-text="formatCurrency(tax)"></span>
                    </div>
                    <div class="flex justify-between text-2xl font-bold text-gray-800 pt-2 border-t border-gray-300">
                        <span>TOTAL</span>
                        <span x-text="formatCurrency(total)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Input & Actions (40%) -->
        <div class="w-2/5 flex flex-col bg-gray-50">
            <!-- Barcode Input -->
            <div class="p-4 bg-white border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Scan Barcode / SKU</label>
                <input type="text" x-ref="barcodeInput"
                    @keyup.enter="scanBarcode($event.target.value); $event.target.value = ''"
                    placeholder="Scan atau ketik barcode..."
                    class="w-full px-4 py-3 text-lg border-2 border-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200">
            </div>

            <!-- Product Search -->
            <div class="p-4 bg-white border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Produk</label>
                <input type="text" x-model="searchQuery" @input="searchProducts()" placeholder="Ketik nama produk..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

                <!-- Search Results -->
                <div x-show="searchResults.length > 0"
                    class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white">
                    <template x-for="product in searchResults" :key="product.id">
                        <button @click="addToCart(product); searchQuery = ''; searchResults = []"
                            class="w-full p-3 text-left hover:bg-blue-50 border-b border-gray-100 last:border-0">
                            <p class="font-medium text-gray-800" x-text="product.name"></p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500" x-text="product.sku"></span>
                                <span class="font-semibold text-green-600"
                                    x-text="formatCurrency(product.selling_price)"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="paymentMethod = 'cash'"
                        :class="paymentMethod === 'cash' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="py-3 rounded-lg font-medium transition-colors">
                        Cash
                    </button>
                    <button @click="paymentMethod = 'debit_card'"
                        :class="paymentMethod === 'debit_card' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="py-3 rounded-lg font-medium transition-colors">
                        Debit
                    </button>
                    <button @click="paymentMethod = 'qris'"
                        :class="paymentMethod === 'qris' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="py-3 rounded-lg font-medium transition-colors">
                        QRIS
                    </button>
                </div>
            </div>

            <!-- Cash Input (only for cash) -->
            <div x-show="paymentMethod === 'cash'" class="px-4 pb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima</label>
                <input type="number" x-model.number="amountPaid"
                    class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <div x-show="amountPaid >= total && cart.length > 0" class="mt-2 p-3 bg-green-100 rounded-lg">
                    <p class="text-lg font-bold text-green-800">Kembalian: <span
                            x-text="formatCurrency(amountPaid - total)"></span></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-auto p-4 space-y-3">
                <button @click="checkout()"
                    :disabled="cart.length === 0 || (paymentMethod === 'cash' && amountPaid < total)"
                    class="w-full py-4 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xl font-bold rounded-xl transition-colors">
                    <span class="mr-2">💰</span> BAYAR (F2)
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="showHistory = true"
                        class="py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        📋 Riwayat (F9)
                    </button>
                    <button @click="clearCart()" :disabled="cart.length === 0"
                        class="py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 text-white font-medium rounded-lg">
                        ❌ Batal (ESC)
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Success Modal -->
        <div x-show="showSuccess" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center" @click.away="showSuccess = false">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h3>
                <p class="text-gray-600 mb-4">Invoice: <span class="font-mono font-bold" x-text="lastInvoice"></span>
                </p>
                <div class="flex space-x-3">
                    <button @click="printReceipt()"
                        class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                        🖨️ Cetak Struk
                    </button>
                    <button @click="showSuccess = false; resetTransaction()"
                        class="flex-1 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function posApp() {
                return {
                    cart: [],
                    selectedIndex: -1,
                    searchQuery: '',
                    searchResults: [],
                    paymentMethod: 'cash',
                    amountPaid: 0,
                    showSuccess: false,
                    showHistory: false,
                    lastInvoice: '',

                    init() {
                        this.$refs.barcodeInput.focus();

                        // Keyboard shortcuts
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'F2') { e.preventDefault(); this.checkout(); }
                            if (e.key === 'Escape') { e.preventDefault(); this.clearCart(); }
                            if (e.key === 'Delete' && this.selectedIndex >= 0) {
                                e.preventDefault();
                                this.removeItem(this.selectedIndex);
                            }
                        });
                    },

                    get subtotal() {
                        return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                    },

                    get tax() {
                        return this.subtotal * 0.11;
                    },

                    get total() {
                        return this.subtotal + this.tax;
                    },

                    get totalItems() {
                        return this.cart.reduce((sum, item) => sum + item.qty, 0);
                    },

                    formatCurrency(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
                    },

                    async scanBarcode(barcode) {
                        if (!barcode) return;
                        try {
                            const response = await fetch(`/pos/search-product?barcode=${encodeURIComponent(barcode)}`);
                            const data = await response.json();
                            if (data.product) {
                                this.addToCart(data.product);
                            } else {
                                alert('Produk tidak ditemukan');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                        this.$refs.barcodeInput.focus();
                    },

                    async searchProducts() {
                        if (this.searchQuery.length < 2) {
                            this.searchResults = [];
                            return;
                        }
                        try {
                            const response = await fetch(`/pos/autocomplete?q=${encodeURIComponent(this.searchQuery)}`);
                            this.searchResults = await response.json();
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    },

                    addToCart(product) {
                        const existing = this.cart.find(item => item.id === product.id);
                        if (existing) {
                            existing.qty++;
                        } else {
                            this.cart.push({
                                id: product.id,
                                name: product.name,
                                sku: product.sku,
                                price: product.selling_price,
                                qty: 1
                            });
                        }
                    },

                    removeItem(index) {
                        this.cart.splice(index, 1);
                        this.selectedIndex = -1;
                    },

                    increaseQty(index) {
                        this.cart[index].qty++;
                    },

                    decreaseQty(index) {
                        if (this.cart[index].qty > 1) {
                            this.cart[index].qty--;
                        }
                    },

                    updateQty(index, value) {
                        this.cart[index].qty = Math.max(1, parseInt(value) || 1);
                    },

                    clearCart() {
                        if (this.cart.length > 0 && confirm('Kosongkan keranjang?')) {
                            this.cart = [];
                            this.selectedIndex = -1;
                            this.amountPaid = 0;
                        }
                    },

                    async checkout() {
                        if (this.cart.length === 0) return;
                        if (this.paymentMethod === 'cash' && this.amountPaid < this.total) {
                            alert('Jumlah pembayaran kurang');
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
                                    cart: this.cart,
                                    payment_method: this.paymentMethod,
                                    amount_paid: this.paymentMethod === 'cash' ? this.amountPaid : this.total
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                this.lastInvoice = data.invoice_number;
                                this.showSuccess = true;
                            } else {
                                alert(data.message || 'Gagal memproses pembayaran');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan');
                        }
                    },

                    resetTransaction() {
                        this.cart = [];
                        this.selectedIndex = -1;
                        this.amountPaid = 0;
                        this.paymentMethod = 'cash';
                        this.$refs.barcodeInput.focus();
                    },

                    printReceipt() {
                        window.open(`/pos/print/${this.lastInvoice}`, '_blank');
                    }
                }
            }
        </script>
    @endpush
</x-layouts.pos>