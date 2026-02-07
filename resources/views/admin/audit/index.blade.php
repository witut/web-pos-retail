<x-layouts.admin :title="'Audit Log'">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Audit Log</h2>
            <p class="text-sm text-gray-500">Rekaman aktivitas penting dalam sistem untuk keamanan dan pemantauan.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <!-- Filter Tanggal -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 text-sm">
            </div>

            <!-- Filter User -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Filter User</label>
                <select name="user_id"
                    class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 text-sm">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Action Type -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Aksi</label>
                <select name="action_type"
                    class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 text-sm">
                    <option value="">Semua Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                <a href="{{ route('admin.audit-logs.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
                    Reset
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 text-sm font-medium transition-colors">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Waktu</th>
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Action Type</th>
                        <th class="px-6 py-4 font-semibold">Deskripsi (Tabel/Record)</th>
                        <th class="px-6 py-4 font-semibold">IP Address</th>
                        <th class="px-6 py-4 font-semibold text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $color = match ($log->action_type) {
                                        'VOID_TRANSACTION', 'PIN_OVERRIDE' => 'bg-red-100 text-red-800',
                                        'PRICE_CHANGE', 'STOCK_ADJUSTMENT' => 'bg-orange-100 text-orange-800',
                                        'USER_CREATED', 'USER_LOGIN' => 'bg-green-100 text-green-800',
                                        default => 'bg-blue-100 text-blue-800',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                    {{ $log->action_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <div class="font-medium text-gray-900">{{ $log->table_name }} #{{ $log->record_id }}</div>
                                <!-- Optional: Show short description logic here if needed, or rely on show page -->
                            </td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                    class="text-slate-600 hover:text-slate-900 font-medium text-sm inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat
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
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900">Belum ada log aktivitas</p>
                                    <p class="text-sm text-gray-500 mt-1">Sistem belum mencatat aktivitas sensitif apapun.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts.admin>