<x-layouts.admin :title="'Dashboard'">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($todaySales ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $todayTransactions ?? 0 }} transaksi</p>
        </div>

        <!-- Monthly Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Penjualan Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($monthlySales ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $monthlyTransactions ?? 0 }} transaksi</p>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Produk</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalProducts ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $activeProducts ?? 0 }} aktif</p>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Stok Menipis</p>
                    <p
                        class="text-2xl font-bold {{ ($lowStockCount ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                        {{ $lowStockCount ?? 0 }}
                    </p>
                </div>
                <div
                    class="w-12 h-12 {{ ($lowStockCount ?? 0) > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 {{ ($lowStockCount ?? 0) > 0 ? 'text-red-600' : 'text-gray-600' }}" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Perlu restock segera</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Low Stock Products -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Stok Menipis</h3>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($lowStockProducts ?? [] as $product)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-red-600">{{ $product->stock_on_hand }}</p>
                            <p class="text-xs text-gray-400">min: {{ $product->min_stock_alert }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">Tidak ada produk stok menipis</p>
                @endforelse
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Produk Terlaris (Bulan Ini)</h3>
            </div>
            <div class="p-4">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="pb-3">Produk</th>
                            <th class="pb-3 text-right">Qty Terjual</th>
                            <th class="pb-3 text-right">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topProducts ?? [] as $product)
                            <tr>
                                <td class="py-3">
                                    <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                                </td>
                                <td class="py-3 text-right font-medium">
                                    {{ number_format($product->total_qty, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-right font-medium text-green-600">Rp
                                    {{ number_format($product->total_sales, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">Belum ada data penjualan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>