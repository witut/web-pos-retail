<x-layouts.admin :title="'Detail Audit Log'">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.audit-logs.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Aktivitas</h2>
                <p class="text-sm text-gray-500">
                    ID: #{{ $auditLog->id }} &bull;
                    {{ $auditLog->created_at->format('d F Y, H:i:s') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Informasi Dasar</h3>

                <div class="space-y-4">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">User</span>
                        <div class="font-medium text-gray-900 mt-1 flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs">
                                {{ substr($auditLog->user->name ?? 'S', 0, 1) }}
                            </div>
                            {{ $auditLog->user->name ?? 'System' }}
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Tipe Aksi</span>
                        <div class="font-medium text-gray-900 mt-1">
                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-sm font-mono">
                                {{ $auditLog->action_type }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Target Objek</span>
                        <div class="font-medium text-gray-900 mt-1">
                            Tabel: <span class="font-mono">{{ $auditLog->table_name ?? '-' }}</span><br>
                            ID: <span class="font-mono">{{ $auditLog->record_id ?? '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Teknis</span>
                        <div class="text-sm text-gray-600 mt-1">
                            IP: {{ $auditLog->ip_address }}<br>
                            <span class="text-xs text-gray-400 break-all">{{ $auditLog->user_agent }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changes Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
                <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Perubahan Data</h3>

                @if(empty($auditLog->old_values) && empty($auditLog->new_values))
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>Tidak ada data perubahan yang tercatat.</p>
                        <p class="text-sm">(Mungkin aksi ini hanya berupa event tanpa perubahan data spesifik)</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Before -->
                        <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                            <h4 class="font-bold text-red-700 mb-2 text-sm uppercase flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sebelum (Old)
                            </h4>
                            @if($auditLog->old_values)
                                <pre
                                    class="text-xs text-red-900 overflow-x-auto whitespace-pre-wrap font-mono">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <p class="text-sm text-red-400 italic">Data kosong / Baru dibuat</p>
                            @endif
                        </div>

                        <!-- After -->
                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                            <h4 class="font-bold text-green-700 mb-2 text-sm uppercase flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sesudah (New)
                            </h4>
                            @if($auditLog->new_values)
                                <pre
                                    class="text-xs text-green-900 overflow-x-auto whitespace-pre-wrap font-mono">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <p class="text-sm text-green-400 italic">Data dihapus</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>