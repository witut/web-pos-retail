<x-layouts.admin :title="'Daftar Pembelian'">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Pembelian (Procurement)</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola pengadaan barang dan hutang supplier.</p>
            </div>
            <div>
                <a href="{{ route('admin.purchases.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-all shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pembelian Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('admin.purchases.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari No. Pembelian atau Nama Supplier..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent outline-none">
                </div>
                
                <div>
                    <select name="payment_status" onchange="this.form.submit()"
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Hutang (Unpaid)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'payment_status']))
                        <a href="{{ route('admin.purchases.index') }}" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">No. Pembelian / Tgl</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Total Transaksi</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status Bayar</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Sisa Hutang</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($purchases as $purchase)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-800">{{ $purchase->purchase_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $purchase->purchase_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-700">{{ $purchase->supplier->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">Oleh: {{ $purchase->creator->name ?? 'System' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-sm font-bold text-gray-800">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @include('admin.purchases.partials.payment_status', ['status' => $purchase->payment_status])
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($purchase->debt_amount > 0)
                                        <div class="text-sm font-bold text-red-600">Rp {{ number_format($purchase->debt_amount, 0, ',', '.') }}</div>
                                        @if($purchase->due_date)
                                            <div class="text-[10px] {{ $purchase->due_date < now() ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                                                Tempo: {{ $purchase->due_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.purchases.show', $purchase) }}" 
                                            class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <!-- Add "Bayar Hutang" button if debt exists -->
                                        @if($purchase->debt_amount > 0)
                                            <button @click="alert('Fitur bayar hutang sedang dikembangkan')" 
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Bayar Hutang">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-500 font-medium italic">Tidak ada transaksi pembelian yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($purchases->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
