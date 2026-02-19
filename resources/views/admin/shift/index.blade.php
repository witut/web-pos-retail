<x-layouts.admin title="Riwayat Sesi Kasir">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Sesi Kasir</h2>
            <p class="text-gray-600">Daftar semua sesi shift kasir.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs text-gray-500 font-semibold">
                    <tr>
                        <th class="px-6 py-4">Kasir</th>
                        <th class="px-6 py-4">Waktu Buka</th>
                        <th class="px-6 py-4">Waktu Tutup</th>
                        <th class="px-6 py-4 text-right">Modal Awal</th>
                        <th class="px-6 py-4 text-right">Total Penjualan</th>
                        <th class="px-6 py-4 text-right">Selisih</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $session->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $session->opened_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $session->closed_at ? $session->closed_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                Rp {{ number_format($session->opening_cash, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                Rp {{ number_format($session->closing_cash ?? 0, 0, ',', '.') }}
                            </td>
                            <td
                                class="px-6 py-4 text-right font-medium {{ ($session->variance ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format($session->variance ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($session->status === 'open')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.shifts.show', $session->id) }}"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-xs uppercase tracking-wide">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Belum ada riwayat sesi kasir.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>