function posTerminal() {
    return {
        // Data
        searchQuery: '',
        searchError: '',
        autocompleteResults: [],
        autocompleteIndex: -1,
        cart: [],
        selectedCartIndex: -1,
        taxRate: 11,
        taxType: 'exclusive',
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
            return Math.round(parseInt(this.amountPaid || 0) - this.grandTotal);
        },

        // Methods
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
            if (this.amountPaid < this.grandTotal && this.paymentMethod === 'cash') {
                this.amountPaid = this.grandTotal;
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
                            : this.grandTotal
                    },
                    customer_id: window.Alpine?.$data(document.querySelector('[x-data=\'customerComponent()\']'))?.selectedCustomer?.id || null,
                    points_to_redeem: window.Alpine?.$data(document.querySelector('[x-data=\'customerComponent()\']'))?.pointsToRedeem || 0
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
    }
}

// Customer Component for POS
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
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.pointsToRedeem = 0;
            this.pointsDiscount = 0;
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

                // Auto-select newly created customer
                this.selectedCustomer = data.customer;
                this.closeQuickAddModal();

            } catch (error) {
                this.quickAddError = 'Terjadi kesalahan. Silakan coba lagi.';
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
