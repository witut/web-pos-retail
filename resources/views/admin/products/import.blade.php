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
                <p class="text-yellow-800 font-medium">⚠️ {{ session('warning') }}</p>

                {{-- Download Error File --}}
                @if (session('has_error_file') || \Illuminate\Support\Facades\Session::has('error_file_path'))
                    <div class="mt-3">
                        <a href="{{ route('admin.products.import.download-errors') }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download File Error (.xlsx)
                        </a>
                        <p class="text-xs text-yellow-700 mt-1">
                            File berisi baris-baris yang gagal beserta keterangan errornya. Perbaiki lalu import ulang.
                        </p>
                    </div>
                @endif

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
                <li>Isi data produk sesuai kolom yang tersedia (<strong>14 kolom</strong>)</li>
                <li>Kolom wajib: <strong>SKU, Nama Produk, Harga Jual, Unit Dasar</strong></li>
                <li>Product Type: <strong>inventory</strong> (barang fisik) atau <strong>service</strong> (jasa)</li>
                <li>Kolom <strong>Konversi</strong>: isi <strong>1</strong> untuk unit dasar (PCS), isi angka lebih besar untuk unit besar (RTG=10, BOX=24, dll)</li>
                <li>Untuk menambahkan <strong>multi-unit (UOM)</strong>: tambahkan baris baru dengan SKU yang sama, isi Konversi dan Unit yang berbeda</li>
                <li>Kategori akan dibuat otomatis jika belum ada</li>
                <li>Upload file yang sudah diisi</li>
            </ol>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <p class="text-sm text-blue-900">
                        <strong>📦 Contoh Multi-Unit:</strong><br>
                        Baris 1: SKU=PROD-001 | Konversi=<strong>1</strong> | Unit=<strong>PCS</strong> | Harga=2.000<br>
                        Baris 2: SKU=PROD-001 | Konversi=<strong>10</strong> | Unit=<strong>RTG</strong> | Harga=19.000
                    </p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg">
                    <p class="text-sm text-amber-900">
                        <strong>⚠️ Hasil Summary:</strong><br>
                        "Produk baru", "Diupdate", <strong>"Unit ditambahkan"</strong>, dan "Gagal" akan dilaporkan setelah import selesai.
                    </p>
                </div>
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
            <h3 class="text-lg font-semibold text-gray-800 mb-3">ℹ️ Aturan Import</h3>
            <ul class="space-y-2 text-gray-700">
                <li>• Harga Jual harus lebih besar atau sama dengan Harga Pokok</li>
                <li>• Baris dengan SKU yang sama + Konversi & Unit identik → <strong>dilaporkan sebagai duplikat</strong> (dilewati)</li>
                <li>• Baris dengan SKU yang sama + Konversi atau Unit berbeda → <strong>ditambahkan sebagai UOM baru</strong></li>
                <li>• Konflik (misal: nama unit sama tapi konversi beda) → <strong>dilaporkan sebagai error konflik</strong>, harus edit manual</li>
                <li>• Baris yang error akan di-skip, baris valid tetap diimport</li>
                <li>• Barcode akan ditambahkan ke produk (jika diisi dan belum ada)</li>
                <li>• Status default: <strong>active</strong></li>
                <li>• Konversi default: <strong>1</strong> (satu unit dasar)</li>
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