<x-layouts.app :title="$title ?? 'Cashier Dashboard'">
    <div class="h-screen flex overflow-hidden bg-gray-50" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="bg-slate-900 text-white flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            <!-- Logo & Toggle -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between overflow-hidden">
                <div class="flex items-center space-x-3 whitespace-nowrap min-w-0">
                    <div class="w-10 h-10 bg-emerald-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 hidden'">
                        <h1 class="text-lg font-bold text-white">POS Retail</h1>
                        <p class="text-xs text-emerald-400">Cashier Panel</p>
                    </div>
                </div>

                <!-- Collapse Button -->
                <button @click="sidebarOpen = false" x-show="sidebarOpen"
                    class="text-slate-400 hover:text-white transition-colors p-1 rounded-md hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Expand Button -->
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
                <a href="{{ route('pos.dashboard') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('pos.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Dashboard">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Dashboard</span>
                </a>

                <!-- POS Terminal -->
                <a href="{{ route('pos.index') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('pos.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="POS Terminal">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">POS Terminal</span>
                </a>

                <!-- Riwayat Transaksi -->
                <a href="{{ route('pos.history') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('pos.history') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Riwayat Transaksi">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Riwayat Transaksi</span>
                </a>

                <div class="my-3 border-t border-slate-800"></div>

                <!-- Profil -->
                <a href="{{ route('pos.profile') }}"
                    class="flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap
                          {{ request()->routeIs('pos.profile') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}"
                    title="Profil Saya">
                    <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Profil Saya</span>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-3 py-2.5 rounded-lg mb-1 transition-all group whitespace-nowrap text-red-400 hover:bg-red-900/20 hover:text-red-300"
                        title="Logout">
                        <svg class="w-5 h-5 flex-shrink-0 transition-colors"
                            :class="sidebarOpen ? 'mr-3' : 'mr-0 mx-auto'" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium transition-opacity duration-300"
                            :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">Logout</span>
                    </button>
                </form>

            </nav>

            <!-- User Info at Bottom -->
            <div class="p-4 border-t border-slate-800 overflow-hidden">
                <div class="flex items-center space-x-3 whitespace-nowrap">
                    <div class="w-9 h-9 bg-slate-700 rounded-full flex-shrink-0 flex items-center justify-center">
                        <span
                            class="text-white font-medium text-sm">{{ substr(auth()->user()->name ?? 'K', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0 transition-opacity duration-300"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 hidden'">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Kasir' }}</p>
                        <p class="text-xs text-slate-400 truncate">Kasir</p>
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

                <div class="text-sm text-gray-500">
                    {{ now()->translatedFormat('l, d F Y') }}
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