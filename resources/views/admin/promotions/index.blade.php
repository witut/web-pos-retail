<x-layouts.admin :title="'Daftar Promosi'">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Promosi</h2>
            <p class="text-gray-500">Kelola diskon dan kampanye penjualan</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}"
            class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Promosi Baru
        </a>
    </div>

    <!-- Promotions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($promotions as $promotion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $promotion->name }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($promotion->description, 50) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg capitalize">
                                {{ str_replace('_', ' ', $promotion->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium">
                            @if($promotion->type === 'percentage')
                                {{ $promotion->value }}%
                            @else
                                Rp {{ number_format($promotion->value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div>{{ $promotion->start_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">s/d
                                {{ $promotion->end_date ? $promotion->end_date->format('d M Y') : 'Seterusnya' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($promotion->is_active)
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aktif</span>
                            @else
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.promotions.edit', $promotion) }}"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus promosi ini?');">
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
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            <p>Belum ada promosi aktif.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($promotions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>