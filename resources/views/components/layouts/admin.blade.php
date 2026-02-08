<x-layouts.app :title="$title ?? 'Admin'">
    <div class="h-screen flex overflow-hidden bg-gray-50" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="bg-slate-900 text-white flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            <!-- Logo & Toggle -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between overflow-hidden">
                <div class="flex items-center space-x-3 whitespace-nowrap min-w-0">
                    <div class="w-10 h-10 bg-slate-800 rounded-lg flex-shrink-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 hidden'">
                        <h1 class="text-lg font-bold text-white">POS Retail</h1>
                        <p class="text-xs text-slate-400">Admin Panel</p>
                    </div>
                </div>

                <!-- Collapse Button (Visible only when Open) -->
                <button @click="sidebarOpen = false" x-show="sidebarOpen"
                    class="text-slate-400 hover:text-white transition-colors p-1 rounded-md hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Expand Button (Visible only when Closed) -->
            <div class="w-full flex justify-center py-2" x-show="!sidebarOpen" style="display: none;">
                <button @click="sidebarOpen = true"
                    class="text-slate-400 hover:text-white p-1 rounded-md hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Dashboard">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Dashboard</span>
                </a>

                <!-- MASTER DATA Group -->
                <div class="mt-6 mb-3 transition-opacity duration-300"
                    :class="sidebarOpen ? 'px-3 opacity-100' : 'px-0 opacity-0 hidden'">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Master Data</h3>
                </div>
                <div class="my-3 border-t border-slate-800" :class="!sidebarOpen ? 'block' : 'hidden'"></div>

                <a href="{{ route('admin.products.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.products.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Produk">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Produk</span>
                </a>

                <a href="{{ route('admin.categories.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.categories.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Kategori">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Kategori</span>
                </a>

                <a href="{{ route('admin.suppliers.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.suppliers.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Supplier">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Supplier</span>
                </a>

                <!-- INVENTORY Group -->
                <div class="mt-6 mb-3 transition-opacity duration-300"
                    :class="sidebarOpen ? 'px-3 opacity-100' : 'px-0 opacity-0 hidden'">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Inventory</h3>
                </div>
                <div class="my-3 border-t border-slate-800" :class="!sidebarOpen ? 'block' : 'hidden'"></div>

                <a href="{{ route('admin.stock.receiving.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.stock.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Stock">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Stock</span>
                </a>

                <a href="{{ route('admin.stock.opname.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.stock.opname.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Stock Opname">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Opname / Stok Fisik</span>
                </a>

                <!-- REPORTS Group -->
                <div class="mt-6 mb-3 transition-opacity duration-300"
                    :class="sidebarOpen ? 'px-3 opacity-100' : 'px-0 opacity-0 hidden'">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laporan</h3>
                </div>
                <div class="my-3 border-t border-slate-800" :class="!sidebarOpen ? 'block' : 'hidden'"></div>

                <a href="{{ route('admin.reports.sales') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.reports.sales') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Laporan Penjualan">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Laporan Penjualan</span>
                </a>

                <a href="{{ route('admin.reports.stock') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.reports.stock') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Laporan Stok">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Laporan Stok</span>
                </a>

                <a href="{{ route('admin.reports.profit-loss') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.reports.profit-loss') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Laporan Laba Rugi">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Laporan Laba Rugi</span>
                </a>

                <a href="{{ route('admin.reports.dead_stock') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('admin.reports.dead_stock') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Stok Pasif">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Stok Pasif</span>
                </a>
                <!-- PENGATURAN SYSTEM Group -->
                <div class="px-3 mt-6 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider"
                    :class="!sidebarOpen ? 'text-center' : ''">
                    <span :class="sidebarOpen ? '' : 'hidden'">Sistem</span>
                    <span :class="!sidebarOpen ? '' : 'hidden'">...</span>
                </div>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                        {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="User Management">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Pengguna</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                        {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Pengaturan Toko">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Pengaturan</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                        {{ request()->routeIs('admin.audit-logs.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Audit Log">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Audit Log</span>
                </a>
            </nav>

            <!-- User Info at Bottom -->
            <div class="p-4 border-t border-slate-800 overflow-hidden">
                <div class="flex items-center space-x-3 whitespace-nowrap">
                    <div class="w-9 h-9 bg-slate-700 rounded-full flex-shrink-0 flex items-center justify-center">
                        <span
                            class="text-white font-medium text-sm">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0 transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header
                class="h-16 bg-white border-b border-gray-200 flex flex-shrink-0 items-center justify-between px-6 z-10">
                <h1 class="text-xl font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>

                <div class="flex items-center space-x-4">
                    <!-- POS Button -->
                    <a href="{{ route('pos.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg transition-colors shadow-sm border border-slate-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Buka POS
                    </a>

                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                            <div class="w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center">
                                <span
                                    class="text-white font-medium text-sm">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                            </div>
                            <span class="font-medium hidden md:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <svg class="w-4 h-4 transition-transform hidden md:block" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content (Scrollable) -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>