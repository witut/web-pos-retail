@php
    $hasReturnableItems = false;
    if ($transaction->status === 'completed') {
        $hasReturnableItems = $transaction->items->contains(function ($item) use ($transaction) {
            $returnedQty = 0;
            foreach ($transaction->returns as $ret) {
                foreach ($ret->returnItems->where('transaction_item_id', $item->id) as $match) {
                    $returnedQty += $match->quantity;
                }
            }
            return ($item->qty - $returnedQty) > 0;
        });
    }
@endphp
<x-layouts.admin title="Detail Transaksi #{{ $transaction->invoice_number }}">
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Detail Transaksi</h2>
            <p class="text-sm text-slate-500">Invoice: <span
                    class="font-medium text-slate-700">{{ $transaction->invoice_number }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.transactions.index') }}"
                class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                Kembali
            </a>
            @if($transaction->status === 'completed' && $hasReturnableItems)
                <button type="button" @click.prevent.stop="$dispatch('open-return-modal')"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                        </svg>
                        Retur Barang
                    </div>
                </button>
            @endif
            @if($transaction->status === 'completed')
                <a href="{{ route('pos.transaction.print', $transaction) }}" target="_blank"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 border border-slate-800 text-white rounded-lg text-sm font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                    Cetak Ulang Struk
                </a>
            @endif
        </div>
    </div>

    <!-- Transaction Summary Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Info -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Informasi Umum</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Tanggal</span>
                    <span
                        class="text-sm font-medium text-slate-900">{{ $transaction->transaction_date->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Kasir</span>
                    <span
                        class="text-sm font-medium text-slate-900">{{ optional($transaction->cashier)->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Pelanggan</span>
                    <span
                        class="text-sm font-medium text-slate-900">{{ optional($transaction->customer)->name ?? 'Walk-in Customer' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Status</span>
                    @if($transaction->status === 'completed')
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Completed</span>
                    @else
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Void</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Pembayaran</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Metode</span>
                    <span class="text-sm font-medium text-slate-900 uppercase">{{ $transaction->payment_method }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Total Tagihan</span>
                    <span class="text-sm font-bold text-slate-900">Rp
                        {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Uang Diterima</span>
                    <span class="text-sm font-medium text-green-600">Rp
                        {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Kembalian</span>
                    <span class="text-sm font-medium text-slate-900">Rp
                        {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Discounts & Points -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Diskon & Reward</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Diskon Produk</span>
                    <span class="text-sm font-medium text-slate-900 text-red-600">Rp
                        {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                </div>
                @if($transaction->coupon_id)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Kupon ({{ optional($transaction->coupon)->code }})</span>
                        <span class="text-sm font-medium text-slate-900 text-red-600">Rp
                            {{ number_format($transaction->coupon_discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaction->points_redeemed > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Tukar Poin ({{ $transaction->points_redeemed }})</span>
                        <span class="text-sm font-medium text-slate-900 text-red-600">Rp
                            {{ number_format($transaction->points_discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="pt-2 mt-2 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-700">Poin Didapat</span>
                    <span
                        class="inline-flex px-2.5 py-1 text-xs font-bold leading-5 text-amber-800 bg-amber-100 rounded-full">+{{ $transaction->points_earned }}
                        Pt</span>
                </div>
            </div>
        </div>
    </div>

    @if($transaction->returns->isNotEmpty())
        <!-- Return Info Box -->
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-sm font-bold text-red-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Terdapat Riwayat Retur pada Transaksi ini
            </h3>
            <div class="space-y-4">
                @foreach($transaction->returns as $ret)
                    <div class="bg-white rounded border border-red-100 p-4">
                        <div class="flex justify-between border-b border-gray-100 pb-2 mb-2">
                            <div>
                                <p class="text-xs text-gray-500 font-mono">{{ $ret->return_number }} &bull;
                                    {{ $ret->created_at->format('d M Y H:i') }}
                                </p>
                                <p class="text-sm font-bold mt-1">Status: <span
                                        class="capitalize text-red-600">{{ $ret->status }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Total Refund</p>
                                <p class="text-sm font-bold text-red-600">Rp
                                    {{ number_format($ret->refund_amount, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-400 capitalize">{{ $ret->refund_method }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700"><span class="font-semibold">Alasan:</span> {{ $ret->reason }}</p>
                        @if($ret->notes)
                            <p class="text-sm text-gray-500 italic mt-1 pb-2">"{{ $ret->notes }}"</p>
                        @endif

                        <div class="mt-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Item Diretur:</p>
                            <ul class="text-sm space-y-1">
                                @foreach($ret->returnItems as $rItem)
                                    <li class="flex justify-between text-gray-700">
                                        <span>• {{ $rItem->quantity }}x {{ optional($rItem->product)->name }} <span
                                                class="text-xs text-gray-400">({{ $rItem->condition }})</span></span>
                                        <span>Rp {{ number_format($rItem->refund_amount, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <h3 class="font-semibold text-slate-800">Item Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Produk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Diskon</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Subtotal Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transaction->items as $item)
                        <!-- Check if item was returned -->
                        @php
                            $returnedQty = 0;
                            if ($transaction->returns->isNotEmpty()) {
                                foreach ($transaction->returns as $ret) {
                                    $match = $ret->returnItems->where('transaction_item_id', $item->id)->first();
                                    if ($match)
                                        $returnedQty += $match->quantity;
                                }
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 {{ $returnedQty > 0 ? 'bg-red-50/50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-500">Unit: {{ $item->unit_name }}</div>
                                @if($returnedQty > 0)
                                    <span
                                        class="inline-flex mt-1 px-2 py-0.5 text-[10px] font-bold leading-4 text-red-700 bg-red-100 rounded">
                                        {{ $returnedQty }} Diretur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-900">
                                @if($item->discount_amount > 0)
                                    <span class="text-xs text-slate-400 line-through mr-1">Rp
                                        {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <br>
                                @endif
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-500 font-medium">
                                {{ $item->discount_amount > 0 ? '-Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-slate-900 font-medium">
                                {{ $item->qty }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-slate-500">Subtotal</td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-slate-900">Rp
                            {{ number_format($transaction->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @php
                        $totalDiscountDisplay = $transaction->discount_amount + $transaction->points_discount_amount;
                    @endphp
                    @if($totalDiscountDisplay > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-slate-500">Diskon</td>
                            <td class="px-6 py-3 text-right text-sm font-bold text-red-500">-Rp
                                {{ number_format($totalDiscountDisplay, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-slate-700">Total Akhir</td>
                        <td class="px-6 py-3 text-right text-base font-bold text-indigo-700">Rp
                            {{ number_format($transaction->total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($transaction->tax_amount > 0)
                        <tr>
                            <td colspan="5" class="px-6 py-2 text-right text-xs text-slate-500 italic">
                                {{ \App\Models\Setting::get('tax_type', 'exclusive') == 'inclusive' ? 'Sudah termasuk Pajak (PPN)' : 'Belum termasuk Pajak Tambahan (PPN)' }}
                                Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

    @php
        $hasReturnableItems = false;
        foreach ($transaction->items as $item) {
            $returnedQty = 0;
            foreach ($transaction->returns as $ret) {
                $match = $ret->returnItems->where('transaction_item_id', $item->id)->first();
                if ($match)
                    $returnedQty += $match->quantity;
            }
            if ($item->qty > $returnedQty) {
                $hasReturnableItems = true;
                break;
            }
        }
    @endphp

    <!-- Return Modal -->
    @if($transaction->status === 'completed' && $hasReturnableItems)
        <div x-data="{ 
                                                            open: false,
                                                            items: {{ Js::from($transaction->items->map(function ($i) use ($transaction) {
            $returnedQty = 0;
            foreach ($transaction->returns as $ret) {
                foreach ($ret->returnItems->where('transaction_item_id', $i->id) as $match) {
                    $returnedQty += $match->quantity;
                }
            }
            $netPrice = $i->qty > 0 ? ($i->subtotal / $i->qty) : 0;
            return ['id' => $i->id, 'name' => $i->product_name, 'price' => $i->unit_price, 'netPrice' => $netPrice, 'maxQty' => max(0, $i->qty - $returnedQty), 'returnQty' => 0, 'condition' => 'good'];
        })->filter(function ($i) {
            return $i['maxQty'] > 0; })->values()) }},
                                                            reason: '',
                                                            notes: '',
                                                            refundMethod: 'cash',
                                                            get globalDiscounts() {
                                                                return {{ $transaction->points_discount_amount + $transaction->coupon_discount_amount }};
                                                            },
                                                            get totalRefund() {
                                                                return Math.round(this.items.reduce((sum, item) => {
                                                                    let itemNetRefund = item.netPrice * item.returnQty;

                                                                    if (this.globalDiscounts > 0 && {{ $transaction->subtotal }} > 0) {
                                                                        let ratio = itemNetRefund / {{ $transaction->subtotal }};
                                                                        itemNetRefund = itemNetRefund - (this.globalDiscounts * ratio);
                                                                    }

                                                                    return sum + Math.max(0, itemNetRefund);
                                                                }, 0));
                                                            },
                                                            get hasValidItems() {
                                                                return this.totalRefund > 0;
                                                            },
                                                            submitForm() {
                                                                if(!this.hasValidItems) {
                                                                    alert('Pilih minimal 1 item untuk diretur');
                                                                    return;
                                                                }
                                                                if(this.reason.trim() === '') {
                                                                    alert('Alasan retur wajib diisi');
                                                                    return;
                                                                }
                                                                if(confirm('Proses retur senilai Rp ' + this.totalRefund.toLocaleString('id-ID') + '? Aksi ini tidak dapat dibatalkan.')) {
                                                                    document.getElementById('returnForm').submit();
                                                                }
                                                            }
                                                        }" @open-return-modal.window="open = true" x-show="open"
            class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop without click handler to prevent event bubbling issues -->
                <div x-show="open" @click="open = false" x-transition.opacity
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="open" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-t-4 border-red-500">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Formulir Retur Transaksi</h3>

                                <form id="returnForm" action="{{ route('admin.returns.store', $transaction) }}"
                                    method="POST">
                                    @csrf

                                    <div class="bg-slate-50 p-4 rounded-lg mb-4 border border-slate-200">
                                        <h4 class="font-semibold text-sm text-slate-700 mb-2">1. Pilih Item & Kuantitas
                                            Retur</h4>
                                        <div class="space-y-3">
                                            <template x-for="(item, index) in items" :key="item.id">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between bg-white p-3 rounded border border-slate-200 shadow-sm gap-3">
                                                    <div class="flex-1">
                                                        <p class="font-medium text-sm text-slate-800" x-text="item.name">
                                                        </p>
                                                        <p class="text-xs text-slate-500">
                                                            <template x-if="item.price !== item.netPrice">
                                                                <span class="line-through text-slate-400 mr-1"
                                                                    x-text="'Rp ' + item.price.toLocaleString('id-ID')"></span>
                                                            </template>
                                                            Rp <span x-text="item.netPrice.toLocaleString('id-ID')"></span>
                                                            / pcs
                                                            (Maks: <span x-text="item.maxQty"></span>)
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="flex items-center border border-slate-300 rounded overflow-hidden">
                                                            <button type="button"
                                                                @click="if(item.returnQty > 0) item.returnQty--"
                                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold">-</button>
                                                            <input type="number" :name="'items['+item.id+'][qty]'"
                                                                x-model.number="item.returnQty"
                                                                class="w-12 text-center py-1 outline-none text-sm font-medium"
                                                                min="0" :max="item.maxQty" readonly>
                                                            <button type="button"
                                                                @click="if(item.returnQty < item.maxQty) item.returnQty++"
                                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold">+</button>
                                                        </div>

                                                        <select
                                                            :name="item.returnQty > 0 ? 'items['+item.id+'][condition]' : ''"
                                                            x-show="item.returnQty > 0" x-model="item.condition"
                                                            class="block py-1.5 px-2 border border-slate-300 bg-white rounded-md shadow-sm focus:outline-none text-xs">
                                                            <option value="good">Bisa Dijual Lagi (Good)</option>
                                                            <option value="damaged">Rusak (Damaged)</option>
                                                        </select>

                                                        <div class="w-24 text-right">
                                                            <span class="text-sm font-bold text-red-600"
                                                                x-show="item.returnQty > 0">
                                                                Rp <span x-text="Math.round(
                                                                                        globalDiscounts > 0 && {{ $transaction->subtotal }} > 0 ?
                                                                                        (item.netPrice * item.returnQty) - (globalDiscounts * ((item.netPrice * item.returnQty) / {{ $transaction->subtotal }})) :
                                                                                        (item.netPrice * item.returnQty)
                                                                                    ).toLocaleString('id-ID')"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-slate-50 p-4 rounded-lg mb-4 border border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-sm font-semibold text-gray-700">2. Alasan Retur <span
                                                    class="text-red-500">*</span></label>
                                            <select name="reason" x-model="reason"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                                required>
                                                <option value="">-- Pilih Alasan --</option>
                                                <option value="Barang rusak / cacat produksi">Barang rusak / cacat produksi
                                                </option>
                                                <option value="Barang kedaluwarsa (Expired)">Barang kedaluwarsa (Expired)
                                                </option>
                                                <option value="Ketidaksesuaian barang (salah ambil)">Ketidaksesuaian barang
                                                    (salah ambil)</option>
                                                <option value="Lainnya">Lainnya...</option>
                                            </select>
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Catatan Tambahan</label>
                                            <input type="text" name="notes" x-model="notes"
                                                class="mt-1 flex-1 block w-full border-gray-300 rounded-md sm:text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Opsional">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700">3. Metode Pengembalian
                                                Dana</label>
                                            <select name="refund_method" x-model="refundMethod"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="cash">Uang Tunai (Cash)</option>
                                                <option value="store_credit" disabled>Saldo / Store Credit (Coming Soon)
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-red-50 p-4 border border-red-200 rounded-lg flex justify-between items-center">
                                        <span class="font-bold text-red-800">TOTAL REFUND:</span>
                                        <span class="text-2xl font-black text-red-700">Rp <span
                                                x-text="totalRefund.toLocaleString('id-ID')"></span></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="button" @click="submitForm()" :disabled="!hasValidItems || reason === ''"
                            :class="(hasValidItems && reason !== '') ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-red-300 cursor-not-allowed'"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Selesaikan Retur
                        </button>
                        <button type="button" @click="open = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-layouts.admin>