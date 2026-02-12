<x-layouts.admin :title="'Import Produk'">
    <div class="max-w-4xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.products.index') }}" class="hover:text-gray-700">Produk</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Import</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Import Produk dari Excel/CSV</h2>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800 font-medium">{{ session('warning') }}</p>

                @if (session('import_errors'))
                    <div class="mt-4">
                        <button type="button" onclick="toggleErrors()"
                            class="text-sm text-yellow-700 underline hover:text-yellow-900">
                            Lihat Detail Error ({{ count(session('import_errors')) }} baris)
                        </button>

                        <div id="errorDetails" class="hidden mt-4 max-h-64 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-yellow-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Baris</th>
                                        <th class="px-3 py-2 text-left">SKU</th>
                                        <th class="px-3 py-2 text-left">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-yellow-200">
                                    @foreach (session('import_errors') as $error)
                                        <tr>
                                            <td class="px-3 py-2">{{ $error['row'] }}</td>
                                            <td class="px-3 py-2">{{ $error['sku'] }}</td>
                                            <td class="px-3 py-2">
                                                @foreach ($error['errors'] as $msg)
                                                    <div>• {{ $msg }}</div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">📋 Petunjuk Import</h3>
            <ol class="list-decimal list-inside space-y-2 text-blue-800">
                <li>Download template Excel dengan klik tombol "Download Template" di bawah</li>
                <li>Isi data produk sesuai kolom yang tersedia</li>
                <li>Kolom wajib: <strong>SKU, Nama Produk, Harga Jual</strong></li>
                <li>Product Type: <strong>inventory</strong> (barang fisik) atau <strong>service</strong> (jasa)</li>
                <li>Jika SKU sudah ada, data akan di-<strong>update</strong></li>
                <li>Kategori akan dibuat otomatis jika belum ada</li>
                <li>Upload file yang sudah diisi</li>
            </ol>

            <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                <p class="text-sm text-blue-900">
                    <strong>💡 Tips:</strong> Untuk produk <strong>service</strong>, kolom Stok Awal dan Min Stock Alert
                    bisa dikosongkan.
                </p>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload File</h3>

            <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <!-- Download Template Button -->
                    <div>
                        <a href="{{ route('admin.products.import.template') }}"
                            class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Template
                        </a>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih File Excel/CSV <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror">
                        @error('file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max: 5MB)</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center space-x-3">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Import Produk
                        </button>
                        <a href="{{ route('admin.products.index') }}"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">ℹ️ Informasi Tambahan</h3>
            <ul class="space-y-2 text-gray-700">
                <li>• Harga Jual harus lebih besar atau sama dengan Harga Pokok</li>
                <li>• Produk dengan SKU yang sama akan di-update, bukan dibuat duplikat</li>
                <li>• Baris yang error akan di-skip, baris valid tetap diimport</li>
                <li>• Barcode akan ditambahkan ke produk (jika diisi)</li>
                <li>• Status default: <strong>active</strong></li>
                <li>• Unit Dasar default: <strong>PCS</strong></li>
            </ul>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleErrors() {
                const errorDetails = document.getElementById('errorDetails');
                errorDetails.classList.toggle('hidden');
            }
        </script>
    @endpush
</x-layouts.admin>