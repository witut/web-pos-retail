<div class="grid grid-cols-1 gap-6 max-w-3xl">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Pengaturan shift management dan pengelolaan kas. Fitur ini akan aktif setelah modul Shift Management diimplementasikan.
                </p>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Mode Shift</label>
        <div class="space-y-2">
            <div class="flex items-center">
                <input id="shift_single" name="shift.mode" type="radio" value="single"
                    {{ old('shift.mode', $settings['shift.mode'] ?? 'multiple') == 'single' ? 'checked' : '' }}
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="shift_single" class="ml-3 block text-sm text-gray-700">
                    1 shift per hari (otomatis)
                </label>
            </div>
            <div class="flex items-center">
                <input id="shift_multiple" name="shift.mode" type="radio" value="multiple"
                    {{ old('shift.mode', $settings['shift.mode'] ?? 'multiple') == 'multiple' ? 'checked' : '' }}
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="shift_multiple" class="ml-3 block text-sm text-gray-700">
                    Multiple shift (manual buka/tutup)
                </label>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Shift per Hari</label>
        <input type="number" name="shift.shifts_per_day" min="1" max="10"
            value="{{ old('shift.shifts_per_day', $settings['shift.shifts_per_day'] ?? '3') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
        <p class="text-xs text-gray-500 mt-1">Hanya berlaku jika mode multiple shift</p>
    </div>

    <div class="border-t pt-4">
        <h4 class="font-medium text-gray-800 mb-3">Kebijakan Shift</h4>
        
        <div class="space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="shift.require_opening_balance" value="1"
                    {{ old('shift.require_opening_balance', $settings['shift.require_opening_balance'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Wajib input modal awal saat buka shift</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="shift.require_close_before_logout" value="1"
                    {{ old('shift.require_close_before_logout', $settings['shift.require_close_before_logout'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Wajib tutup shift sebelum kasir logout</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="shift.require_pin_on_variance" value="1"
                    {{ old('shift.require_pin_on_variance', $settings['shift.require_pin_on_variance'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Require PIN admin jika selisih > toleransi</span>
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi Selisih Kas (Rp)</label>
        <input type="number" name="shift.cash_variance_tolerance" min="0"
            value="{{ old('shift.cash_variance_tolerance', $settings['shift.cash_variance_tolerance'] ?? '5000') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/2">
        <p class="text-xs text-gray-500 mt-1">Selisih di atas angka ini akan memicu warning</p>
    </div>
</div>
