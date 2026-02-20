<x-layouts.admin :title="'Tambah Mesin Kasir'">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.cash-registers.index') }}"
            class="mr-4 p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Mesin Kasir</h2>
            <p class="text-sm text-gray-500">Mendaftarkan terminal POS baru.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl">
        <form action="{{ route('admin.cash-registers.store') }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama / Lokasi Mesin Kasir
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
                        placeholder="Contoh: Kasir Depan, Komputer 1, Lantai 2">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Keaktifan <span
                            class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif - Bisa digunakan
                            untuk buka sesi</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif
                            (Inactive)</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP Address
                        (Opsional)</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
                        placeholder="Contoh: 192.168.1.100">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin membatasi akses ke IP tertentu.</p>
                    @error('ip_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('admin.cash-registers.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 mr-3">
                    Batal
                </a>
                <button type="submit"
                    class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                    Simpan Kasir
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>