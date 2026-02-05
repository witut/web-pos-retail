<x-layouts.pos :title="'Riwayat Transaksi'">
    <div class="h-full p-6 overflow-y-auto" x-data="voidTransaction()">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Transaksi Anda hari ini</p>
                </div>
                <a href="{{ route('pos.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke POS
                </a>
            </div>

            <!-- Success/Error Messages -->
            <template x-if="successMessage">
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700" x-text="successMessage"></div>
            </template>
            <template x-if="errorMessage">
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700" x-text="errorMessage"></div>
            </template>

            <!-- Transactions Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if ($transactions->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-500 mb-2">Belum Ada Transaksi</h3>
                        <p class="text-gray-400">Mulai transaksi baru di POS Terminal</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Waktu</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Metode</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($transactions as $trx)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-sm font-medium text-slate-700">
                                                {{ $trx->invoice_number }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $trx->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $trx->items->count() }} item
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-gray-900">
                                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm">
                                            @switch($trx->payment_method)
                                                @case('cash')
                                                    <span class="text-green-600">Tunai</span>
                                                @break

                                                @case('card')
                                                    <span class="text-blue-600">Kartu</span>
                                                @break

                                                @case('qris')
                                                    <span class="text-purple-600">QRIS</span>
                                                @break

                                                @default
                                                    <span class="text-gray-600">{{ ucfirst($trx->payment_method) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if ($trx->status === 'completed')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Selesai
                                                </span>
                                            @elseif ($trx->status === 'void')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Void
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($trx->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('pos.transaction.show', $trx) }}"
                                                    class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg"
                                                    title="Detail">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('pos.transaction.print', $trx) }}" target="_blank"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                                    title="Cetak">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                </a>
                                                @if ($trx->status === 'completed')
                                                    <button type="button"
                                                        @click="openVoidModal({{ $trx->id }}, '{{ $trx->invoice_number }}')"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                        title="Void">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Void Modal -->
        <div x-show="showVoidModal" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4" @click.outside="closeVoidModal()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Void Transaksi</h3>
                        <button @click="closeVoidModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">
                            <strong>Perhatian!</strong> Void akan membatalkan transaksi 
                            <span class="font-mono font-medium" x-text="voidInvoice"></span> 
                            dan mengembalikan stok.
                        </p>
                    </div>

                    <form @submit.prevent="processVoid()">
                        <!-- Admin PIN -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">PIN Admin</label>
                            <input type="password" x-model="adminPin" maxlength="6" pattern="\d{6}"
                                class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="● ● ● ● ● ●" required autofocus>
                        </div>

                        <!-- Void Reason -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Void</label>
                            <select x-model="voidReason" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Pilih alasan...</option>
                                <option value="Kesalahan input">Kesalahan input</option>
                                <option value="Pelanggan batal">Pelanggan batal</option>
                                <option value="Pembayaran gagal">Pembayaran gagal</option>
                                <option value="Produk rusak/cacat">Produk rusak/cacat</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <!-- Void Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                            <textarea x-model="voidNotes" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Keterangan tambahan..."></textarea>
                        </div>

                        <!-- Error Message -->
                        <template x-if="voidError">
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="voidError"></div>
                        </template>

                        <!-- Buttons -->
                        <div class="flex space-x-3">
                            <button type="button" @click="closeVoidModal()"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="isProcessing"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                                <span x-show="!isProcessing">Void Transaksi</span>
                                <span x-show="isProcessing">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function voidTransaction() {
                return {
                    showVoidModal: false,
                    voidTransactionId: null,
                    voidInvoice: '',
                    adminPin: '',
                    voidReason: '',
                    voidNotes: '',
                    voidError: '',
                    isProcessing: false,
                    successMessage: '',
                    errorMessage: '',

                    openVoidModal(transactionId, invoiceNumber) {
                        this.voidTransactionId = transactionId;
                        this.voidInvoice = invoiceNumber;
                        this.adminPin = '';
                        this.voidReason = '';
                        this.voidNotes = '';
                        this.voidError = '';
                        this.showVoidModal = true;
                    },

                    closeVoidModal() {
                        this.showVoidModal = false;
                        this.voidTransactionId = null;
                        this.voidInvoice = '';
                        this.adminPin = '';
                        this.voidReason = '';
                        this.voidNotes = '';
                        this.voidError = '';
                    },

                    async processVoid() {
                        if (!this.adminPin || this.adminPin.length !== 6) {
                            this.voidError = 'PIN harus 6 digit';
                            return;
                        }

                        if (!this.voidReason) {
                            this.voidError = 'Pilih alasan void';
                            return;
                        }

                        this.isProcessing = true;
                        this.voidError = '';

                        try {
                            const response = await fetch(`/pos/transaction/${this.voidTransactionId}/void`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    admin_pin: this.adminPin,
                                    void_reason: this.voidReason,
                                    void_notes: this.voidNotes
                                })
                            });

                            const data = await response.json();

                            if (!response.ok || data.success === false) {
                                this.voidError = data.error || 'Gagal memproses void';
                                return;
                            }

                            // Success - reload page to show updated status
                            this.successMessage = `Transaksi ${this.voidInvoice} berhasil di-void`;
                            this.closeVoidModal();
                            
                            // Reload after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);

                        } catch (error) {
                            console.error('Void error:', error);
                            this.voidError = 'Terjadi kesalahan: ' + error.message;
                        } finally {
                            this.isProcessing = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-layouts.pos>
