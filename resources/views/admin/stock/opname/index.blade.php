<x-layouts.admin :title="'Stock Opname'">
    <div class="flex flex-col items-center justify-center py-16">
        <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <h2 class="text-xl font-bold text-gray-600 mb-2">Stock Opname</h2>
        <p class="text-gray-400 text-center max-w-md">
            Fitur Stock Opname akan segera hadir. Anda akan dapat melakukan penghitungan stok fisik dan penyesuaian
            stok.
        </p>
        <a href="{{ route('admin.stock.receiving.index') }}"
            class="mt-6 inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Ke Penerimaan Stok
        </a>
    </div>
</x-layouts.admin>