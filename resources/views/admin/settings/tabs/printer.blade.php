<div class="grid grid-cols-1 gap-6 max-w-3xl"
    x-data="{ printerType: '{{ old('printer.type', $settings['printer.type'] ?? 'browser') }}' }">
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Pengaturan printer thermal dan direct printing. Fitur ini akan aktif setelah Print Server
                    diimplementasikan.
                </p>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Printer</label>
        <div class="space-y-2">
            <div class="flex items-center">
                <input id="printer_browser" name="printer.type" type="radio" value="browser" x-model="printerType"
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="printer_browser" class="ml-3 block text-sm text-gray-700">
                    Browser Print (window.print)
                </label>
            </div>
            <p class="text-xs text-gray-500 ml-7">Menggunakan dialog print browser (default)</p>

            <div class="flex items-center">
                <input id="printer_escpos" name="printer.type" type="radio" value="escpos" x-model="printerType"
                    class="focus:ring-slate-500 h-4 w-4 text-slate-600 border-gray-300">
                <label for="printer_escpos" class="ml-3 block text-sm text-gray-700">
                    ESC/POS (Direct Print via Print Server)
                </label>
            </div>
            <p class="text-xs text-gray-500 ml-7">Langsung ke thermal printer tanpa dialog (butuh Print Server)</p>
        </div>
    </div>

    <div x-show="printerType === 'escpos'" x-collapse>
        <label class="block text-sm font-medium text-gray-700 mb-1">URL Print Server</label>
        <input type="text" name="printer.server_url"
            value="{{ old('printer.server_url', $settings['printer.server_url'] ?? 'http://localhost:9100') }}"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm"
            placeholder="http://localhost:9100">
        <p class="text-xs text-gray-500 mt-1">Hanya untuk tipe ESC/POS</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Kertas (Lebar/Tipe)</label>
        <select name="printer.paper_width"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 sm:text-sm sm:w-1/3">
            <option value="58" {{ old('printer.paper_width', $settings['printer.paper_width'] ?? '80') == '58' ? 'selected' : '' }}>58mm (Struk Thermal Kecil)</option>
            <option value="80" {{ old('printer.paper_width', $settings['printer.paper_width'] ?? '80') == '80' ? 'selected' : '' }}>80mm (Struk Thermal Besar)</option>
            <option value="faktur" {{ old('printer.paper_width', $settings['printer.paper_width'] ?? '80') == 'faktur' ? 'selected' : '' }}>Faktur (Matrix/A5)</option>
        </select>
    </div>

    <div class="border-t pt-4" x-show="printerType === 'escpos'" x-collapse>
        <h4 class="font-medium text-gray-800 mb-3">Fitur Printer (ESC/POS)</h4>

        <div class="space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="printer.auto_cut" value="1" {{ old('printer.auto_cut', $settings['printer.auto_cut'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Auto-cut kertas setelah print</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="printer.open_drawer" value="1" {{ old('printer.open_drawer', $settings['printer.open_drawer'] ?? '1') == '1' ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-600 shadow-sm focus:border-slate-300 focus:ring focus:ring-slate-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm font-medium text-gray-700">Buka cash drawer otomatis (pembayaran tunai)</span>
            </label>
        </div>
    </div>
</div>