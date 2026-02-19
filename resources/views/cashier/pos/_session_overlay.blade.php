@if(!$session)
    <div class="absolute inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-xl shadow-2xl p-8 text-center border-t-4 border-yellow-400">
            <div class="mb-4 flex justify-center">
                <svg class="h-16 w-16 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-2">Sesi Kasir Belum Dibuka</h2>
            <p class="text-gray-600 mb-8">
                Anda perlu membuka sesi kasir (Input Modal Awal) sebelum dapat memulai transaksi.
            </p>

            <a href="{{ route('cashier.shift.create') }}"
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 w-full transition duration-150 ease-in-out">
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
                Buka Sesi Baru
            </a>

            <div class="mt-4">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('pos.dashboard') }}"
                    class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
@endif