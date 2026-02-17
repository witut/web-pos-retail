{{-- Notification Bell Component - Alpine.js --}}
<div x-data="notificationBell()" x-init="init()" class="relative">
    {{-- Bell Icon with Badge --}}
    <button @click="toggleDropdown()"
        class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-lg transition">
        {{-- Bell Icon (from Heroicons) --}}
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
            </path>
        </svg>

        {{-- Unread Badge --}}
        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px]">
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="isOpen" @click.away="closeDropdown()" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        style="display: none;">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
            <button @click="markAllAsRead()" x-show="unreadCount > 0"
                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Tandai Semua Dibaca
            </button>
        </div>

        {{-- Notification List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <p class="mt-2 text-sm">Tidak ada notifikasi</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div @click="handleNotificationClick(notification)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition"
                    :class="{ 'bg-blue-50': !notification.read_at }">

                    <div class="flex items-start">
                        {{-- Icon based on type --}}
                        <div class="flex-shrink-0">
                            <template x-if="notification.type === 'backup_failed'">
                                <span
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-100 text-red-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="notification.type === 'low_stock'">
                                <span
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-yellow-100 text-yellow-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="notification.type === 'shift_not_closed'">
                                <span
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 text-orange-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                            </template>
                            <template
                                x-if="!['backup_failed', 'low_stock', 'shift_not_closed'].includes(notification.type)">
                                <span
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                            </template>
                        </div>

                        {{-- Content --}}
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-600 line-clamp-2" x-text="notification.message"></p>
                            <p class="mt-1 text-xs text-gray-500" x-text="formatTimeAgo(notification.created_at)"></p>
                        </div>

                        {{-- Unread indicator --}}
                        <div class="ml-2 flex-shrink-0" x-show="!notification.read_at">
                            <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-gray-200 text-center">
            <a href="{{ route('admin.notifications.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>

<script>
    function notificationBell() {
        return {
            isOpen: false,
            unreadCount: 0,
            notifications: [],
            refreshInterval: null,

            init() {
                this.fetchNotifications();
                // Auto-refresh every 60 seconds
                this.refreshInterval = setInterval(() => {
                    this.fetchNotifications();
                }, 60000);
            },

            toggleDropdown() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.fetchNotifications();
                }
            },

            closeDropdown() {
                this.isOpen = false;
            },

            async fetchNotifications() {
                try {
                    // Fetch unread count
                    const countResponse = await fetch('{{ route("admin.notifications.unread-count") }}');
                    const countData = await countResponse.json();
                    this.unreadCount = countData.count;

                    // Fetch recent notifications (only when dropdown is open)
                    if (this.isOpen) {
                        const response = await fetch('{{ route("admin.notifications.index") }}?ajax=1&limit=10');
                        const data = await response.json();
                        this.notifications = data.notifications || [];
                    }
                } catch (error) {
                    console.error('Failed to fetch notifications:', error);
                }
            },

            async handleNotificationClick(notification) {
                // Mark as read if unread
                if (!notification.read_at) {
                    try {
                        await fetch(`{{ url('/admin/notifications') }}/${notification.id}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        // Update local state
                        notification.read_at = new Date().toISOString();
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                }

                // Navigate based on notification type
                // You can customize this based on notification data
                if (notification.data?.url) {
                    window.location.href = notification.data.url;
                }
            },

            async markAllAsRead() {
                try {
                    const response = await fetch('{{ route("admin.notifications.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        // Update local state
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    }
                } catch (error) {
                    console.error('Failed to mark all as read:', error);
                }
            },

            formatTimeAgo(datetime) {
                const date = new Date(datetime);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);

                if (seconds < 60) return 'Baru saja';
                if (seconds < 3600) return `${Math.floor(seconds / 60)} menit yang lalu`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
                if (seconds < 604800) return `${Math.floor(seconds / 86400)} hari yang lalu`;

                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            }
        }
    }
</script>