<x-layouts.admin title="Laporan Laba Rugi">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Laba Rugi</h2>
            <p class="text-sm text-gray-500">Analisis pendapatan, HPP, dan margin keuntungan</p>
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
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <div class="flex flex-col md:flex-row gap-3">
            <form action="{{ route('admin.reports.profit-loss') }}" method="GET"
                class="flex flex-wrap gap-2 items-center flex-1">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <span class="text-gray-400">-</span>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
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
                    <a href="{{ route('admin.reports.export', ['type' => 'profit_loss', 'format' => 'excel', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Excel</a>
                    <a href="{{ route('admin.reports.export', ['type' => 'profit_loss', 'format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Gross Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Pendapatan (Omzet)</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($summary['gross_revenue'], 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <span class="font-medium">{{ $summary['transaction_count'] }}</span> transaksi berhasil
            </div>
        </div>

        <!-- COGS (HPP) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total HPP</p>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">Rp
                        {{ number_format($summary['cogs'], 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-2 bg-red-50 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <span
                    class="font-medium">{{ number_format(($summary['cogs'] / ($summary['gross_revenue'] ?: 1)) * 100, 1) }}%</span>
                dari pendapatan
            </div>
        </div>

        <!-- Gross Profit -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Laba Kotor</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">Rp
                        {{ number_format($summary['gross_profit'], 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Margin Kotor: <span
                    class="font-bold text-emerald-600">{{ number_format($summary['gross_margin_percent'], 1) }}%</span>
            </div>
        </div>

        <!-- Net Profit (Simplified) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Laba Bersih</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">Rp
                        {{ number_format($summary['net_profit'], 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Setelah dikurangi biaya ops. (0)
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Rincian Per Hari</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3 text-right">Pendapatan</th>
                        <th class="px-6 py-3 text-right">HPP</th>
                        <th class="px-6 py-3 text-right">Laba Kotor</th>
                        <th class="px-6 py-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($daily_breakdown as $day)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($day['date'])->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-700">
                                Rp {{ number_format($day['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-red-600">
                                Rp {{ number_format($day['cogs'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                Rp {{ number_format($day['profit'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                {{ $day['revenue'] > 0 ? number_format(($day['profit'] / $day['revenue']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold border-t border-gray-200">
                    <tr>
                        <td class="px-6 py-4 text-gray-800">TOTAL</td>
                        <td class="px-6 py-4 text-right text-gray-800">Rp
                            {{ number_format($summary['gross_revenue'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-red-600">Rp
                            {{ number_format($summary['cogs'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-emerald-600">Rp
                            {{ number_format($summary['gross_profit'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-800">
                            {{ number_format($summary['gross_margin_percent'], 1) }}%
                        </td>
                    </tr>
                </tfoot>
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