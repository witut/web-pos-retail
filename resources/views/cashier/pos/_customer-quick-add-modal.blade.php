{{-- Quick Add Customer Modal --}}
<div x-show="showQuickAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @keydown.escape="closeQuickAddModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
        @click.outside="closeQuickAddModal()">
        <div class="px-6 py-4 bg-slate-800 text-white">
            <h3 class="text-lg font-bold">Tambah Pelanggan Baru</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span
                        class="text-red-500">*</span></label>
                <input type="text" x-model="newCustomer.name" x-ref="quickAddNameInput"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span
                        class="text-red-500">*</span></label>
                <input type="text" x-model="newCustomer.phone" placeholder="08123456789"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>
            <div x-show="quickAddError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 text-sm" x-text="quickAddError"></p>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex space-x-3">
            <button @click="closeQuickAddModal()"
                class="flex-1 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                Batal
            </button>
            <button @click="saveNewCustomer()" :disabled="isQuickAdding"
                class="flex-1 py-2 bg-slate-800 text-white font-medium rounded-lg hover:bg-slate-900 disabled:opacity-50">
                <span x-show="!isQuickAdding">Simpan</span>
                <span x-show="isQuickAdding">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>