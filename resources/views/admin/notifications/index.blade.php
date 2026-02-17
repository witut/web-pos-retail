<x-layouts.admin :title="'Notifikasi'">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
            <button onclick="markAllAsRead()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Tandai Semua Dibaca
            </button>
        </div>

        @if($notifications->isEmpty())
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                    </path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak ada notifikasi</h3>
                <p class="mt-2 text-sm text-gray-500">Anda akan menerima notifikasi tentang backup, stok rendah, dan
                    aktivitas penting lainnya di sini.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @foreach($notifications as $notification)
                    <div
                        class="px-6 py-4 border-b border-gray-200 hover:bg-gray-50 transition {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                        <div class="flex items-start">
                            {{-- Icon based on type --}}
                            <div class="flex-shrink-0">
                                @if($notification->type === 'backup_failed')
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </span>
                                @elseif($notification->type === 'low_stock')
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 text-yellow-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </span>
                                @elseif($notification->type === 'shift_not_closed')
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-orange-100 text-orange-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="ml-4 flex-1">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900">{{ $notification->title }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ $notification->message }}</p>
                                        <p class="mt-2 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                        @if(!$notification->read_at)
                                            <button onclick="markAsRead({{ $notification->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                Tandai Dibaca
                                            </button>
                                        @else
                                            <span class="text-green-600 text-xs font-medium">✓ Dibaca</span>
                                        @endif
                                        <button onclick="deleteNotification({{ $notification->id }})"
                                            class="text-red-600 hover:text-red-800 text-xs">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <script>
        async function markAsRead(id) {
            try {
                const response = await fetch(`/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        }

        async function markAllAsRead() {
            try {
                const response = await fetch('{{ route("admin.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        }

        async function deleteNotification(id) {
            if (!confirm('Yakin ingin menghapus notifikasi ini?')) {
                return;
            }

            try {
                const response = await fetch(`/admin/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Failed to delete notification:', error);
            }
        }
    </script>
</x-layouts.admin>