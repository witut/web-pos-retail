<x-layouts.admin :title="'Edit Kategori'">
    <div class="max-w-2xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.categories.index') }}" class="hover:text-gray-700">Kategori</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit: {{ $category->name }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Kategori</h2>
        </div>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Category Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
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
                        @foreach ($categories ?? [] as $cat)
                            @if ($cat->id !== $category->id)
                                {{-- Don't allow selecting itself --}}
                                <option value="{{ $cat->id }}"
                                    {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Tidak dapat memilih kategori ini sendiri sebagai parent</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description', $category->description) }}</textarea>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Info:</strong> Kategori ini memiliki {{ $category->products_count ?? 0 }} produk
                        @if ($category->children_count > 0)
                            dan {{ $category->children_count }} sub-kategori
                        @endif
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.categories.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium">
                        Update Kategori
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
