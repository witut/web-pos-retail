<x-layouts.admin :title="'Stock Opname'">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Stock Opname</h2>
            <p class="text-sm text-gray-500">Riwayat opname (cek fisik) stok gudang dan adjustment.</p>
        </div>
        <a href="{{ route('admin.stock.opname.create') }}"
            class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Buat Opname Baru
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nomor Opname</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Dibuat Oleh</th>
                        <th class="px-6 py-4 font-semibold text-center">Total Item</th>
                        <th class="px-6 py-4 font-semibold">Status / Notes</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($opnames as $opname)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $opname->opname_number }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $opname->opname_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $opname->creator->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full text-xs font-bold">{{ $opname->items_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                @if(Str::contains($opname->notes, '[FINALIZED'))
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Selesai
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Draft
                                    </span>
                                @endif
                                <span class="ml-2">{{ Str::limit($opname->notes ?? '', 30) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.stock.opname.show', $opname) }}"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Detail / Input
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900">Belum ada riwayat opname</p>
                                    <p class="text-sm text-gray-500 mt-1">Buat opname baru untuk mulai melakukan stok fisik.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $opnames->links() }}
        </div>
    </div>
</x-layouts.admin>