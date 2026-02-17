<x-layouts.admin :title="'Daftar Kupon'">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Kupon</h2>
            <p class="text-gray-500">Kelola kode diskon untuk pelanggan</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}"
            class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Kupon Baru
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Penggunaan</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($coupons as $coupon)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 font-mono">{{ $coupon->code }}</p>
                            @if($coupon->expiry_date)
                                <p class="text-xs text-gray-500">Exp: {{ $coupon->expiry_date->format('d M Y') }}</p>
                            @else
                                <p class="text-xs text-gray-500">Selamanya</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg capitalize">
                                {{ str_replace('_', ' ', $coupon->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium">
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                Rp {{ number_format($coupon->value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">
                            {{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($coupon->is_active)
                                @if($coupon->expiry_date && $coupon->expiry_date->isPast())
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Expired</span>
                                @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Habis</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aktif</span>
                                @endif
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <p>Belum ada kupon.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $coupons->links() }}
        </div>
    </div>
</x-layouts.admin>
