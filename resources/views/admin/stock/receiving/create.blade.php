<x-layouts.admin :title="'Terima Barang'">
    <div class="max-w-6xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.stock.receiving.index') }}" class="hover:text-gray-700">Penerimaan Stok</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Terima Barang Baru</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Terima Barang</h2>
        </div>

        <form method="POST" action="{{ route('admin.stock.receiving.store') }}" x-data="receivingForm()">
            @csrf

            <div class="space-y-6">
                <!-- Header Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penerimaan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Receiving Number (Auto) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                No. Penerimaan
                            </label>
                            <input type="text" value="{{ $receivingNumber }}" readonly
                                class="w-full px-4 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-600 font-mono">
                            <p class="text-xs text-gray-500 mt-1">Auto-generated</p>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <select name="supplier_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 @error('supplier_id') border-red-500 @enderror">
                                <option value="">Pilih Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Invoice Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                No. Invoice/DO Supplier
                            </label>
                            <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                placeholder="Opsional"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        </div>

                        <!-- Receiving Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Terima <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="receiving_date" value="{{ old('receiving_date', date('Y-m-d')) }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 @error('receiving_date') border-red-500 @enderror">
                            @error('receiving_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Item Barang</h3>
                            <p class="text-sm text-gray-500">Tambahkan produk yang diterima</p>
                        </div>
                        <button type="button" @click="addItem()"
                            class="px-4 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-900 transition-colors">
                            + Tambah Item
                        </button>
                    </div>

                    @error('items')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-2/5">
                                        Produk</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">
                                        Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-28">
                                        Satuan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-36">
                                        Harga/Unit</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-36">
                                        Subtotal</th>
                                    <th class="px-4 py-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id"
                                                @change="updateProductUnit(index)" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 text-sm">
                                                <option value="">Pilih Produk</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-unit="{{ $product->base_unit }}"
                                                        data-name="{{ $product->name }}">
                                                        {{ $product->sku }} - {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" :name="'items[' + index + '][qty]'"
                                                x-model.number="item.qty" @input="calculateSubtotal(index)" required
                                                step="0.01" min="0.01"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 text-sm text-center">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'items[' + index + '][unit_name]'"
                                                x-model="item.unit_name" required maxlength="20"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 text-sm text-center">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" :name="'items[' + index + '][cost_per_unit]'"
                                                x-model.number="item.cost_per_unit" @input="calculateSubtotal(index)"
                                                required step="1" min="0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 text-sm text-right">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm font-medium text-gray-900"
                                                x-text="formatCurrency(item.subtotal)"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button type="button" @click="removeItem(index)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700">
                                        Total Penerimaan:
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-lg text-slate-800"
                                        x-text="formatCurrency(grandTotal)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div x-show="items.length === 0" class="py-8 text-center text-gray-500">
                        Klik "Tambah Item" untuk menambahkan produk.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.stock.receiving.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" :disabled="items.length === 0"
                        class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function receivingForm() {
                return {
                    items: [],

                    addItem() {
                        this.items.push({
                            product_id: '',
                            qty: 1,
                            unit_name: '',
                            cost_per_unit: 0,
                            subtotal: 0
                        });
                    },

                    removeItem(index) {
                        this.items.splice(index, 1);
                    },

                    updateProductUnit(index) {
                        const select = document.querySelector(`select[name="items[${index}][product_id]"]`);
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.dataset.unit) {
                            this.items[index].unit_name = selectedOption.dataset.unit;
                        }
                    },

                    calculateSubtotal(index) {
                        const item = this.items[index];
                        item.subtotal = (item.qty || 0) * (item.cost_per_unit || 0);
                    },

                    get grandTotal() {
                        return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
                    },

                    formatCurrency(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
                    }
                }
            }
        </script>
    @endpush
</x-layouts.admin>