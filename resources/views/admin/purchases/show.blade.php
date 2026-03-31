<x-layouts.admin :title="'Detail Pembelian ' . $purchase->purchase_number">
    <div class="max-w-6xl mx-auto">
        <!-- Header & Breadcrumb -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                    <a href="{{ route('admin.purchases.index') }}" class="hover:text-gray-700 transition-colors">Daftar Pembelian</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-900 font-medium">{{ $purchase->purchase_number }}</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    Detail Transaksi 
                    <span class="ml-3 px-3 py-1 bg-slate-100 text-slate-600 rounded text-xs font-mono">
                        {{ $purchase->purchase_number }}
                    </span>
                </h2>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-all flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Nota
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Info (Items) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Item List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest">Barang Diterima</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center">Jumlah</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right">Harga Beli</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($purchase->items as $item)
                                    <tr class="align-top">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-slate-800">{{ $item->product->name }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono">{{ $item->product->sku }}</div>
                                            
                                            <!-- Additional Info: Batch / SN -->
                                            @php
                                                // Normally we'd link back to the specific batch created, 
                                                // but for display we can look at movements or just show general info
                                            @endphp
                                            @if($item->product->tracking_type === 'batch')
                                                <div class="mt-1 flex gap-2">
                                                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-bold">BATCH TRACKING</span>
                                                </div>
                                            @endif
                                            @if($item->product->tracking_type === 'serial')
                                                <div class="mt-1">
                                                    <span class="px-1.5 py-0.5 bg-purple-50 text-purple-600 rounded text-[9px] font-bold">SERIAL TRACKING</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-sm font-medium text-gray-700">{{ number_format($item->qty, 0) }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $item->unit_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-sm text-gray-600">Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-sm font-bold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-gray-600">Total Transaksi</td>
                                    <td class="px-6 py-4 text-right text-slate-800 text-lg">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if($purchase->notes)
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-2">Catatan</h4>
                    <p class="text-sm text-gray-600 italic leading-relaxed">"{{ $purchase->notes }}"</p>
                </div>
                @endif
            </div>

            <!-- Right Info (Payment & Meta) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status & Payment Info -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest border-b pb-3">Informasi Pembayaran</h3>
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Status Bayar</span>
                        @include('admin.purchases.partials.payment_status', ['status' => $purchase->payment_status])
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Dibayar</span>
                        <span class="font-bold text-green-600">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($purchase->debt_amount > 0)
                        <div class="p-3 bg-red-50 rounded-lg border border-red-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] font-bold text-red-700 uppercase">Sisa Hutang</span>
                                <span class="text-lg font-bold text-red-600">Rp {{ number_format($purchase->debt_amount, 0, ',', '.') }}</span>
                            </div>
                            @if($purchase->due_date)
                                <div class="text-[10px] {{ $purchase->due_date < now() ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                                    Jatuh Tempo: {{ $purchase->due_date->format('d M Y') }}
                                    @if($purchase->due_date < now()) (OVERDUE) @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Transaction Meta -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-800 uppercase text-xs tracking-widest border-b pb-3">Metadata</h3>
                    
                    <div class="space-y-4">
                         <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase">Supplier</label>
                            <div class="text-sm font-bold text-slate-800">{{ $purchase->supplier->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $purchase->supplier->email ?? '' }}</div>
                         </div>
                         <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase">Tanggal Input</label>
                            <div class="text-sm text-gray-700">{{ $purchase->created_at->format('d M Y, H:i') }}</div>
                         </div>
                         <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase">Input Oleh</label>
                            <div class="text-sm text-gray-700 font-medium">{{ $purchase->creator->name ?? 'System' }}</div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
