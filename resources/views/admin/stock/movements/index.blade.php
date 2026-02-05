<x-layouts.admin :title="'Stock Movements'">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Stock Movements</h2>
            <p class="text-sm text-gray-500">Kartu stok - Riwayat pergerakan stok</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.stock.movements.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Product Filter -->
            <div>
                <select name="product_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <select name="type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Semua Tipe</option>
                    <option value="IN" {{ request('type') == 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                    <option value="OUT" {{ request('type') == 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <!-- End Date -->
            <div>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <!-- Buttons -->
            <div class="flex space-x-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.stock.movements.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($movements->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-lg font-medium text-gray-500 mb-2">Belum Ada Pergerakan Stok</h3>
                <p class="text-gray-400">Data pergerakan stok akan muncul setelah ada transaksi.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referensi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Sebelum</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Sesudah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $movement->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-800">{{ $movement->product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($movement->movement_type == 'IN')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                            </svg>
                                            MASUK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                            </svg>
                                            KELUAR
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-medium {{ $movement->movement_type == 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->movement_type == 'IN' ? '+' : '-' }}{{ number_format($movement->qty, 2) }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $movement->unit_name }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="text-gray-500">{{ $movement->reference_type }}:</span>
                                    <span class="text-gray-800">{{ $movement->reference_id }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">
                                    {{ number_format($movement->stock_before, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    {{ number_format($movement->stock_after, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
