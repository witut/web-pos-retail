<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show settings form
     */
    public function index()
    {
        // Get all settings as key-value array
        $settings = Setting::allSettings();

        // Default values for missing keys (fallback)
        $defaults = [
            'store_name' => 'Toko Ritel Modern',
            'store_address' => 'Jl. Contoh No. 123',
            'store_phone' => '-',
            'receipt_footer' => 'Terima Kasih',
            'tax_rate' => 11,
            'currency_symbol' => 'Rp',
            'void_time_limit' => 24,
            'low_stock_threshold' => 10,
            'pin_attempt_limit' => 3,
            'pin_lockout_duration' => 15,
        ];

        // Merge defaults with database values
        $settings = array_merge($defaults, $settings);

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'void_time_limit' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            // Add other validations as needed
        ]);

        // List of allowed keys to update
        $allowedKeys = [
            'store_name',
            'store_address',
            'store_phone',
            'receipt_footer',
            'tax_rate',
            'currency_symbol',
            'void_time_limit',
            'low_stock_threshold',
            'pin_attempt_limit',
            'pin_lockout_duration'
        ];

        foreach ($allowedKeys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
