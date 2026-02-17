<x-layouts.admin :title="'Edit Kupon'">
    <div class="max-w-4xl">
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.coupons.index') }}" class="hover:text-gray-700">Kupon</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Edit Kupon</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Kupon: {{ $coupon->code }}</h2>
        </div>

        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" x-data="couponForm()">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kupon <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon <span
                                class="text-red-500">*</span></label>
                        <select name="type" x-model="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed_amount">Potongan Tetap (Rp)</option>
                        </select>
                    </div>

                    <div x-show="type === 'percentage'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon (%) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="value" x-model="percentageValue" :disabled="type !== 'percentage'"
                            step="0.01" min="0" max="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div x-show="type === 'fixed_amount'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Potongan (Rp) <span
                                class="text-red-500">*</span></label>
                        <input type="hidden" name="value" :value="fixedValue" :disabled="type !== 'fixed_amount'">
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">Rp</span>
                            <input type="text" x-model="formattedFixedValue" @input="formatInput"
                                :disabled="type !== 'fixed_amount'"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Limits -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t border-gray-100 pt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batas Penggunaan (Total)</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Total penggunaan saat ini: {{ $coupon->used_count }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kadaluarsa</label>
                        <input type="date" name="expiry_date"
                            value="{{ old('expiry_date', $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center mt-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Aktifkan Kupon Ini</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.coupons.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-medium">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function couponForm() {
                return {
                    type: '{{ old('type', $coupon->type) }}',
                    percentageValue: '{{ old('type', $coupon->type) == 'percentage' ? old('value', $coupon->value) : '' }}',
                    fixedValue: '{{ old('type', $coupon->type) == 'fixed_amount' ? old('value', $coupon->value) : '' }}',
                    formattedFixedValue: '',

                    init() {
                        if (this.fixedValue) {
                            this.formattedFixedValue = new Intl.NumberFormat('id-ID').format(this.fixedValue);
                        }
                    },

                    formatInput(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        this.fixedValue = value;
                        this.formattedFixedValue = value ? new Intl.NumberFormat('id-ID').format(value) : '';
                    }
                }
            }
        </script>
    @endpush
</x-layouts.admin>