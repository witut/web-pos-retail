<x-layouts.admin :title="'Edit Promosi'">
    <div class="max-w-4xl">
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.promotions.index') }}" class="hover:text-gray-700">Promosi</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit Promosi</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Promosi: {{ $promotion->name }}</h2>
        </div>

        <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" x-data="promotionForm()">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promosi <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $promotion->name) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $promotion->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Promosi <span class="text-red-500">*</span></label>
                        <select name="type" x-model="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed_amount">Potongan Tetap (Rp)</option>
                            <!-- Future: buy_x_get_y, bundle -->
                        </select>
                    </div>

                    <div x-show="type === 'percentage'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Persentase Diskon (%) <span class="text-red-500">*</span></label>
                        <input type="number" name="value" x-model="percentageValue" :disabled="type !== 'percentage'" step="0.01" min="0" max="100" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div x-show="type === 'fixed_amount'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Potongan (Rp) <span class="text-red-500">*</span></label>
                         <!-- Hidden input for the actual numeric value sent to backend -->
                        <input type="hidden" name="value" :value="fixedValue" :disabled="type !== 'fixed_amount'">
                        
                        <!-- Visible text input for formatting -->
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">Rp</span>
                            <input type="text" x-model="formattedFixedValue" @input="formatInput" :disabled="type !== 'fixed_amount'"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t border-gray-100 pt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', $promotion->start_date->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berakhir</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $promotion->end_date ? $promotion->end_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku selamanya</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Pembelian (Rp)</label>
                        <input type="number" name="min_purchase" value="{{ old('min_purchase', $promotion->min_purchase) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-center mt-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Aktifkan Promosi Ini</span>
                        </label>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Pilih Produk (Opsional)</h3>
                    <p class="text-sm text-gray-500 mb-4">Pilih produk spesifik yang dikenakan diskon ini. Jika kosong, berlaku untuk semua produk (berdasarkan minimum pembelian).</p>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <input type="text" x-model="search" placeholder="Cari produk..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-blue-500">
                        
                        <div class="max-h-60 overflow-y-auto space-y-2">
                            @foreach($products as $product)
                                <label class="flex items-center space-x-3 p-2 hover:bg-white rounded cursor-pointer" 
                                    x-show="matchesSearch('{{ strtolower($product->name) }}', '{{ strtolower($product->sku) }}')">
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                        {{ (old('product_ids') && in_array($product->id, old('product_ids'))) || $promotion->products->contains($product->id) ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->sku }} | Stok: {{ $product->stock_on_hand }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.promotions.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function promotionForm() {
                return {
                    type: '{{ old('type', $promotion->type) }}',
                    search: '',
                    percentageValue: '{{ old('type', $promotion->type) == 'percentage' ? old('value', $promotion->value) : '' }}',
                    fixedValue: '{{ old('type', $promotion->type) == 'fixed_amount' ? old('value', $promotion->value) : '' }}',
                    formattedFixedValue: '',

                    init() {
                         if (this.fixedValue) {
                             this.formattedFixedValue = new Intl.NumberFormat('id-ID').format(this.fixedValue);
                         }
                    },

                    formatInput(e) {
                         // Remove non-numeric characters
                        let value = e.target.value.replace(/\D/g, '');
                        this.fixedValue = value;
                        this.formattedFixedValue = value ? new Intl.NumberFormat('id-ID').format(value) : '';
                    },

                    matchesSearch(name, sku) {
                        const query = this.search.toLowerCase();
                        return name.includes(query) || sku.includes(query);
                    }
                }
            }
        </script>
    @endpush
</x-layouts.admin>
