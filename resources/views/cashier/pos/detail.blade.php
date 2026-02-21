<x-layouts.pos :title="'Detail Transaksi'">
    <div class="h-full p-6 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Detail Transaksi</h2>
                    <p class="font-mono text-slate-600">{{ $transaction->invoice_number }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    @if ($transaction->status === 'completed')
                        <a href="{{ route('pos.index') }}?return_invoice={{ urlencode($transaction->invoice_number) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent bg-orange-100 text-orange-700 font-medium rounded-lg hover:bg-orange-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                            </svg>
                            Retur (F10)
                        </a>
                    @endif
                    <a href="{{ route('pos.transaction.print', $transaction) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Struk
                    </a>
                    <a href="{{ route('pos.history') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Kembali
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transaction Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Informasi Transaksi</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Tanggal</dt>
                                <dd class="font-medium">{{ $transaction->created_at->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Waktu</dt>
                                <dd class="font-medium">{{ $transaction->created_at->format('H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Kasir</dt>
                                <dd class="font-medium">{{ $transaction->cashier->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Status</dt>
                                <dd>
                                    @if ($transaction->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Selesai
                                        </span>
                                    @elseif ($transaction->status === 'void')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Void
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Pembayaran</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Metode</dt>
                                <dd class="font-medium">
                                    @switch($transaction->payment_method)
                                        @case('cash')
                                            Tunai
                                        @break

                                        @case('card')
                                            Kartu
                                        @break

                                        @case('qris')
                                            QRIS
                                        @break

                                        @default
                                            {{ ucfirst($transaction->payment_method) }}
                                    @endswitch
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Dibayar</dt>
                                <dd class="font-medium">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Kembalian</dt>
                                <dd class="font-medium">Rp
                                    {{ number_format($transaction->change_amount, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800">Item Belanja</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Produk</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                            Qty</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Harga</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-gray-800">
                                                    {{ $item->product->name ?? $item->product_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center text-sm">
                                                {{ $item->qty }} {{ $item->unit_name }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm">
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-medium">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-gray-600">Subtotal</td>
                                        <td class="px-6 py-3 text-right font-medium">
                                            Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-gray-600">PPN</td>
                                        <td class="px-6 py-3 text-right font-medium">
                                            Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="text-lg">
                                        <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-800">
                                            TOTAL</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-700">
                                            Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.pos>
