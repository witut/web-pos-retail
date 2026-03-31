<x-layouts.admin :title="'Dashboard'">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($stats['today_sales'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-2">
                <span class="text-xs text-gray-500">{{ $stats['today_transactions'] ?? 0 }} transaksi</span>
                <span class="mx-2 text-gray-300">•</span>
                @if(($stats['growth_percentage'] ?? 0) >= 0)
                    <span class="text-xs text-green-600 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        {{ abs($stats['growth_percentage']) }}%
                    </span>
                @else
                    <span class="text-xs text-red-600 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                        {{ abs($stats['growth_percentage']) }}%
                    </span>
                @endif
                <span class="text-xs text-gray-400 ml-1">vs kemarin</span>
            </div>
        </div>

        <!-- Today's Profit -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Estimasi Profit</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($stats['today_profit'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Margin:
                {{ ($stats['today_sales'] > 0) ? round(($stats['today_profit'] / $stats['today_sales']) * 100, 1) : 0 }}%
            </p>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['today_transactions'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Hari ini</p>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Stok Menipis</p>
                    <p
                        class="text-2xl font-bold {{ ($stats['low_stock_count'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                        {{ $stats['low_stock_count'] ?? 0 }}
                    </p>
                </div>
                <div
                    class="w-12 h-12 {{ ($stats['low_stock_count'] ?? 0) > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 {{ ($stats['low_stock_count'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Perlu restock segera</p>
        </div>

        <!-- Total Customers -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Pelanggan</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_customers'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Terdaftar</p>
        </div>

        <!-- New Customers -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pelanggan Baru</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['new_customers_this_month'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Bulan ini</p>
        </div>
    </div>

    <!-- Charts & Tables Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Tren Penjualan (30 Hari Terakhir)</h3>
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Low Stock List -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 text-sm">Stok Menipis</h3>
                <a href="{{ route('admin.products.index', ['stock_status' => 'low']) }}"
                    class="text-xs text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            <div class="p-4 space-y-3 max-h-72 overflow-y-auto">
                @forelse($lowStockProducts ?? [] as $product)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div class="flex-1 min-w-0 mr-2">
                            <p class="text-xs font-medium text-gray-800 truncate">{{ $product->name }}</p>
                            <p class="text-[10px] text-gray-500">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <p class="text-xs font-bold text-red-600">{{ $product->stock_on_hand }} {{ $product->base_unit }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-xs italic">
                        Stok aman terkendali
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Inventory Alerts (Negative & Expiring) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Negative Stock List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-orange-50 rounded-t-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="font-bold text-orange-800 text-sm">Stok Minus (Perlu Penyesuaian)</h3>
                </div>
                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-full">Kritis</span>
            </div>
            <div class="p-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-gray-500 border-b">
                            <tr>
                                <th class="px-3 py-2 font-medium">Produk</th>
                                <th class="px-3 py-2 text-right font-medium">Stok Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($negativeStockProducts ?? [] as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">
                                        <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $product->sku }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <span class="text-red-600 font-bold font-mono">{{ $product->stock_on_hand }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-10 text-center text-gray-400 italic">
                                        Tidak ada stok negatif (Bagus!)
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expiring Soon List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-rose-50 rounded-t-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="font-bold text-rose-800 text-sm">Barang Mendekati Kadaluwarsa (ED)</h3>
                </div>
                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded-full">30 Hari</span>
            </div>
            <div class="p-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-gray-500 border-b">
                            <tr>
                                <th class="px-3 py-2 font-medium">Produk / Batch</th>
                                <th class="px-3 py-2 font-medium">Tgl Expired</th>
                                <th class="px-3 py-2 text-right font-medium">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($expiringSoonProducts ?? [] as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">
                                        <p class="font-medium text-gray-800">{{ $item->name }}</p>
                                        <p class="text-[10px] text-gray-500">Batch: {{ $item->batch_number }}</p>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded font-medium text-[10px]">
                                            {{ \Carbon\Carbon::parse($item->expiry_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <span class="font-bold text-gray-700">{{ $item->current_quantity }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-10 text-center text-gray-400 italic">
                                        Tidak ada barang mendekati kadaluwarsa
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Produk Terlaris (Bulan Ini)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3 text-right">Qty Terjual</th>
                        <th class="px-6 py-3 text-right">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topProducts ?? [] as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                            </td>
                            <td class="px-6 py-4 text-right font-medium">
                                {{ number_format($product->total_qty, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-green-600">Rp
                                {{ number_format($product->total_sales, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada data penjualan bulan ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('salesChart').getContext('2d');

                // Data from Controller
                const labels = @json($salesTrend['labels']);
                const data = @json($salesTrend['data']);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Penjualan (Rp)',
                            data: data,
                            borderColor: '#2563eb', // Blue-600
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [2, 4],
                                    color: '#f3f4f6'
                                },
                                ticks: {
                                    callback: function (value) {
                                        return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-layouts.admin>