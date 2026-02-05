<x-layouts.admin :title="'Detail Penerimaan'">
    <div class="max-w-6xl">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.stock.receiving.index') }}" class="hover:text-gray-700">Penerimaan Stok</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ $receiving->receiving_number }}</span>
            </div>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Detail Penerimaan</h2>
                <a href="{{ route('admin.stock.receiving.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Receiving Number -->
                    <div>
                        <p class="text-sm text-gray-500 mb-1">No. Penerimaan</p>
                        <p class="font-mono font-semibold text-lg text-slate-700">{{ $receiving->receiving_number }}</p>
                    </div>

                    <!-- Date -->
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tanggal Terima</p>
                        <p class="font-medium text-gray-800">{{ $receiving->receiving_date->format('d F Y') }}</p>
                    </div>

                    <!-- Supplier -->
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Supplier</p>
                        <p class="font-medium text-gray-800">{{ $receiving->supplier->name }}</p>
                        <p class="text-xs text-gray-500">{{ $receiving->supplier->code }}</p>
                    </div>

                    <!-- Invoice -->
                    <div>
                        <p class="text-sm text-gray-500 mb-1">No. Invoice/DO</p>
                        <p class="font-medium text-gray-800">{{ $receiving->invoice_number ?? '-' }}</p>
                    </div>
                </div>

                @if ($receiving->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Catatan</p>
                        <p class="text-gray-700">{{ $receiving->notes }}</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Dibuat oleh: <span class="font-medium text-gray-700">{{ $receiving->creator->name }}</span>
                        pada {{ $receiving->created_at->format('d M Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Item Barang</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Satuan
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga/Unit
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($receiving->items as $index => $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-800">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-800">
                                        {{ number_format($item->qty, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ $item->unit_name }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-800">
                                        Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-right font-semibold text-gray-700">
                                    Total Penerimaan:
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-lg text-slate-800">
                                    Rp {{ number_format($receiving->total_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-300 text-sm">Total Item</p>
                        <p class="text-2xl font-bold">{{ $receiving->items->count() }} Produk</p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-300 text-sm">Total Nilai</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($receiving->total_cost, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>