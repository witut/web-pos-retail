<x-layouts.admin :title="'Tambah Pembelian'">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.purchases.index') }}" class="hover:text-gray-700 transition-colors">Daftar Pembelian</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900 font-medium">Tambah Baru</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Pencatatan Pembelian Barang</h2>
            <p class="text-sm text-gray-500">Input stok masuk sekaligus atur status hutang supplier.</p>
        </div>

        <form action="{{ route('admin.purchases.store') }}" method="POST" x-data="purchaseForm()">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form (Items) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Supplier & Date Info -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Supplier <span class="text-red-500">*</span></label>
                                <select name="supplier_id" required
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tanggal Pembelian <span class="text-red-500">*</span></label>
                                <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Item Barang</h3>
                            <button type="button" @click="addItem()"
                                class="px-3 py-1.5 bg-slate-800 text-white rounded text-xs hover:bg-slate-900 transition-colors">
                                + Tambah Produk
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider w-1/3">Produk</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center w-24">Unit/Qty</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right w-32">Harga Beli</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right w-32">Subtotal</th>
                                        <th class="px-2 py-3 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="align-top">
                                            <td class="px-4 py-4">
                                                <div class="relative w-full" x-data="{
                                                    open: false,
                                                    search: '',
                                                    filteredProducts: [],
                                                    get selectedProduct() {
                                                        return productsData.find(p => p.id == item.product_id);
                                                    },
                                                    get displayValue() {
                                                        return this.selectedProduct ? '[' + this.selectedProduct.sku + '] ' + this.selectedProduct.name : this.search;
                                                    },
                                                    init() {
                                                        this.filteredProducts = productsData;
                                                        this.$watch('search', value => {
                                                            if (!value) {
                                                                this.filteredProducts = productsData;
                                                                return;
                                                            }
                                                            const q = value.toLowerCase();
                                                            this.filteredProducts = productsData.filter(p => 
                                                                p.name.toLowerCase().includes(q) || 
                                                                p.sku.toLowerCase().includes(q) ||
                                                                (p.barcodes && p.barcodes.some(b => b.barcode.toLowerCase().includes(q)))
                                                            );
                                                        });
                                                    }
                                                }" @click.away="open = false">
                                                    <!-- Item Reference -->
                                                    <input type="hidden" :name="'items['+index+'][product_id]'" x-model="item.product_id" required>
                                                    
                                                    <!-- Search Input -->
                                                    <input type="text"
                                                           x-bind:value="displayValue"
                                                           @input="search = $event.target.value; item.product_id = ''; open = true; updateProductInfo(index);"
                                                           @focus="open = true; search = ''"
                                                           placeholder="Ketik SKU, Nama, atau Barcode..."
                                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 outline-none mb-2 bg-white cursor-text placeholder-gray-400">
                                                    
                                                    <!-- Dropdown -->
                                                    <div x-show="open" x-cloak
                                                         x-transition.opacity.duration.200ms
                                                         class="absolute z-[60] w-[120%] min-w-[300px] mt-0 bg-white border border-gray-200 shadow-xl rounded-lg max-h-60 overflow-y-auto left-0 top-[calc(100%-8px)]">
                                                        <template x-if="filteredProducts.length === 0">
                                                            <div class="px-4 py-3 text-sm text-gray-500 text-center italic">Produk tidak ditemukan</div>
                                                        </template>
                                                        <template x-for="p in filteredProducts" :key="p.id">
                                                            <div @click="item.product_id = p.id; search = ''; open = false; updateProductInfo(index);"
                                                                 class="px-4 py-2 hover:bg-slate-50 cursor-pointer border-b border-gray-100 last:border-0">
                                                                <div class="font-medium text-sm text-slate-800" x-text="p.name"></div>
                                                                <div class="text-[10px] text-gray-500 flex gap-2">
                                                                    <span x-text="'SKU: ' + p.sku"></span>
                                                                    <span x-show="p.barcodes && p.barcodes.length > 0" x-text="'| Barcode: ' + p.barcodes.map(b=>b.barcode).join(', ')"></span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Conditional: Batch info -->
                                                <div x-show="item.tracking === 'batch'" x-cloak class="grid grid-cols-2 gap-2 mt-2">
                                                    <div>
                                                        <label class="text-[9px] font-bold text-gray-400 uppercase">Batch No.</label>
                                                        <input type="text" :name="'items['+index+'][batch_number]'" placeholder="No Batch"
                                                            class="w-full px-2 py-1 border border-gray-200 rounded text-xs focus:ring-2 focus:ring-slate-500 outline-none">
                                                    </div>
                                                    <div>
                                                        <label class="text-[9px] font-bold text-gray-400 uppercase">Exp. Date</label>
                                                        <input type="date" :name="'items['+index+'][expiry_date]'"
                                                            class="w-full px-2 py-1 border border-gray-200 rounded text-xs focus:ring-2 focus:ring-slate-500 outline-none">
                                                    </div>
                                                </div>

                                                <!-- Conditional: Serial Numbers -->
                                                <div x-show="item.tracking === 'serial'" x-cloak class="mt-2">
                                                    <label class="text-[9px] font-bold text-gray-400 uppercase flex justify-between items-center mb-1">
                                                        <div class="flex items-center gap-1">
                                                            <span>Nomor Seri (Serial No)</span>
                                                            <label class="cursor-pointer text-blue-500 hover:text-blue-700 normal-case font-medium flex items-center gap-0.5">
                                                                (Impor .csv/.txt)
                                                                <input type="file" class="hidden" accept=".csv,.txt" @change="importSerials($event, index)">
                                                            </label>
                                                        </div>
                                                        <span x-text="'Terisi: ' + countSerials(item.serials) + ' / ' + item.qty" 
                                                            :class="countSerials(item.serials) != item.qty ? 'text-orange-500' : 'text-emerald-500'" 
                                                            class="font-bold"></span>
                                                    </label>
                                                    <textarea :name="'items['+index+'][serials]'" rows="2" x-model="item.serials"
                                                        placeholder="Satu baris satu SN (atau pisah koma)"
                                                        class="w-full px-2 py-1 border border-gray-200 rounded text-[10px] focus:ring-2 focus:ring-slate-500 outline-none font-mono"></textarea>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <select :name="'items['+index+'][unit_name]'" x-model="item.unit_name" 
                                                    @change="updateUnitCost(index)" required
                                                    class="w-full px-2 py-1 text-center text-xs text-gray-700 mb-2 border border-gray-200 rounded outline-none focus:ring-1 focus:ring-slate-500 bg-white">
                                                    <template x-for="unit in item.available_units" :key="unit.name">
                                                        <option :value="unit.name" x-text="unit.name"></option>
                                                    </template>
                                                </select>
                                                <input type="number" :name="'items['+index+'][qty]'" x-model.number="item.qty" 
                                                    @input="calculateSubtotal(index)" step="any" min="0.01" required
                                                    class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-slate-500 outline-none font-bold">
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="relative">
                                                     <input type="number" :name="'items['+index+'][cost_per_unit]'" x-model.number="item.cost" 
                                                        @input="calculateSubtotal(index)" required
                                                        class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm text-right focus:ring-2 focus:ring-slate-500 outline-none font-bold pl-8">
                                                     <span class="absolute left-2 top-2 text-[10px] text-gray-400">Rp</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="text-sm font-bold text-slate-800" x-text="'Rp ' + formatIDR(item.subtotal)"></div>
                                            </td>
                                            <td class="px-2 py-4">
                                                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div x-show="items.length === 0" class="py-12 bg-gray-50 text-center text-gray-400 italic text-sm">
                            Klik "Tambah Produk" untuk mulai mencatat pembelian.
                        </div>
                    </div>
                </div>

                <!-- Summary & Payment Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-slate-800 text-white p-6 rounded-xl shadow-lg relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 opacity-10">
                            <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-300 mb-4">Ringkasan Biaya</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-400">Total Belanja</span>
                                <span class="font-bold" x-text="'Rp ' + formatIDR(grandTotal)"></span>
                            </div>
                            <hr class="border-slate-700">
                            <div class="space-y-4 pt-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Jumlah Bayar (Paid)</label>
                                    <div class="relative">
                                         <input type="number" name="paid_amount" x-model.number="paid_amount" required
                                            class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-right font-bold text-lg">
                                         <span class="absolute left-3 top-4 text-xs text-slate-400">Rp</span>
                                    </div>
                                </div>

                                <div class="p-3 bg-red-900/30 rounded-lg border border-red-900/50" x-show="grandTotal - paid_amount > 0" x-cloak>
                                    <div class="flex justify-between items-center">
                                        <div class="text-[10px] font-bold uppercase text-red-200">Total Hutang</div>
                                        <div class="text-lg font-bold text-red-400" x-text="'Rp ' + formatIDR(grandTotal - paid_amount)"></div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-[10px] font-bold text-red-200 uppercase mb-1 italic">Jatuh Tempo Cicilan / Pelunasan</label>
                                        <input type="date" name="due_date" 
                                            class="w-full px-3 py-1.5 bg-slate-750 border border-red-900/50 rounded text-xs text-white focus:ring-2 focus:ring-red-500 outline-none">
                                    </div>
                                </div>

                                <div class="flex justify-center" x-show="grandTotal - paid_amount == 0 && grandTotal > 0" x-cloak>
                                    <span class="px-3 py-1 bg-green-500/20 text-green-400 border border-green-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        Status: Lunas ✅
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Catatan Internal</label>
                        <textarea name="notes" rows="4" placeholder="Contoh: Barang datang via expedisi JTR, box sedikit penyok di pojok."
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none text-sm"></textarea>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" :disabled="items.length === 0"
                            class="w-full py-4 bg-slate-800 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-slate-900 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95">
                            Simpan Transaksi
                        </button>
                        <a href="{{ route('admin.purchases.index') }}" class="text-center text-sm text-gray-500 hover:text-gray-800 font-medium">
                            Batal & Kembali
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Data produk dari backend termasuk unit konversinya
            const productsData = @json($products);

            function purchaseForm() {
                return {
                    items: [],
                    paid_amount: 0,

                    addItem() {
                        this.items.push({
                            product_id: '',
                            qty: 1,
                            unit_name: '',
                            available_units: [],
                            cost: 0,
                            base_cost: 0,
                            subtotal: 0,
                            tracking: 'default',
                            serials: ''
                        });
                    },

                    removeItem(index) {
                        this.items.splice(index, 1);
                    },

                    updateProductInfo(index) {
                        const item = this.items[index];
                        const product = productsData.find(p => p.id == item.product_id);
                        
                        if (product) {
                            item.tracking = product.tracking_type;
                            item.base_cost = parseFloat(product.cost_price || 0);

                            // Load available units (Base unit + konversi)
                            let units = [];
                            units.push({ name: product.base_unit, conversion_rate: 1 });
                            if (product.units && product.units.length > 0) {
                                product.units.forEach(u => {
                                    units.push({ name: u.unit_name, conversion_rate: parseFloat(u.conversion_rate) });
                                });
                            }
                            item.available_units = units;

                            // Set default ke base unit
                            item.unit_name = product.base_unit;
                            
                            // Reset Qty if serial to 1, or keep current if not
                            item.qty = item.tracking === 'serial' ? 1 : (item.qty || 1);
                            
                            this.updateUnitCost(index);
                        } else {
                            item.available_units = [];
                            item.unit_name = '';
                            item.base_cost = 0;
                            item.cost = 0;
                            this.calculateSubtotal(index);
                        }
                    },

                    updateUnitCost(index) {
                        const item = this.items[index];
                        const selectedUnit = item.available_units.find(u => u.name === item.unit_name);
                        
                        if (selectedUnit) {
                            // Hitung default cost untuk unit ini berdasarkan base_cost dan conversion_rate
                            item.cost = item.base_cost * selectedUnit.conversion_rate;
                        }
                        this.calculateSubtotal(index);
                    },

                    calculateSubtotal(index) {
                        const item = this.items[index];
                        item.subtotal = (item.qty || 0) * (item.cost || 0);
                    },

                    importSerials(event, index) {
                        const file = event.target.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const content = e.target.result;
                            // Clean content: split by newlines or commas, trim, and filter empty
                            const lines = content.split(/[\r\n,]+/).map(l => l.trim()).filter(l => l.length > 0);
                            this.items[index].serials = lines.join('\n');
                            this.items[index].qty = lines.length;
                            this.calculateSubtotal(index);
                            
                            // Reset file input
                            event.target.value = '';
                        };
                        reader.readAsText(file);
                    },

                    countSerials(text) {
                        if (!text) return 0;
                        return text.split(/[\n,]+/).filter(s => s.trim().length > 0).length;
                    },

                    get grandTotal() {
                        return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
                    },

                    formatIDR(value) {
                        return new Intl.NumberFormat('id-ID').format(value || 0);
                    }
                }
            }
        </script>
    @endpush
</x-layouts.admin>
