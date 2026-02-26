<x-layouts.pos :title="'POS Terminal'">
    @include('cashier.pos._session_overlay')
    <div class="h-full flex" x-data="posTerminal()" x-init="initPOS()" @keydown.window="handleKeyboard($event)"
        @points-updated.window="updatePoints($event.detail)" @customer-updated.window="updateCustomer($event.detail)"
        @open-close-register.window="openCloseRegisterModal()"
        @request-cart-total-for-redeem.window="$dispatch('open-redeem-with-cart', taxType === 'inclusive' ? Math.max(0, totalCartValue - promotionDiscount) : Math.round(taxableBase + taxAmount))">
        <!-- Left Panel: Product Search & Cart -->
        <div class="flex-1 flex flex-col p-4 space-y-4">
            <!-- Unified Search Bar: [Qty] [Barcode/Product] | [Customer + Add] -->
            <div class="bg-white rounded-xl shadow-sm p-3 relative">
                <div class="flex items-center gap-2">
                    <!-- Qty Input -->
                    <div class="w-20 flex-shrink-0">
                        <input type="number" x-ref="qtyInput" x-model.number="searchQty" min="1" step="1"
                            @keydown.enter.prevent="$refs.searchInput.focus(); $refs.searchInput.select()"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-center font-semibold text-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                            title="Jumlah item">
                    </div>

                    <!-- Barcode / Product Search -->
                    <div class="flex-1 relative">
                        <input type="text" x-ref="searchInput" x-model="searchQuery"
                            @keydown.enter.prevent="handleSearchEnter()"
                            @keydown.arrow-down.prevent="navigateAutocomplete(1)"
                            @keydown.arrow-up.prevent="navigateAutocomplete(-1)" @keydown.escape="closeAutocomplete()"
                            @input.debounce.300ms="autocomplete()" placeholder="Scan barcode atau cari produk..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 text-base">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Divider -->
                    <div class="w-px h-8 bg-gray-300 flex-shrink-0"></div>

                    <!-- Customer Search (inline) -->
                    <div class="w-56 flex-shrink-0 relative" x-data="customerComponent()"
                        @restore-customer-state.window="selectCustomer($event.detail)"
                        @clear-customer.window="clearCustomer()"
                        @restore-full-state.window="restoreState($event.detail)"
                        @open-redeem-with-cart.window="initRedeemModal($event.detail)">

                        {{-- Inline: Show name if selected, otherwise show search --}}
                        <div x-show="selectedCustomer" class="flex items-center gap-1">
                            <div
                                class="flex-1 flex items-center gap-1.5 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm truncate">
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium text-gray-800 truncate" x-text="selectedCustomer?.name"></span>
                                <span x-show="selectedCustomer?.points_balance > 0"
                                    class="text-xs text-green-600 flex-shrink-0"
                                    x-text="'(' + formatNumber(selectedCustomer?.points_balance) + ' pt)'"></span>
                            </div>
                            <button @click="clearCustomer()"
                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0"
                                title="Hapus pelanggan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div x-show="!selectedCustomer" class="flex items-center gap-1">
                            <div class="flex-1 relative">
                                <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()"
                                    @focus="customerSearchFocused = true"
                                    @keydown.arrow-down.prevent="navigateCustomer(1)"
                                    @keydown.arrow-up.prevent="navigateCustomer(-1)"
                                    @keydown.enter.prevent="selectCurrentCustomer()" placeholder="Pelanggan..."
                                    class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 text-sm">
                                <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            @if($cashierCanCreate ?? true)
                                <button @click="openQuickAddModal()"
                                    class="p-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors flex-shrink-0"
                                    title="Tambah Pelanggan Baru">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Customer Search Dropdown --}}
                        <div x-show="customerResults.length > 0 && customerSearchFocused" x-cloak
                            @click.outside="customerSearchFocused = false"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                            <template x-for="(customer, index) in customerResults" :key="customer.id">
                                <div @click="selectCustomer(customer)"
                                    :class="{'bg-slate-100': index === customerSelectedIndex, 'hover:bg-slate-50': index !== customerSelectedIndex}"
                                    class="px-3 py-2 cursor-pointer border-b border-gray-100 last:border-0">
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

                        {{-- Quick Add Customer Modal --}}
                        @include('cashier.pos._customer-quick-add-modal')
                        {{-- Redeem Points Modal --}}
                        @include('cashier.pos._customer-redeem-modal')
                    </div>
                </div>

                <!-- Autocomplete Dropdown (for products) -->
                <div x-show="autocompleteResults.length > 0" x-cloak
                    class="absolute left-3 right-3 z-50 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden">
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
                <div x-show="searchError" x-cloak class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm" x-text="searchError"></p>
                </div>
            </div>

            {{-- Customer Selected Details (Points, Redeem, etc.) - shown below search bar when customer is selected
            --}}
            @include('cashier.pos._customer-details')

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

                                        <!-- Promo Badge -->
                                        <div x-show="item.promo_name" class="mt-1" x-transition>
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-100 text-pink-700 border border-pink-200 uppercase tracking-wider">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                    </path>
                                                </svg>
                                                <span x-text="item.promo_name"></span>
                                            </span>
                                        </div>

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
                                    <td class="px-4 py-3 text-right text-sm">
                                        <div x-show="item.discount_amount > 0">
                                            <div class="line-through text-gray-400 text-xs"
                                                x-text="formatCurrency(item.selling_price || item.price)"></div>
                                            <div class="font-medium text-green-600"
                                                x-text="formatCurrency((item.selling_price || item.price) - (item.discount_amount / item.qty))">
                                            </div>
                                        </div>
                                        <div x-show="!item.discount_amount"
                                            x-text="formatCurrency(item.selling_price || item.price)"></div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        <div x-show="item.discount_amount > 0">
                                            <div class="line-through text-gray-400 text-xs"
                                                x-text="formatCurrency(item.subtotal)"></div>
                                            <div class="text-green-600"
                                                x-text="formatCurrency(item.subtotal - item.discount_amount)"></div>
                                        </div>
                                        <div x-show="!item.discount_amount" x-text="formatCurrency(item.subtotal)">
                                        </div>
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
                    <div x-show="promotionDiscount > 0" class="flex justify-between text-blue-600">
                        <span>Diskon Promosi</span>
                        <span class="font-medium">-<span x-text="formatCurrency(promotionDiscount)"></span></span>
                    </div>
                    <template x-for="promo in appliedPromotions" :key="promo.name">
                        <div class="flex justify-between text-xs italic px-2"
                            :class="promo.name.includes('Gagal:') ? 'text-red-500' : 'text-blue-500'">
                            <span x-text="promo.name"></span>
                            <span x-text="promo.name.includes('Gagal:') ? 'Ignored' : 'Applied'"></span>
                        </div>
                    </template>
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

                <!-- Coupon Code -->
                <div class="mt-4 pt-2 border-t border-gray-100">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kode Kupon</label>
                    <div class="flex space-x-2">
                        <input type="text" x-model.debounce.500ms="couponCode" placeholder="Masukkan kode..."
                            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                        placeholder="Ketik jumlah..." autocomplete="new-password" name="amount_paid_dummy"
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
                <div class="flex space-x-2">
                    <button type="button" @click="holdTransaction()" :disabled="cart.length === 0"
                        class="flex-1 py-2 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hold (F8)
                        </span>
                    </button>
                    <button type="button" @click="showHoldModal = true"
                        class="flex-1 py-2 bg-slate-600 text-white text-sm font-bold rounded-xl hover:bg-slate-700 transition-colors relative">
                        <span class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daftar Hold
                            <span x-show="heldTransactions.length > 0" x-text="heldTransactions.length"
                                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            </span>
                        </span>
                    </button>
                </div>

                <button type="button" @click="openPaymentModal()" :disabled="cart.length === 0"
                    class="w-full py-3 bg-emerald-600 text-white text-base font-bold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed mt-2">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Checkout / Bayar (F2)
                    </span>
                </button>
                <!-- Action Buttons Grid -->
                <div class="grid grid-cols-3 gap-2 mt-2">
                    <button type="button" @click.prevent="console.log('Batal clicked'); confirmClearCart()"
                        class="py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition-colors cursor-pointer text-sm">
                        Batal (ESC)
                    </button>
                    <button type="button" @click="openReturnModal()"
                        class="py-2 bg-orange-100 text-orange-700 font-medium rounded-lg hover:bg-orange-200 transition-colors cursor-pointer text-sm">
                        Retur (F10)
                    </button>
                    <a href="{{ route('pos.history') }}"
                        class="py-2 flex justify-center items-center bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors text-center text-sm">
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

                        <!-- Error Message -->
                        <div x-show="paymentError" x-cloak
                            class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-red-700 text-sm" x-text="paymentError"></span>
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
                            @input="updateAmountPaid($event.target.value)"
                            @keydown.enter.prevent="$refs.btnProcessPayment.focus()" placeholder="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg text-right focus:ring-2 focus:ring-slate-500">
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex space-x-3">
                    <button x-ref="btnCancelPayment" @click="closePaymentModal()"
                        @keydown.enter.prevent="closePaymentModal()"
                        @keydown.arrow-right.prevent="$refs.btnProcessPayment.focus()"
                        @keydown.arrow-left.prevent="$refs.btnProcessPayment.focus()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors focus:ring-2 focus:ring-slate-400 focus:outline-none">
                        Batal
                    </button>
                    <button x-ref="btnProcessPayment" @click="processCheckout()"
                        @keydown.enter.prevent="processCheckout()"
                        @keydown.arrow-left.prevent="$refs.btnCancelPayment.focus()"
                        @keydown.arrow-right.prevent="$refs.btnCancelPayment.focus()"
                        :disabled="isProcessing || (paymentMethod === 'cash' && change < 0)"
                        class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <span x-show="!isProcessing">Proses Pembayaran</span>
                        <span x-show="isProcessing">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
        <!-- Toast Container -->
        <div class="fixed bottom-4 right-4 z-[70] flex flex-col items-end space-y-2 pointer-events-none">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="true" x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="translate-y-4 opacity-0 scale-95"
                    x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                    x-transition:leave-end="translate-y-4 opacity-0 scale-95"
                    class="max-w-md w-full sm:min-w-[350px] bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                    <div class="p-4 flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Info Icon -->
                            <svg x-show="toast.type === 'info'" class="h-6 w-6 text-blue-400"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg x-show="toast.type === 'error'" class="h-6 w-6 text-red-400"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="toast.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeToast(toast.id)"
                                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
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
                        @keydown.arrow-down.prevent="$refs.btnNewTransaction.focus()"
                        @keydown.arrow-up.prevent="$refs.btnNewTransaction.focus()"
                        @keydown.enter.prevent="printReceipt()"
                        class="w-full py-3 bg-slate-800 text-white font-medium rounded-lg hover:bg-slate-900 transition-colors focus:ring-2 focus:ring-slate-400 focus:outline-none">
                        Cetak Struk
                    </button>
                    <button x-ref="btnNewTransaction" @click="newTransaction()"
                        @keydown.arrow-up.prevent="$refs.printReceiptBtn.focus()"
                        @keydown.arrow-down.prevent="$refs.printReceiptBtn.focus()"
                        @keydown.enter.prevent="newTransaction()"
                        class="w-full py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal (Custom) -->
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
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
        <!-- Hold Name Prompt Modal -->
        <div x-show="showHoldPromptModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.window="if(showHoldPromptModal) showHoldPromptModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden"
                @click.outside="showHoldPromptModal = false">
                <div class="px-6 py-4 bg-amber-500 text-white flex justify-between items-center">
                    <h3 class="font-bold">Simpan Transaksi (Hold)</h3>
                    <button @click="showHoldPromptModal = false" class="text-amber-100 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama/Referensi Hold</label>
                    <input type="text" x-model="holdNameInput" x-ref="holdNameInputField"
                        @keydown.enter.prevent="$refs.btnHoldSave.focus()" placeholder="Cth: Bapak Baju Merah"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 mb-4">
                    <div class="flex space-x-3 mt-2">
                        <button x-ref="btnHoldCancel" @click="showHoldPromptModal = false"
                            @keydown.enter.prevent="showHoldPromptModal = false"
                            @keydown.arrow-right.prevent="$refs.btnHoldSave.focus()"
                            @keydown.arrow-left.prevent="$refs.btnHoldSave.focus()"
                            class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 focus:ring-2 focus:ring-gray-400 focus:outline-none">Batal</button>
                        <button x-ref="btnHoldSave" @click="confirmHoldTransaction()"
                            @keydown.enter.prevent="confirmHoldTransaction()"
                            @keydown.arrow-left.prevent="$refs.btnHoldCancel.focus()"
                            @keydown.arrow-right.prevent="$refs.btnHoldCancel.focus()"
                            class="flex-1 py-2 bg-amber-500 text-white rounded-lg font-bold hover:bg-amber-600 focus:ring-2 focus:ring-amber-400 focus:outline-none">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hold Transactions Modal -->
        <div x-show="showHoldModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.window="if(showHoldModal) showHoldModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[80vh]"
                @click.outside="showHoldModal = false">
                <div class="px-6 py-4 bg-slate-800 text-white flex justify-between items-center flex-shrink-0">
                    <h3 class="font-bold flex items-center text-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Daftar Transaksi Ditahan (Hold)
                    </h3>
                    <button @click="showHoldModal = false" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto custom-scrollbar bg-slate-50 flex-1">
                    <template x-if="heldTransactions.length === 0">
                        <div class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <p class="text-gray-500 font-medium">Tidak ada transaksi yang ditahan.</p>
                        </div>
                    </template>
                    <template x-if="heldTransactions.length > 0">
                        <div class="space-y-3">
                            <template x-for="(held, index) in heldTransactions" :key="held.id">
                                <div
                                    class="bg-white border rounded-xl p-4 flex justify-between items-center shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-800 text-lg" x-text="held.name"></h4>
                                        <div class="flex items-center text-sm text-gray-500 mt-1 space-x-2">
                                            <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg> <span
                                                    x-text="new Date(held.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span></span>
                                            <span>&bull;</span>
                                            <span
                                                class="font-medium bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs"
                                                x-text="held.cart.length + ' item'"></span>
                                            <span>&bull;</span>
                                            <span class="font-bold text-emerald-600"
                                                x-text="formatCurrency(held.totals.final_total)"></span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1.5 bg-gray-50 inline-block px-2 py-1 rounded"
                                            x-show="held.customer_name" x-text="'Pelanggan: ' + held.customer_name"></p>
                                    </div>
                                    <div class="flex space-x-2 ml-4 flex-shrink-0">
                                        <button @click="resumeTransaction(index)"
                                            class="px-4 py-2 bg-emerald-500 text-white font-bold rounded-lg hover:bg-emerald-600 transition-colors shadow-sm flex items-center">
                                            Lanjutkan
                                        </button>
                                        <button @click="deleteHeldTransaction(index)"
                                            class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors border border-red-100"
                                            title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Close Register Modal -->
        <div x-show="showCloseRegisterModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.prevent="closeCloseRegisterModal()">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden flex flex-col max-h-[90vh]"
                @click.outside="closeCloseRegisterModal()">

                <!-- Header -->
                <div class="px-6 py-4 bg-slate-800 text-white flex justify-between items-center">
                    <h3 class="text-xl font-bold">Tutup Register Kasir (Z-Report)</h3>
                    <button @click="closeCloseRegisterModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto bg-gray-50 flex-1">

                    <!-- Loading State -->
                    <div x-show="isLoadingCloseRegister" class="flex flex-col items-center justify-center py-12">
                        <svg class="animate-spin h-10 w-10 text-slate-600 mb-4" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-gray-500">Memuat ringkasan sesi...</p>
                    </div>

                    <!-- Error State -->
                    <div x-show="!isLoadingCloseRegister && closeRegisterError"
                        class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700" x-text="closeRegisterError"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Data Loaded -->
                    <div x-show="!isLoadingCloseRegister && closeRegisterData"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Summary Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Ringkasan Sesi</h3>

                            <dl class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-500">Waktu Buka</dt>
                                    <dd class="text-sm font-medium text-gray-900"
                                        x-text="new Date(closeRegisterData?.session?.opened_at).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})">
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-500">Modal Awal</dt>
                                    <dd class="text-sm font-medium text-gray-900"
                                        x-text="formatCurrency(closeRegisterData?.report?.opening_cash)"></dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-500">Total Penjualan Tunai</dt>
                                    <dd class="text-sm font-bold text-green-600"
                                        x-text="'+ ' + formatCurrency(closeRegisterData?.report?.cash_sales)"></dd>
                                </div>
                                <template x-if="closeRegisterData?.report?.cash_out > 0">
                                    <div class="flex justify-between items-center">
                                        <dt class="text-sm text-gray-500">Pengeluaran Kas</dt>
                                        <dd class="text-sm font-bold text-red-600"
                                            x-text="'- ' + formatCurrency(closeRegisterData?.report?.cash_out)"></dd>
                                    </div>
                                </template>

                                <div class="pt-4 border-t flex justify-between items-center">
                                    <dt class="text-base font-bold text-gray-900">Total Ekspektasi Sistem</dt>
                                    <dd class="text-lg font-bold text-gray-900"
                                        x-text="formatCurrency(closeRegisterData?.report?.expected_cash)"></dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Input Form -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 h-full">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Input Fisik Uang</h3>

                            <form action="{{ route('cashier.shift.update') }}" method="POST" x-ref="closeRegisterForm">
                                @csrf
                                <div class="space-y-6">
                                    <div>
                                        <label for="closing_cash" class="block text-sm font-medium text-gray-700">Total
                                            Uang di Laci</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-lg">Rp</span>
                                            </div>
                                            <input type="text" x-model="closingCashDisplay" @input="formatClosingCash"
                                                required autofocus @keydown.enter.prevent="verifyCloseRegister()"
                                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-lg border-gray-300 rounded-md py-3 font-medium transition-all"
                                                placeholder="0">
                                            <input type="hidden" name="closing_cash" x-model="closingCashValue">
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Hitung semua uang tunai fisik yang ada di
                                            laci kasir.</p>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan
                                            (Opsional)</label>
                                        <textarea name="notes" id="notes" rows="3"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md mt-1"
                                            placeholder="Alasan selisih, dll..."></textarea>
                                    </div>

                                    <div class="pt-4 space-y-3">
                                        <button type="button" @click="verifyCloseRegister()"
                                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-bold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            Tutup Register & Logout
                                        </button>

                                        <button type="button" @click="closeCloseRegisterModal()"
                                            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Modal -->
        <div x-show="showReturnModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @keydown.escape.window="if(showReturnModal) closeReturnModal()">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col mx-4"
                @click.outside="closeReturnModal()">

                <div
                    class="px-6 py-4 bg-orange-600 text-white flex justify-between items-center rounded-t-2xl flex-shrink-0">
                    <h3 class="text-xl font-bold flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z">
                            </path>
                        </svg>
                        Retur Transaksi & Supervisor Override
                    </h3>
                    <button @click="closeReturnModal()" class="text-white hover:text-orange-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">

                    <!-- Search Invoice Form -->
                    <div x-show="!returnTransaction && !returnSuccess" class="space-y-4">
                        <p class="text-gray-600">Masukkan Nomor Invoice pelanggan untuk memproses retur barang langsung
                            dari POS. Dibutuhkan <strong>PIN Admin/Supervisor</strong> untuk menyelesaikan proses.</p>
                        <div class="flex space-x-3 mt-4">
                            <input type="text" x-model="returnSearchInvoice" x-ref="returnInvoiceInput"
                                @keydown.enter="searchReturnInvoice()" placeholder="Contoh: INV/2026/..."
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 uppercase text-lg">
                            <button @click="searchReturnInvoice()" :disabled="isProcessingReturn"
                                class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium disabled:opacity-50 flex items-center">
                                <svg x-show="isProcessingReturn" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Cari Invoice
                            </button>
                        </div>

                        <div x-show="returnError" x-collapse>
                            <div
                                class="mt-4 p-4 items-start bg-red-50 text-red-700 rounded-lg border border-red-200 flex">
                                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="returnError"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div x-show="returnSuccess" x-transition.duration.500ms class="text-center py-10">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h4 class="text-2xl font-bold text-gray-800 mb-2">Retur Berhasil!</h4>
                        <p class="text-gray-600 text-lg" x-text="returnSuccess"></p>
                    </div>

                    <!-- Return Details Form -->
                    <div x-show="returnTransaction && !returnSuccess" x-transition>
                        <div
                            class="bg-gray-50 p-4 rounded-lg mb-6 flex justify-between items-center border border-gray-200">
                            <div>
                                <p class="text-sm text-gray-500">Invoice</p>
                                <p class="font-bold text-gray-800 text-lg" x-text="returnTransaction?.invoice_number">
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Pelanggan</p>
                                <p class="font-medium text-gray-800" x-text="returnTransaction?.customer_name"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Waktu Transaksi</p>
                                <p class="font-medium text-gray-800" x-text="returnTransaction?.transaction_date"></p>
                            </div>
                        </div>

                        <div class="overflow-x-auto border border-gray-200 rounded-xl mb-6 shadow-sm">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-slate-50 text-slate-600 uppercase text-xs font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3 text-right">Harga Nett</th>
                                        <th class="px-4 py-3 text-center">Bisa Diretur</th>
                                        <th class="px-4 py-3 text-center w-36">Qty Retur</th>
                                        <th class="px-4 py-3 text-left w-56">Kondisi Barang</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <template x-for="(item, idx) in returnTransaction?.items" :key="item.id">
                                        <tr class="hover:bg-orange-50/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900" x-text="item.product_name"></div>
                                                <div class="text-xs text-gray-500">Terbeli awal: <span
                                                        x-text="item.original_qty"></span>, Sudah diretur: <span
                                                        x-text="item.returned_qty"></span></div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-slate-700"
                                                x-text="formatCurrency(item.net_price)"></td>
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded"
                                                    x-text="item.max_qty"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    <button type="button"
                                                        @click="if(item.return_qty > 0) item.return_qty--"
                                                        class="px-2 py-1 bg-gray-100 border border-gray-300 rounded-l hover:bg-gray-200">-</button>
                                                    <input type="number" x-model.number="item.return_qty" min="0"
                                                        :max="item.max_qty"
                                                        class="w-full px-2 py-1 border-y border-gray-300 text-center focus:ring-orange-500 focus:border-orange-500 appearance-none m-0">
                                                    <button type="button"
                                                        @click="if(item.return_qty < item.max_qty) item.return_qty++"
                                                        class="px-2 py-1 bg-gray-100 border border-gray-300 rounded-r hover:bg-gray-200">+</button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-left">
                                                <select x-model="item.condition" :disabled="item.return_qty == 0"
                                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100">
                                                    <option value="good">Kembali ke Stok (Good)</option>
                                                    <option value="damaged">Barang Rusak (Damaged)</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Otorisasi Section -->
                        <div class="bg-orange-50 p-5 rounded-xl border border-orange-200 shadow-inner">
                            <h4 class="font-bold text-orange-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                Otorisasi Supervisor
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-orange-900 mb-1">Catatan / Alasan
                                        Retur</label>
                                    <input type="text" x-model="returnReason"
                                        placeholder="Misal: Salah ukuran, cacat pabrik"
                                        class="w-full px-4 py-3 border border-orange-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-orange-900 mb-1">PIN Otorisasi Admin
                                        <span class="text-red-500">*</span></label>
                                    <input type="password" x-model="adminPin" maxlength="6" placeholder="******"
                                        required @keydown.enter="submitReturn()" autocomplete="off"
                                        class="w-full text-center tracking-widest text-2xl font-mono px-4 py-2 border border-orange-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 bg-white placeholder-gray-300">
                                </div>
                            </div>

                            <div x-show="returnError" x-collapse>
                                <div class="mt-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200">
                                    <strong class="font-bold">Gagal!</strong> <span x-text="returnError"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="returnTransaction = null" type="button"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
                                Kembali
                            </button>
                            <button @click="submitReturn()" type="button" :disabled="isProcessingReturn"
                                class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-bold disabled:opacity-50 flex items-center transition-colors shadow-sm">
                                <svg x-show="isProcessingReturn" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Proses Retur
                            </button>
                        </div>
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
                    // Settings
                    pointsMinRedeem: {{ $pointsMinRedeem ?? 100 }},
                    pointsMaxRedeemPercent: {{ $pointsMaxRedeemPercent ?? 90 }},

                    // Customer Data
                    selectedCustomer: null,
                    customerSearch: '',
                    customerResults: [],
                    customerSearchFocused: false,
                    customerSelectedIndex: -1,

                    // Points Management
                    pointsToRedeem: 0,
                    pointsDiscount: 0,
                    tempPointsDiscount: 0,
                    redeemAmount: 0,
                    maxRedeemAllowed: 0,

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
                            this.customerSelectedIndex = -1;
                        } catch (error) {
                            console.error('Customer search error:', error);
                            this.customerResults = [];
                        }
                    },

                    navigateCustomer(direction) {
                        if (this.customerResults.length === 0) return;

                        this.customerSelectedIndex += direction;

                        // Wrap around
                        if (this.customerSelectedIndex < 0) {
                            this.customerSelectedIndex = this.customerResults.length - 1;
                        } else if (this.customerSelectedIndex >= this.customerResults.length) {
                            this.customerSelectedIndex = 0;
                        }
                    },

                    selectCurrentCustomer() {
                        if (this.customerSelectedIndex >= 0 && this.customerResults[this.customerSelectedIndex]) {
                            this.selectCustomer(this.customerResults[this.customerSelectedIndex]);
                        }
                    },

                    selectCustomer(customer) {
                        this.selectedCustomer = customer;
                        this.customerSearch = '';
                        this.customerResults = [];
                        this.customerSearch = '';
                        this.customerResults = [];
                        this.customerSelectedIndex = -1;
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

                    restoreState(detail) {
                        this.selectedCustomer = detail.customer;
                        this.pointsToRedeem = detail.pointsToRedeem || 0;
                        this.pointsDiscount = detail.pointsDiscount || 0;

                        this.$dispatch('points-updated', { discount: this.pointsDiscount, redeem: this.pointsToRedeem });
                        this.$dispatch('customer-updated', { customer: this.selectedCustomer });
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

                    // Called before opening modal, passing subtotal from posTerminal
                    initRedeemModal(cartTotal) {
                        this.showRedeemModal = true;
                        this.redeemError = '';

                        // Calculate max allowed discount based on percentage
                        let maxDiscountValue = cartTotal * (this.pointsMaxRedeemPercent / 100);
                        // Convert max discount to points (Rp 100 = 1 point)
                        this.maxRedeemAllowed = Math.floor(maxDiscountValue / 100);

                        // Auto-fill logic
                        let availablePoints = this.selectedCustomer?.points_balance || 0;

                        if (availablePoints >= this.maxRedeemAllowed) {
                            this.redeemAmount = this.maxRedeemAllowed;
                        } else {
                            this.redeemAmount = availablePoints;
                        }

                        this.calculateRedeemDiscount();
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

                        if ((this.selectedCustomer?.points_balance || 0) < this.pointsMinRedeem) {
                            this.redeemError = `Minimal poin yang bisa ditukar adalah ${this.pointsMinRedeem} Poin`;
                            return;
                        }

                        if (this.redeemAmount > this.maxRedeemAllowed) {
                            this.redeemError = `Titik pemotongan maksimal adalah ${this.maxRedeemAllowed} poin (${this.pointsMaxRedeemPercent}% dari tagihan)`;
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
                    toasts: [],
                    searchQuery: '',
                    searchQty: 1,
                    // Return Session
                    showReturnModal: false,
                    returnSearchInvoice: '',
                    returnTransaction: null,
                    returnError: '',
                    returnSuccess: '',
                    adminPin: '',
                    returnReason: '',
                    isProcessingReturn: false,
                    searchError: '',
                    autocompleteResults: [],
                    autocompleteIndex: -1,
                    cart: [],

                    selectedCartIndex: -1,
                    isUpdatingFromServer: false,
                    taxRate: {{ $taxRate ?? 0 }},
                    taxType: '{{ $taxType ?? 'exclusive' }}',
                    printerSettings: @json($printerSettings ?? []),
                    amountPaid: 0,
                    paymentMethod: 'cash',
                    showPaymentModal: false,
                    paymentError: '',
                    showSuccessModal: false,
                    isProcessing: false,
                    lastInvoice: '',
                    lastTransactionId: null,
                    quickCashAmounts: [10000, 20000, 50000, 100000, 200000, 500000],

                    // Hold Transactions
                    showHoldModal: false,
                    showHoldPromptModal: false,
                    holdNameInput: '',
                    heldTransactions: JSON.parse(localStorage.getItem('pos_held_transactions')) || [],

                    // Promotions
                    promotionDiscount: 0,
                    appliedPromotions: [],
                    couponCode: '',

                    // Custom Confirm Modal
                    showConfirmModal: false,
                    confirmTitle: '',
                    confirmMessage: '',
                    confirmAction: null,

                    // Close Register Modal
                    showCloseRegisterModal: false,
                    closeRegisterData: null,
                    isLoadingCloseRegister: false,
                    closeRegisterError: '',
                    closingCashDisplay: '',
                    closingCashValue: '',

                    openReturnModal() {
                        this.showReturnModal = true;
                        this.returnSearchInvoice = '';
                        this.returnTransaction = null;
                        this.returnError = '';
                        this.returnSuccess = '';
                        this.adminPin = '';
                        this.returnReason = '';
                        this.isProcessingReturn = false;
                        this.$nextTick(() => {
                            if (this.$refs.returnInvoiceInput) this.$refs.returnInvoiceInput.focus();
                        });
                    },
                    closeReturnModal() {
                        this.showReturnModal = false;
                        this.returnTransaction = null;
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) this.$refs.searchInput.focus();
                        });
                    },
                    async searchReturnInvoice() {
                        if (!this.returnSearchInvoice) return;
                        this.isProcessingReturn = true;
                        this.returnError = '';
                        this.returnSuccess = '';

                        try {
                            const response = await fetch(`/pos/transactions/search-invoice?invoice_number=${encodeURIComponent(this.returnSearchInvoice)}`);
                            const data = await response.json();

                            if (data.success) {
                                // Initialize return_qty to 0
                                data.transaction.items = data.transaction.items.map(i => ({
                                    ...i,
                                    return_qty: 0,
                                    condition: 'good'
                                }));
                                this.returnTransaction = data.transaction;
                            } else {
                                this.returnError = data.message || 'Invoice tidak ditemukan atau statusnya tidak valid untuk diretur.';
                                this.returnTransaction = null;
                            }
                        } catch (error) {
                            console.error('Search invoice error:', error);
                            this.returnError = 'Terjadi kesalahan sistem saat mencari invoice. Pastikan format penulisan benar.';
                            this.returnTransaction = null;
                        } finally {
                            this.isProcessingReturn = false;
                        }
                    },
                    async submitReturn() {
                        if (!this.returnTransaction) return;
                        if (this.adminPin.length !== 6) {
                            this.returnError = 'PIN Otorisasi Supervisor harus 6 digit.';
                            return;
                        }

                        // Filter items where return_qty > 0
                        const itemsToReturn = this.returnTransaction.items.filter(i => i.return_qty > 0);
                        if (itemsToReturn.length === 0) {
                            this.returnError = 'Silakan pilih minimal 1 qty untuk barang yang ingin diretur.';
                            return;
                        }

                        this.isProcessingReturn = true;
                        this.returnError = '';

                        try {
                            const payload = {
                                admin_pin: this.adminPin,
                                reason: this.returnReason || 'Retur inisiatif pelanggan dari Kasir POS',
                                refund_method: 'cash',
                                items: itemsToReturn.map(i => ({
                                    id: i.id,
                                    qty: i.return_qty,
                                    condition: i.condition
                                }))
                            };

                            const res = await fetch(`/pos/transactions/${this.returnTransaction.id}/returns-with-pin`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await res.json();

                            if (data.success) {
                                this.returnSuccess = data.message;
                                this.returnTransaction = null; // Hide the form on success
                                this.adminPin = '';

                                setTimeout(() => {
                                    this.closeReturnModal();
                                    // optional: reload page or emit event
                                }, 3500);
                            } else {
                                this.returnError = data.error || 'Gagal memproses retur pembatalan.';
                            }
                        } catch (e) {
                            console.error('Return submit error:', e);
                            this.returnError = 'Terjadi kesalahan sistem server internal saat request.';
                        } finally {
                            this.isProcessingReturn = false;
                        }
                    },

                    openCloseRegisterModal() {
                        this.showCloseRegisterModal = true;
                        this.isLoadingCloseRegister = true;
                        this.closeRegisterData = null;
                        this.closeRegisterError = '';
                        this.closingCashValue = ''; // Reset input
                        this.closingCashDisplay = '';

                        fetch('{{ route("cashier.shift.summary") }}')
                            .then(response => {
                                if (!response.ok) throw new Error('Gagal memuat data sesi');
                                return response.json();
                            })
                            .then(data => {
                                this.closeRegisterData = data;
                                this.isLoadingCloseRegister = false;
                                console.log('Session Summary:', data);
                            })
                            .catch(error => {
                                console.error('Error fetching session summary:', error);
                                this.closeRegisterError = 'Gagal memuat ringkasan sesi. Silakan coba lagi.';
                                this.isLoadingCloseRegister = false;
                            });
                    },

                    closeCloseRegisterModal() {
                        this.showCloseRegisterModal = false;
                        this.closeRegisterData = null;
                    },

                    formatClosingCash(e) {
                        let val = e.target.value.replace(/\D/g, '');
                        this.closingCashValue = val;
                        if (!val) {
                            this.closingCashDisplay = '';
                            return;
                        }
                        this.closingCashDisplay = new Intl.NumberFormat('id-ID').format(val);
                    },

                    verifyCloseRegister() {
                        if (!this.closingCashValue && this.closingCashValue !== '0') {
                            this.closeRegisterError = 'Total uang di laci wajib diisi.';
                            return;
                        }

                        this.confirmTitle = 'Konfirmasi Tutup Register';
                        this.confirmMessage = 'Apakah Anda yakin data sudah benar? Sesi akan ditutup dan Anda akan logout.';
                        this.confirmAction = () => {
                            this.$refs.closeRegisterForm.submit();
                        };
                        this.showConfirmModal = true;
                    },

                    // Points Data (from event)
                    pointsDiscount: 0,
                    pointsToRedeem: 0,
                    customerId: null,
                    currentCustomer: null,

                    // localStorage key for cart persistence
                    storageKey: 'pos_cart_data',

                    // Initialize
                    initPOS() {
                        // Load cart from localStorage
                        this.loadCartFromStorage();

                        // Watch cart changes and save to localStorage
                        this.$watch('cart', (value) => {
                            if (this.isUpdatingFromServer) return;
                            this.saveCartToStorage();
                            this.calculateTotals();
                        }, { deep: true });

                        this.$watch('couponCode', (value) => {
                            this.calculateTotals();
                            this.saveCartToStorage();
                        });

                        // Calculate totals immediately to restore discounts
                        if (this.cart.length > 0) {
                            this.calculateTotals();
                        }

                        this.$refs.searchInput.focus();
                    },

                    // localStorage methods
                    saveCartToStorage() {
                        // Jangan simpan keranjang statis (kosong) ke localStorage. 
                        // Mencegah masalah 'ghost data' dari watcher Alpine.js
                        if (this.cart.length === 0) {
                            try {
                                localStorage.removeItem(this.storageKey);
                            } catch (e) { }
                            return;
                        }

                        try {
                            const data = {
                                cart: this.cart,
                                couponCode: this.couponCode,
                                customer: this.currentCustomer, // Saved from updateCustomer event
                                pointsToRedeem: this.pointsToRedeem,
                                pointsDiscount: this.pointsDiscount,
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

                                if (hoursSinceSave < 24) {
                                    if (data.cart && data.cart.length > 0) {
                                        this.cart = data.cart;
                                        console.log('Cart restored from localStorage:', this.cart.length, 'items');
                                    }

                                    if (data.couponCode) {
                                        this.couponCode = data.couponCode;
                                    }

                                    if (data.customer) {
                                        this.currentCustomer = data.customer;
                                        // Dispatch event to restore customer component UI
                                        // Use setTimeout to ensure component is initialized
                                        setTimeout(() => {
                                            this.$dispatch('restore-full-state', {
                                                customer: data.customer,
                                                pointsToRedeem: data.pointsToRedeem || 0,
                                                pointsDiscount: data.pointsDiscount || 0
                                            });
                                        }, 100);
                                    }
                                }
                            }
                        } catch (e) {
                            console.warn('Failed to load cart from localStorage:', e);
                        }
                    },

                    clearCartStorage() {
                        try {
                            localStorage.removeItem(this.storageKey);

                            // Reset Customer State
                            this.currentCustomer = null;
                            this.customerId = null;
                            this.pointsToRedeem = 0;
                            this.pointsDiscount = 0;

                            // Reset Coupon
                            this.couponCode = '';

                            // Dispatch event to clear customer component
                            this.$dispatch('clear-customer');

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
                    get taxableBase() {
                        return Math.max(0, this.totalCartValue - this.promotionDiscount);
                    },
                    get netDiscountedValue() {
                        return Math.max(0, this.totalCartValue - this.promotionDiscount - this.pointsDiscount);
                    },
                    get subtotal() {
                        // Untuk display di UI (Sebelum pajak khusus untuk tipe eksklusif)
                        if (this.taxType === 'inclusive') {
                            // Jika pajak inklusif, harga subtotal utuh sebelum didiskon dan seolah-olah sudah termasuk pajak 
                            // Untuk UI POS ini, 'subtotal' adalah total cart tanpa tax jika diekstrak, 
                            return Math.round(this.totalCartValue - (this.totalCartValue - (this.totalCartValue / (1 + this.taxRate / 100))));
                        }
                        return Math.round(this.totalCartValue);
                    },
                    get taxAmount() {
                        if (this.taxType === 'inclusive') {
                            // Tax included in price, calculated from taxableBase (NOT reduced by points)
                            return Math.round(this.taxableBase - (this.taxableBase / (1 + this.taxRate / 100)));
                        }
                        // Tax added on top of taxableBase (Points are tender, not pre-tax discount)
                        return Math.round(this.taxableBase * (this.taxRate / 100));
                    },
                    get grandTotal() {
                        if (this.taxType === 'inclusive') {
                            return Math.round(this.totalCartValue);
                        }
                        return Math.round(this.subtotal + Math.round(this.totalCartValue * (this.taxRate / 100)));
                    },
                    get finalTotal() {
                        if (this.taxType === 'inclusive') {
                            return Math.round(this.netDiscountedValue);
                        }
                        return Math.round(this.taxableBase + this.taxAmount - this.pointsDiscount);
                    },
                    get change() {
                        return Math.round(parseInt(this.amountPaid || 0) - this.finalTotal);
                    },

                    // Methods
                    async calculateTotals() {
                        if (this.cart.length === 0) {
                            this.promotionDiscount = 0;
                            this.appliedPromotions = [];
                            return;
                        }

                        // Use local logic for instant feedback until server responds
                        // (Optional, for now just wait for server)

                        try {
                            // Prepare items for server
                            const items = this.cart.map(item => ({
                                id: item.id,
                                qty: item.qty,
                                price: item.selling_price || item.price || 0, // Ensure price field match
                                unit: item.unit
                            }));

                            const response = await fetch('/pos/calculate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    items: items,
                                    coupon_code: this.couponCode
                                })
                            });

                            if (!response.ok) return;

                            const data = await response.json();

                            this.promotionDiscount = parseFloat(data.discount_amount);
                            this.appliedPromotions = data.promotions;

                            // Map discounts back to cart items
                            if (data.items) {
                                this.isUpdatingFromServer = true;
                                this.cart = this.cart.map(localItem => {
                                    const serverItem = data.items.find(i => i.product_id == localItem.id);
                                    if (serverItem) {
                                        // Tentukan nama promo jika ada
                                        let promoName = serverItem.promo_name || null;

                                        // Update discount info
                                        return {
                                            ...localItem,
                                            discount_amount: parseFloat(serverItem.discount_amount),
                                            promo_name: promoName,
                                            // Keep other local props
                                        };
                                    }
                                    return { ...localItem, discount_amount: 0 };
                                });

                                // Reset flag after Alpine processes the update
                                this.$nextTick(() => {
                                    this.isUpdatingFromServer = false;
                                    this.amountPaid = this.finalTotal;
                                });
                            }

                            // Check if coupon was applied
                            if (this.couponCode && data.coupon_code !== this.couponCode && !data.promotions.some(p => p.code === this.couponCode)) {
                                // Coupon invalid or not applied logic if needed
                            }

                        } catch (error) {
                            console.error('Calculation error:', error);
                        }
                    },

                    updatePoints(detail) {
                        this.pointsDiscount = detail.discount || 0;
                        this.pointsToRedeem = detail.redeem || 0;
                        this.amountPaid = this.finalTotal;
                    },
                    updateCustomer(detail) {
                        this.customerId = detail.customer ? detail.customer.id : null;
                        this.currentCustomer = detail.customer; // Store for persistence
                        this.saveCartToStorage();
                    },
                    handleSearchEnter() {
                        // If search is empty, focus qty input
                        if (!this.searchQuery.trim()) {
                            this.$refs.qtyInput.focus();
                            this.$refs.qtyInput.select();
                            return;
                        }
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
                            const addQty = parseInt(this.searchQty) || 1;
                            if (productType === 'service' || (existing.qty + addQty) <= stock) {
                                existing.qty += addQty;
                                existing.subtotal = Math.round(existing.qty * existing.price);
                            } else {
                                this.searchError = 'Stok tidak mencukupi';
                            }
                        } else {
                            const initialQty = parseInt(this.searchQty) || 1;
                            this.cart.push({
                                id: product.id,
                                sku: product.sku,
                                name: product.name,
                                product_type: productType,
                                price: price,
                                qty: initialQty,
                                subtotal: price * initialQty,
                                stock: stock,
                                unit: baseUnit,
                                available_units: availableUnits,
                                conversion: 1, // Base unit conversion is 1
                                showStockError: false // For tooltip
                            });
                        }
                        this.searchQty = 1; // Reset qty input
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
                        if (this.cart.length === 0) return;

                        // Validation
                        this.paymentError = '';
                        if (this.amountPaid < this.finalTotal && this.paymentMethod === 'cash') {
                            this.paymentError = 'Jumlah pembayaran kurang dari total tagihan';
                            return;
                        }

                        this.isProcessing = true;

                        // Auto-focus and select the amount input
                        this.$nextTick(() => {
                            if (this.$refs.amountPaidInput) {
                                this.$refs.amountPaidInput.focus();
                            }
                        });

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
                                points_to_redeem: this.pointsToRedeem,
                                coupon_code: this.couponCode
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
                                this.paymentError = data.error || data.message || 'Gagal memproses pembayaran';
                                this.isProcessing = false;
                                return;
                            }

                            this.lastInvoice = data.invoice_number;
                            this.lastTransactionId = data.transaction_id;

                            // Reset State
                            this.cart = [];
                            this.clearCartStorage();

                            this.closePaymentModal();
                            this.showSuccessModal = true;

                            // Auto-focus print receipt button (and auto-print for ESC/POS)
                            setTimeout(() => {
                                if (this.printerSettings.type === 'escpos') {
                                    this.printReceipt(data.print_payload);
                                }

                                if (this.$refs.printReceiptBtn) {
                                    this.$refs.printReceiptBtn.focus();
                                }
                            }, 150);

                        } catch (error) {
                            console.error('Checkout error:', error);
                            // Show error in modal if open, otherwise alert
                            if (this.showPaymentModal) {
                                this.paymentError = error.message || 'Terjadi kesalahan saat memproses transaksi';
                            } else {
                                alert(error.message || 'Terjadi kesalahan saat memproses transaksi');
                            }
                        } finally {
                            this.isProcessing = false;
                        }
                    },

                    async printReceipt(payload = null) {
                        try {
                            if (this.printerSettings.type === 'escpos') {
                                console.log('Mencetak via Print Server (ESC/POS)...');

                                // Jika tidak ada payload (misal di-klik tombol Reprint), fetch dari server.
                                if (!payload && this.lastTransactionId) {
                                    const res = await fetch(`/pos/transactions/${this.lastTransactionId}/print-payload`);
                                    const d = await res.json();
                                    if (d.success) {
                                        payload = d.data;
                                    } else {
                                        throw new Error("Gagal mengambil data struk");
                                    }
                                }

                                if (payload) {
                                    const printRes = await fetch(this.printerSettings.server_url + '/print', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify(payload)
                                    });
                                    if (!printRes.ok) throw new Error("Print Server tidak merespon (Pastikan Print Server berjalan di " + this.printerSettings.server_url + ")");
                                    console.log("Struk berhasil dikirim ke Print Server");
                                }
                            } else {
                                // Default Browser Print (window.print)
                                if (this.lastTransactionId) {
                                    window.open(`/pos/transaction/${this.lastTransactionId}/print`, '_blank');
                                }
                            }
                        } catch (e) {
                            console.error("Print Error:", e);
                            alert("Gagal mencetak struk: " + e.message);
                        }
                    },

                    newTransaction() {
                        this.cart = [];
                        this.amountPaid = 0;
                        this.paymentMethod = 'cash';
                        this.showSuccessModal = false;

                        // Form Resets
                        this.searchQuery = '';
                        this.couponCode = '';
                        this.promotionDiscount = 0;
                        this.appliedPromotions = [];

                        // Complete Storage Clear and Broadcast
                        this.clearCartStorage();
                        window.dispatchEvent(new CustomEvent('clear-customer'));

                        // Recalculate
                        this.$nextTick(() => {
                            this.calculateTotals();
                            this.$refs.searchInput.focus();
                        });
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
                        // F8 - Hold Transaction
                        if (event.key === 'F8') {
                            event.preventDefault();
                            this.holdTransaction();
                        }
                        // F9 - History
                        if (event.key === 'F9') {
                            event.preventDefault();
                            window.location.href = '/pos/history';
                        }
                        // F10 - Return
                        if (event.key === 'F10') {
                            event.preventDefault();
                            this.openReturnModal();
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

                    // Toast Notification Component Variables
                    showToast(message, type = 'info') {
                        const id = Date.now() + Math.random().toString(36).substr(2, 5);
                        this.toasts.push({ id, message, type });
                        setTimeout(() => {
                            this.removeToast(id);
                        }, 3000);
                    },
                    removeToast(id) {
                        this.toasts = this.toasts.filter(toast => toast.id !== id);
                    },

                    // --- Hold Transactions Logic ---
                    holdTransaction() {
                        if (this.cart.length === 0) return;

                        // Close other modals if any
                        this.showPaymentModal = false;

                        // Setup default name and show modal
                        this.holdNameInput = this.customerName || `Hold ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
                        this.showHoldPromptModal = true;

                        this.$nextTick(() => {
                            if (this.$refs.holdNameInputField) {
                                this.$refs.holdNameInputField.select();
                            }
                        });
                    },

                    confirmHoldTransaction() {
                        if (!this.showHoldPromptModal) return;

                        const holdName = this.holdNameInput.trim() || `Hold ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;

                        const newHold = {
                            id: Date.now(),
                            name: holdName,
                            cart: JSON.parse(JSON.stringify(this.cart)), // Deep copy cart
                            customer_id: this.customerId,
                            customer_name: this.customerName,
                            full_customer_data: this.currentCustomer || null,
                            coupon_code: this.couponCode,
                            points_to_redeem: this.pointsToRedeem,
                            points_discount: this.pointsDiscount,
                            totals: {
                                subtotal: this.subtotal,
                                final_total: this.finalTotal
                            },
                            created_at: new Date().toISOString()
                        };

                        this.heldTransactions.push(newHold);
                        localStorage.setItem('pos_held_transactions', JSON.stringify(this.heldTransactions));

                        // Flash success and clear cart
                        this.showHoldPromptModal = false;
                        this.showToast('Transaksi berhasil ditahan', 'info');
                        this.newTransaction();
                        // bersihkan localstorage cart
                    },

                    resumeTransaction(index) {
                        const holdData = this.heldTransactions[index];
                        if (!holdData) return;

                        const executeResume = () => {
                            // Load data
                            this.cart = holdData.cart;

                            if (holdData.full_customer_data) {
                                window.dispatchEvent(new CustomEvent('restore-full-state', {
                                    detail: {
                                        customer: holdData.full_customer_data,
                                        pointsToRedeem: holdData.points_to_redeem || 0,
                                        pointsDiscount: holdData.points_discount || 0
                                    }
                                }));
                            } else {
                                window.dispatchEvent(new CustomEvent('clear-customer'));
                            }

                            this.couponCode = holdData.coupon_code || '';

                            // Recalculate and update storage after Alpine state settles
                            this.$nextTick(() => {
                                this.calculateTotals();
                                this.saveCartToStorage();
                            });

                            // Remove from Hold list without re-confirming
                            this.heldTransactions.splice(index, 1);
                            localStorage.setItem('pos_held_transactions', JSON.stringify(this.heldTransactions));
                            this.showHoldModal = false;
                            this.showToast('Transaksi berhasil dilanjutkan', 'success');
                        };

                        // Warn if current cart is not empty
                        if (this.cart.length > 0) {
                            this.showConfirm(
                                'Timpa Keranjang?',
                                'Keranjang saat ini tidak kosong. Ingin menimpa dengan transaksi yang ditahan? (Transaksi saat ini akan hilang)',
                                executeResume
                            );
                        } else {
                            executeResume();
                        }
                    },

                    deleteHeldTransaction(index) {
                        this.showConfirm(
                            'Hapus Transaksi',
                            'Yakin ingin menghapus transaksi tertunda ini selamanya?',
                            () => {
                                this.heldTransactions.splice(index, 1);
                                localStorage.setItem('pos_held_transactions', JSON.stringify(this.heldTransactions));
                                this.showToast('Transaksi tertunda berhasil dihapus', 'info');
                            }
                        );
                    },

                    formatDate(isoString) {
                        return new Date(isoString).toLocaleString('id-ID', {
                            day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                        });
                    },
                    // ----------------------------

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