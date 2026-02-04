<x-layouts.admin :title="'Edit Supplier'">
    <div class="max-w-3xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.suppliers.index') }}" class="hover:text-gray-700">Supplier</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit: {{ $supplier->name }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Supplier</h2>
        </div>

        <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supplier Code (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kode Supplier
                            </label>
                            <input type="text" value="{{ $supplier->code }}" readonly
                                class="w-full px-4 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-600">
                            <p class="text-xs text-gray-500 mt-1">Kode tidak dapat diubah</p>
                        </div>

                        <!-- Supplier Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Perusahaan
                            </label>
                            <input type="text" name="company" value="{{ old('company', $supplier->company) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kontak</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                                placeholder="supplier@example.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea name="address" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('address', $supplier->address) }}</textarea>
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                            <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Postal Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                            <input type="text" name="postal_code"
                                value="{{ old('postal_code', $supplier->postal_code) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Tambahan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- PIC Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC (Person In
                                Charge)</label>
                            <input type="text" name="pic_name" value="{{ old('pic_name', $supplier->pic_name) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- PIC Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon PIC</label>
                            <input type="text" name="pic_phone" value="{{ old('pic_phone', $supplier->pic_phone) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea name="notes" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes', $supplier->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.suppliers.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Update Supplier
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>