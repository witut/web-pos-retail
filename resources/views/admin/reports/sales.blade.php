<x-layouts.admin :title="'Laporan Penjualan'">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h2>
            <p class="text-sm text-gray-500">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </p>
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
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <div class="flex gap-2">
            <form action="{{ route('admin.reports.sales') }}" method="GET"
                class="flex flex-wrap gap-2 items-center bg-white dark:bg-slate-800 p-2 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-1.5 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                <span class="text-slate-400">-</span>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-1.5 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">

                <select name="cashier_id"
                    class="px-3 py-1.5 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ $selectedCashier == $cashier->id ? 'selected' : '' }}>
                            {{ $cashier->name }}</option>
                    @endforeach
                </select>

                <select name="payment_method"
                    class="px-3 py-1.5 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-white">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ $selectedPaymentMethod == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ $selectedPaymentMethod == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="debit" {{ $selectedPaymentMethod == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                    Filter
                </button>
            </form>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-md shadow-lg py-1 z-50 border border-slate-200 dark:border-slate-700"
                    x-cloak>
                    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'sales', 'format' => 'excel'])) }}"
                        class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Export
                        Excel</a>
                    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'sales', 'format' => 'pdf'])) }}"
                        class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Export
                        PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp
                {{ number_format($summary['total_sales'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Profit</p>
            <p class="text-2xl font-bold text-green-600 mt-1">Rp
                {{ number_format($summary['total_profit'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_transactions']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Rata-rata Transaksi</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">Rp
                {{ number_format($summary['average_transaction'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="mb-8">
        <h3 class="font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($by_payment_method as $method => $data)
                <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-semibold">{{ $method }}</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">Rp {{ number_format($data['total'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $data['count'] }}
                            Trx</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Detail Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-3">No Invoice</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Kasir</th>
                        <th class="px-6 py-3">Metode</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-right">Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-blue-600">
                                <a href="{{ route('pos.transaction.show', $trx->id) }}" target="_blank">
                                    {{ $trx->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $trx->created_at->format('d M H:i') }}
                            </td>
                            <td class="px-6 py-4 text-gray-800">{{ $trx->cashier->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 bg-gray-100 text-xs rounded-full uppercase">{{ $trx->payment_method }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-green-600">
                                Rp {{ number_format($trx->getTotalProfit(), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada data transaksi pada
                                periode ini.</td>
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