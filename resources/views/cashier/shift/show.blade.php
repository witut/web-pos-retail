<x-layouts.cashier title="Detail Sesi Kasir">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('cashier.shift.history') }}"
                    class="text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Riwayat
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Sesi #{{ $session->id }}</h1>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Ringkasan Z-Report
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Kasir: {{ $session->user->name }}
                        </p>
                    </div>
                    <div>
                        @if($session->status === 'open')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Selesai
                            </span>
                        @endif
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Waktu Buka
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $session->opened_at->format('d M Y H:i') }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Waktu Tutup
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $session->closed_at ? $session->closed_at->format('d M Y H:i') : '-' }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Modal Awal
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                Rp {{ number_format($session->opening_cash, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Total Penjualan Tunai
                            </dt>
                            <dd class="mt-1 text-sm font-bold text-green-600 sm:mt-0 sm:col-span-2">
                                + Rp {{ number_format($report['cash_sales'], 0, ',', '.') }}
                            </dd>
                        </div>
                        @if($report['cash_out'] > 0)
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Pengeluaran Kas
                                </dt>
                                <dd class="mt-1 text-sm font-bold text-red-600 sm:mt-0 sm:col-span-2">
                                    - Rp {{ number_format($report['cash_out'], 0, ',', '.') }}
                                </dd>
                            </div>
                        @endif

                        <div
                            class="bg-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200">
                            <dt class="text-base font-bold text-gray-900">
                                Ekspektasi Sistem
                            </dt>
                            <dd class="mt-1 text-base font-bold text-gray-900 sm:mt-0 sm:col-span-2">
                                Rp {{ number_format($report['expected_cash'], 0, ',', '.') }}
                            </dd>
                        </div>

                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Uang Fisik (Aktual)
                            </dt>
                            <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">
                                Rp {{ number_format($session->closing_cash ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>

                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Selisih (Variance)
                            </dt>
                            <dd
                                class="mt-1 text-sm font-bold {{ ($session->variance ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }} sm:mt-0 sm:col-span-2">
                                Rp {{ number_format($session->variance ?? 0, 0, ',', '.') }}
                            </dd>
                        </div>
                        @if($session->notes)
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">
                                    Catatan
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $session->notes }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            <div class="mt-6 flex justify-center">
                <a href="{{ route('pos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                    Buka Sesi Baru / Kembali ke POS
                </a>
            </div>
        </div>
    </div>
</x-layouts.cashier>