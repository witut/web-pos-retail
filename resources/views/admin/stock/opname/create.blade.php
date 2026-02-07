<x-layouts.admin :title="'Buat Opname Baru'">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Mulai Stock Opname</h2>
            <p class="text-sm text-gray-500">Buat dokumen opname baru untuk memulai perhitungan stok fisik.</p>
        </div>
        <a href="{{ route('admin.stock.opname.index') }}"
            class="text-gray-500 hover:text-gray-700 font-medium text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('admin.stock.opname.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Opname</label>
                    <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" required
                        class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500">
                    <p class="text-xs text-gray-500 mt-1">Tanggal pencatatan stok fisik dilakukan.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Opname rutin bulan Februari, Rak A-C"
                        class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500"></textarea>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi Penting</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>
                                    Saat opname dibuat, sistem akan mengambil data <strong>Stok Saat Ini</strong>
                                    sebagai stok sistem.
                                    Pastikan tidak ada transaksi penjualan aktif saat opname berlangsung untuk akurasi
                                    data.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium transition-colors shadow-sm flex items-center">
                        Snapshot Stok & Mulai Hitung
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>