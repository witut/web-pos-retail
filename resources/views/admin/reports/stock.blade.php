<x-layouts.admin :title="'Laporan Nilai Stok'">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Valuasi Stok</h2>
            <p class="text-sm text-gray-500">Posisi Stok per: {{ now()->format('d M Y H:i') }}</p>
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
        <div class="flex flex-col md:flex-row gap-3">
            <form action="{{ route('admin.reports.stock') }}" method="GET"
                class="flex flex-wrap gap-2 items-center flex-1">
                <select name="category_id"
                    class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $selectedCategory == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                    Filter
                </button>
            </form>

            <div x-data="{ open: false }" class="relative shrink-0">
                <button @click="open = !open"
                    class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-1.5 rounded-md text-sm font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute text-left right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                    x-cloak>
                    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'stock', 'format' => 'excel'])) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Excel</a>
                    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'stock', 'format' => 'pdf'])) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Nilai Aset</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">Rp
                {{ number_format($summary['total_value'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-2">Berdasarkan Harga Modal (HPP)</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Item Fisik</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_qty'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-2">Unit</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Jenis Produk</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_products']) }}</p>
            <p class="text-xs text-gray-400 mt-2">SKU Aktif</p>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Detail Stok Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3 text-right">Stok Fisik</th>
                        <th class="px-6 py-3 text-right">Harga Modal (Avg)</th>
                        <th class="px-6 py-3 text-right">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $product->category->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                {{ $product->stock_on_hand }} {{ $product->unit }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600">
                                Rp {{ number_format($product->stock_on_hand * $product->cost_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada data produk.</td>
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