<x-layouts.admin :title="'Pengaturan Toko'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Toko</h2>
        <p class="text-sm text-gray-500">Konfigurasi informasi toko, keuangan, dan fitur sistem.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('POST')

        <div x-data="{ tab: 'general' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Tabs Header -->
            <div class="flex border-b border-gray-100 bg-gray-50/50 overflow-x-auto">
                <button type="button" @click="tab = 'general'"
                    :class="tab === 'general' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Umum
                </button>
                <button type="button" @click="tab = 'financial'"
                    :class="tab === 'financial' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Keuangan
                </button>
                <button type="button" @click="tab = 'security'"
                    :class="tab === 'security' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Keamanan
                </button>
                <button type="button" @click="tab = 'inventory'"
                    :class="tab === 'inventory' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Inventaris
                </button>
                <button type="button" @click="tab = 'customer'"
                    :class="tab === 'customer' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Customer
                </button>
                <button type="button" @click="tab = 'discount'"
                    :class="tab === 'discount' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Diskon
                </button>
                <button type="button" @click="tab = 'shift'"
                    :class="tab === 'shift' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Shift
                </button>
                <button type="button" @click="tab = 'return'"
                    :class="tab === 'return' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Retur
                </button>
                <button type="button" @click="tab = 'printer'"
                    :class="tab === 'printer' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Printer
                </button>
                <button type="button" @click="tab = 'backup'"
                    :class="tab === 'backup' ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex-shrink-0 py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors focus:outline-none">
                    Backup
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="p-6">
                <!-- General Tab -->
                <div x-show="tab === 'general'" x-cloak>
                    @include('admin.settings.tabs.general')
                </div>

                <!-- Financial Tab -->
                <div x-show="tab === 'financial'" x-cloak>
                    @include('admin.settings.tabs.financial')
                </div>

                <!-- Security Tab -->
                <div x-show="tab === 'security'" x-cloak>
                    @include('admin.settings.tabs.security')
                </div>

                <!-- Inventory Tab -->
                <div x-show="tab === 'inventory'" x-cloak>
                    @include('admin.settings.tabs.inventory')
                </div>

                <!-- Customer Tab -->
                <div x-show="tab === 'customer'" x-cloak>
                    @include('admin.settings.tabs.customer')
                </div>

                <!-- Discount Tab -->
                <div x-show="tab === 'discount'" x-cloak>
                    @include('admin.settings.tabs.discount')
                </div>

                <!-- Shift Tab -->
                <div x-show="tab === 'shift'" x-cloak>
                    @include('admin.settings.tabs.shift')
                </div>

                <!-- Return Tab -->
                <div x-show="tab === 'return'" x-cloak>
                    @include('admin.settings.tabs.return')
                </div>


                <!-- Printer Tab -->
                <div x-show="tab === 'printer'" x-cloak>
                    @include('admin.settings.tabs.printer')
                </div>

                <!-- Backup Tab -->
                <div x-show="tab === 'backup'" x-cloak>
                    @include('admin.settings.tabs.backup')
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 font-medium flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin>