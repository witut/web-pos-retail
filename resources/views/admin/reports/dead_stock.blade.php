<x-layouts.admin :title="'Laporan Stok Pasif (Dead Stock)'">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Stok Pasif</h2>
            <p class="text-sm text-gray-500">Produk tanpa penjualan dalam {{ $days }} hari terakhir.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()"
                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <form action="{{ route('admin.reports.dead_stock') }}" method="GET" class="flex items-end gap-4 max-w-lg">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rentang Waktu (Hari)</label>
                <select name="days" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Tanpa Penjualan</option>
                    <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 Hari Tanpa Penjualan</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Hari Tanpa Penjualan</option>
                    <option value="180" {{ $days == 180 ? 'selected' : '' }}>180 Hari (6 Bulan)</option>
                    <option value="365" {{ $days == 365 ? 'selected' : '' }}>365 Hari (1 Tahun)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Alert Advice -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 flex items-start">
        <svg class="w-6 h-6 text-yellow-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="font-bold text-yellow-800">Saran Tindakan</h4>
            <p class="text-sm text-yellow-700 mt-1">
                Produk di bawah ini tidak terjual dalam {{ $days }} hari terakhir namun masih memiliki stok.
                Pertimbangkan untuk melakukan <strong>Promosi / Diskon</strong> (Cuci Gudang) untuk mencairkan modal,
                atau evaluasi ulang sebelum melakukan pembelian stok baru (Restock) untuk item ini.
            </p>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Daftar Produk Stok Pasif</h3>
            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">{{ $products->count() }}
                Item Ditemukan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3 text-right">Sisa Stok</th>
                        <th class="px-6 py-3 text-right">Harga Modal</th>
                        <th class="px-6 py-3 text-right">Nilai Aset Tertahan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $product->category->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-red-600">
                                {{ $product->stock_on_hand }} {{ $product->unit }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                Rp {{ number_format($product->stock_on_hand * $product->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Edit / Diskon
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <p class="font-medium">Tidak ada stok pasif!</p>
                                <p class="text-sm text-gray-400 mt-1">Semua produk Anda bergerak atau tidak memiliki stok.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .shadow-sm {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
        }
    </style>
</x-layouts.admin>