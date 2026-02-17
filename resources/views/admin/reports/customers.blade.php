<x-layouts.admin :title="'Laporan Pelanggan'">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Pelanggan</h2>
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
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <form action="{{ route('admin.reports.customers') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-2 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-2 border border-slate-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Pelanggan Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_customers']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Yang bertransaksi periode ini</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Belanja</p>
            <p class="text-2xl font-bold text-green-600 mt-1">Rp
                {{ number_format($summary['total_spent'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Poin Didapat</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($summary['total_points_earned']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Poin Ditukar</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">{{ number_format($summary['total_points_redeemed']) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Customers -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-fit">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800">Top 5 Pelanggan (Spending)</h3>
            </div>
            <div class="p-0">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topCustomers as $index => $cust)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 w-10 text-center font-bold text-gray-400">#{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $cust->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cust->transaction_count }} Transaksi</p>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-green-600">
                                    Rp {{ number_format($cust->total_spent, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Customer List -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Detail Aktivitas Pelanggan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3 text-center">Transaksi</th>
                            <th class="px-6 py-3 text-right">Total Belanja</th>
                            <th class="px-6 py-3 text-center">Poin (+/-)</th>
                            <th class="px-6 py-3 text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($customers as $cust)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800">{{ $cust->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cust->phone }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ $cust->total_transactions }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">
                                    Rp {{ number_format($cust->total_spent, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="text-green-600 text-xs block">+{{ number_format($cust->points_earned) }}</span>
                                    @if($cust->points_redeemed > 0)
                                        <span
                                            class="text-red-600 text-xs block">-{{ number_format($cust->points_redeemed) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-blue-600">
                                    {{ number_format($cust->points_balance) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada aktivitas pelanggan
                                    pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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