<!-- Success Modal -->
<div x-show="showSuccessModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 overflow-y-auto px-4"
    @keydown.escape.window="newTransaction()">
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden p-8 text-center"
         @click.outside="newTransaction()">
        
        <div class="mb-4 bg-emerald-100 text-emerald-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h3 class="text-2xl font-bold text-slate-800 mb-2">Transaksi Berhasil!</h3>
        <p class="text-slate-500 mb-6">Pembayaran telah diterima dan stok telah diperbarui.</p>
        
        <div class="bg-slate-50 rounded-xl p-4 mb-8 text-center border border-slate-100">
            <p class="text-xs text-slate-400 uppercase tracking-widest mb-1 italic">Nomor Invoice</p>
            <p class="text-2xl font-mono font-bold text-slate-800" x-text="lastInvoice || 'INV/...'"></p>
        </div>
        
        <div class="space-y-3">
            <button @click="printReceipt()" x-ref="printReceiptBtn"
                    class="w-full h-14 flex items-center justify-center gap-2 bg-slate-800 text-white rounded-xl py-3 hover:bg-slate-700 transition font-bold text-lg shadow-lg shadow-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak (Print)
            </button>
            <button @click="newTransaction()" 
                    class="w-full h-14 bg-emerald-500 text-white rounded-xl py-4 text-lg font-bold shadow-lg shadow-emerald-100 hover:bg-emerald-600 transition">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>
