<x-layouts.admin :title="'Tambah Kategori'">
    <div class="max-w-2xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.categories.index') }}" class="hover:text-gray-700">Kategori</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Tambah Kategori</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Kategori Baru</h2>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Category Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori Induk (Optional)
                    </label>
                    <select name="parent_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('parent_id') border-red-500 @enderror">
                        <option value="">- Root Category -</option>
                        @foreach ($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk kategori utama</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.categories.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium">
                        Simpan Kategori
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>