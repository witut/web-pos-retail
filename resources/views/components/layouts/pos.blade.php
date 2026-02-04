<x-layouts.app :title="$title ?? 'POS Terminal'">
    @push('styles')
        <style>
            /* Fullscreen POS layout */
            body {
                overflow: hidden;
            }

            .pos-container {
                height: 100vh;
            }
        </style>
    @endpush

    <div class="pos-container flex flex-col bg-gray-100">
        <!-- Top Bar -->
        <header class="h-14 bg-slate-800 text-white flex items-center justify-between px-4 flex-shrink-0">
            <div class="flex items-center space-x-4">
                <span class="text-lg font-bold">POS Terminal</span>
                <span class="text-slate-400">|</span>
                <span class="text-sm text-slate-300">{{ auth()->user()->name ?? 'Kasir' }}</span>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm text-slate-300" x-data
                    x-text="new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></span>
                <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300">
                    ← Kembali ke Admin
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-hidden">
            {{ $slot }}
        </main>

        <!-- Footer Shortcuts -->
        <footer class="h-10 bg-slate-700 text-white flex items-center justify-center space-x-6 text-xs flex-shrink-0">
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">F1</kbd> Cari Produk</span>
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">F2</kbd> Bayar</span>
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">F4</kbd> Diskon</span>
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">F9</kbd> Riwayat</span>
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">ESC</kbd> Batal</span>
            <span><kbd class="px-2 py-1 bg-slate-600 rounded">DEL</kbd> Hapus Item</span>
        </footer>
    </div>
</x-layouts.app>