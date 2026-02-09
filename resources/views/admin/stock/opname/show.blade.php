<x-layouts.admin :title="'Input Fisik Opname'">
    <div x-data="opnameInput()">
        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.stock.opname.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-800">Opname #{{ $opname->opname_number }}</h2>
                </div>
                <p class="text-sm text-gray-500 mt-1 ml-7">
                    Tanggal: {{ $opname->opname_date->format('d M Y') }} &bull;
                    Total Produk: {{ $summary['total_items'] }} &bull;
                    Selisih Item: <span class="font-bold text-red-600">{{ $summary['mismatch_count'] }}</span>
                </p>
            </div>

            <div class="flex gap-2">
                <!-- Save Draft Button -->
                <button type="button" @click="submitForm('draft')"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors shadow-sm">
                    Simpan Draft
                </button>

                <!-- Finalize Button -->
                @if(!Str::contains($opname->notes, '[FINALIZED'))
                    <button type="button" @click="confirmFinalize()"
                        class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium transition-colors shadow-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Selesai & Sesuaikan Stok
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter / Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 sticky top-0 z-10">
            <input type="text" x-model="search" placeholder="Cari nama produk atau kode..."
                class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500">
        </div>

        <!-- Form -->
        <form id="opnameForm" action="{{ route('admin.stock.opname.update', $opname) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="action" id="formAction" value="draft">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-xs">
                            <tr>
                                <th class="px-6 py-4 font-semibold w-1/3">Produk</th>
                                <th class="px-6 py-4 font-semibold text-center w-24">Stok Sistem</th>
                                <th class="px-6 py-4 font-semibold text-center w-32">Fisik (Input)</th>
                                <th class="px-6 py-4 font-semibold text-center w-24">Selisih</th>
                                <th class="px-6 py-4 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($opname->items as $index => $item)
                                <tr class="hover:bg-gray-50 transition-colors"
                                    x-show="matchesSearch('{{ strtolower($item->product->name) }}', '{{ strtolower($item->product->product_code ?? '') }}')">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->product_code ?? '-' }}</div>
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600 bg-gray-50">
                                        {{ $item->system_stock }}
                                        <span class="text-xs text-gray-400 block">{{ $item->product->base_unit }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" step="0.01" name="items[{{ $index }}][physical_stock]"
                                            value="{{ $item->physical_stock }}"
                                            x-model.number="items[{{ $index }}].physical"
                                            class="w-32 text-center rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 font-bold"
                                            :class="items[{{ $index }}].physical != {{ $item->system_stock }} ? 'bg-yellow-50 border-yellow-300 text-yellow-800' : ''">
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold"
                                        :class="getVarianceClass(items[{{ $index }}].physical, {{ $item->system_stock }})">
                                        <span
                                            x-text="(items[{{ $index }}].physical - {{ $item->system_stock }}).toFixed(2)"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" name="items[{{ $index }}][notes]" value="{{ $item->notes }}"
                                            placeholder="Keterangan selisih..."
                                            class="w-full rounded-lg border-gray-300 focus:ring-slate-500 focus:border-slate-500 text-xs">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('opnameInput', () => ({
                    search: '',
                    items: [
                        @foreach($opname->items as $item)
                            { physical: {{ $item->physical_stock }} },
                        @endforeach
                        ],

                    matchesSearch(name, code) {
                        if (this.search === '') return true;
                        const query = this.search.toLowerCase();
                        return name.includes(query) || code.includes(query);
                    },

                    getVarianceClass(physical, system) {
                        const diff = physical - system;
                        if (diff > 0) return 'text-green-600';
                        if (diff < 0) return 'text-red-600';
                        return 'text-gray-400';
                    },

                    submitForm(action) {
                        document.getElementById('formAction').value = action;
                        document.getElementById('opnameForm').submit();
                    },

                    confirmFinalize() {
                        Swal.fire({
                            title: 'Selesaikan Opname?',
                            text: "Stok sistem akan disesuaikan dengan stok fisik yang Anda input. Tindakan ini tidak dapat dibatalkan!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Selesaikan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.submitForm('finalize');
                            }
                        });
                    }
                }));
            });
        </script>
    @endpush
</x-layouts.admin>