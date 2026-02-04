<x-layouts.admin :title="'Edit Produk'">
    <div class="max-w-5xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.products.index') }}" class="hover:text-gray-700">Produk</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit: {{ $product->name }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Produk</h2>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
            x-data="productEditForm()">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Produk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SKU <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('sku') border-red-500 @enderror">
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active"
                                    {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive"
                                    {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Harga & Stok</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Base Unit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Satuan Dasar <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="base_unit" value="{{ old('base_unit', $product->base_unit) }}"
                                required placeholder="pcs, kg, liter, dll"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('base_unit') border-red-500 @enderror">
                            @error('base_unit')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="selling_price"
                                value="{{ old('selling_price', $product->selling_price) }}" required step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('selling_price') border-red-500 @enderror">
                            @error('selling_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Stock (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Stok Saat Ini
                            </label>
                            <input type="text" value="{{ number_format($product->stock_on_hand, 2) }}" readonly
                                class="w-full px-4 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Update via penerimaan/penjualan</p>
                        </div>

                        <!-- Min Stock Alert -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Minimum Stok Alert
                            </label>
                            <input type="number" name="min_stock_alert"
                                value="{{ old('min_stock_alert', $product->min_stock_alert) }}" step="0.01" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Cost Price (HPP - Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                HPP (Weighted Average)
                            </label>
                            <input type="text" value="Rp {{ number_format($product->cost_price, 0, ',', '.') }}"
                                readonly class="w-full px-4 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Auto-calculate dari penerimaan</p>
                        </div>

                        <!-- Tax Rate -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                PPN (%)
                            </label>
                            <input type="number" name="tax_rate" value="{{ old('tax_rate', $product->tax_rate) }}"
                                step="0.01" min="0" max="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Barcodes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Barcode</h3>
                        <button type="button" @click="addBarcode()"
                            class="px-3 py-1 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-900">
                            + Tambah Barcode
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(barcode, index) in barcodes" :key="index">
                            <div class="flex items-center space-x-3">
                                <input type="hidden" :name="'barcodes[' + index + '][id]'" x-model="barcode.id">
                                <input type="text" :name="'barcodes[' + index + '][code]'" x-model="barcode.code"
                                    placeholder="Masukkan barcode" required
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <label class="flex items-center">
                                    <input type="checkbox" :name="'barcodes[' + index + '][is_primary]'"
                                        x-model="barcode.is_primary" @change="setPrimary(index)"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-600">Primary</span>
                                </label>
                                <button type="button" @click="removeBarcode(index)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div x-show="barcodes.length === 0" class="text-center py-8 text-gray-500">
                            Belum ada barcode. Klik "Tambah Barcode" untuk menambahkan.
                        </div>
                    </div>
                </div>

                <!-- Product Units (UOM) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Unit of Measure (UOM)</h3>
                            <p class="text-sm text-gray-500">Satuan penjualan selain satuan dasar</p>
                        </div>
                        <button type="button" @click="addUnit()"
                            class="px-3 py-1 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-900">
                            + Tambah Unit
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(unit, index) in units" :key="index">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <input type="hidden" :name="'units[' + index + '][id]'" x-model="unit.id">
                                <div class="col-span-4">
                                    <input type="text" :name="'units[' + index + '][name]'" x-model="unit.name"
                                        placeholder="Nama unit (Box, Karton)" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="col-span-3">
                                    <input type="number" :name="'units[' + index + '][conversion_rate]'"
                                        x-model="unit.conversion_rate" placeholder="Konversi (12)" required step="0.01"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="col-span-4">
                                    <input type="number" :name="'units[' + index + '][selling_price]'"
                                        x-model="unit.selling_price" placeholder="Harga jual" required step="0.01"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="col-span-1">
                                    <button type="button" @click="removeUnit(index)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="units.length === 0" class="text-center py-8 text-gray-500">
                            Belum ada unit tambahan. Klik "Tambah Unit" untuk menambahkan.
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Gambar Produk</h3>

                    <div class="flex items-start space-x-6">
                        <div class="flex-shrink-0">
                            <div
                                class="w-48 h-48 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden">
                                <template x-if="!imagePreview && !currentImage">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 mt-2">Tidak ada gambar</p>
                                    </div>
                                </template>
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview && currentImage">
                                    <img :src="currentImage" alt="Current" class="w-full h-full object-cover">
                                </template>
                            </div>
                        </div>

                        <div class="flex-1">
                            <label
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Ganti Gambar
                                <input type="file" name="image" accept="image/*" class="hidden"
                                    @change="previewImage($event)">
                            </label>
                            <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, GIF (max 2MB)</p>
                            <button type="button" x-show="imagePreview || currentImage" @click="clearImage()"
                                class="text-sm text-red-600 hover:text-red-800 mt-2">
                                Hapus Gambar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.products.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium">
                        Update Produk
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function productEditForm() {
                return {
                    barcodes: @json($barcodesData),
                    units: @json($unitsData),
                    imagePreview: null,
                    currentImage: '{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}',

                    addBarcode() {
                        this.barcodes.push({
                            id: null,
                            code: '',
                            is_primary: this.barcodes.length === 0
                        });
                    },

                    removeBarcode(index) {
                        this.barcodes.splice(index, 1);
                    },

                    setPrimary(index) {
                        this.barcodes.forEach((barcode, i) => {
                            barcode.is_primary = i === index;
                        });
                    },

                    addUnit() {
                        this.units.push({
                            id: null,
                            name: '',
                            conversion_rate: '',
                            selling_price: ''
                        });
                    },

                    removeUnit(index) {
                        this.units.splice(index, 1);
                    },

                    previewImage(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.imagePreview = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    },

                    clearImage() {
                        this.imagePreview = null;
                        this.currentImage = null;
                        document.querySelector('input[type="file"]').value = '';
                    }
                }
            }
        </script>
    @endpush
</x-layouts.admin>
