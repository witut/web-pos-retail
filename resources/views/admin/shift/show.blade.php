<x-layouts.admin title="Detail Sesi Kasir">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.shifts.index') }}" class="hover:text-blue-600">Riwayat Sesi</a>
                <span>/</span>
                <span>Detail</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Sesi #{{ $session->id }}</h2>
        </div>
        <a href="{{ route('admin.shifts.index') }}"
            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Ringkasan Z-Report</h3>
                <p class="text-sm text-gray-500 mt-1">Kasir: <span
                        class="font-medium text-gray-900">{{ $session->user->name }}</span></p>
            </div>
            <div>
                @if($session->status === 'open')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        Sesi Aktif
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                        Sesi Selesai
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Session Time -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Waktu Sesi</dt>
                    <dd class="flex justify-between items-center border-b border-gray-200 py-2">
                        <span class="text-sm text-gray-600">Dibuka</span>
                        <span
                            class="text-sm font-medium text-gray-900">{{ $session->opened_at->format('d M Y H:i') }}</span>
                    </dd>
                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Ditutup</span>
                        <span
                            class="text-sm font-medium text-gray-900">{{ $session->closed_at ? $session->closed_at->format('d M Y H:i') : '-' }}</span>
                    </dd>
                </div>

                <!-- Financial Summary -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ringkasan Keuangan
                    </dt>

                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Modal Awal</span>
                        <span class="text-sm font-medium text-gray-900">Rp
                            {{ number_format($session->opening_cash, 0, ',', '.') }}</span>
                    </dd>
                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Total Penjualan Tunai</span>
                        <span class="text-sm font-bold text-green-600">+ Rp
                            {{ number_format($report['cash_sales'], 0, ',', '.') }}</span>
                    </dd>

                    @if($report['cash_out'] > 0)
                        <dd class="flex justify-between items-center py-2 text-red-600">
                            <span class="text-sm">Pengeluaran Kas</span>
                            <span class="text-sm font-bold">- Rp
                                {{ number_format($report['cash_out'], 0, ',', '.') }}</span>
                        </dd>
                    @endif

                    <div class="border-t border-gray-200 my-2"></div>

                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-900">Ekspektasi Sistem (Seharusnya)</span>
                        <span class="text-sm font-bold text-gray-900">Rp
                            {{ number_format($report['expected_cash'], 0, ',', '.') }}</span>
                    </dd>

                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-900">Uang Fisik (Aktual)</span>
                        <span class="text-sm font-bold text-gray-900">Rp
                            {{ number_format($session->closing_cash ?? 0, 0, ',', '.') }}</span>
                    </dd>

                    <div class="border-t border-gray-200 my-2"></div>

                    <dd class="flex justify-between items-center py-2">
                        <span class="text-sm font-bold text-gray-700">Selisih (Variance)</span>
                        <span
                            class="text-lg font-bold {{ ($session->variance ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($session->variance ?? 0, 0, ',', '.') }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if($session->notes)
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Catatan Penutupan</h4>
                    <div class="bg-orange-50 border border-orange-100 p-4 rounded-lg text-sm text-orange-800 italic">
                        "{{ $session->notes }}"
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>