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
            'tax_type' => 'exclusive', // inclusive or exclusive
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
            // General
            'store_name',
            'store_address',
            'store_phone',
            'receipt_footer',
            'tax_rate',
            'tax_type',
            'currency_symbol',
            'void_time_limit',
            'low_stock_threshold',
            'pin_attempt_limit',
            'pin_lockout_duration',

            // Customer
            'customer.required',
            'customer.loyalty_enabled',
            'customer.points_earn_rate',
            'customer.points_redeem_rate',
            'customer.points_expiry_days',
            'customer.points_with_discount',

            // Discount
            'discount.cashier_manual_allowed',
            'discount.cashier_max_percent',
            'discount.cashier_max_amount',
            'discount.allow_stacking',
            'discount.rounding',

            // Shift
            'shift.mode',
            'shift.shifts_per_day',
            'shift.require_opening_balance',
            'shift.require_close_before_logout',
            'shift.cash_variance_tolerance',
            'shift.require_pin_on_variance',

            // Return
            'return.enabled',
            'return.max_days',
            'return.auto_approve_limit',
            'return.refund_methods',
            'return.restore_stock',
            'return.require_photo',

            // Printer
            'printer.type',
            'printer.server_url',
            'printer.auto_cut',
            'printer.open_drawer',
            'printer.paper_width',
        ];

        // Track changes
        $changes = [];
        $oldSettings = Setting::allSettings();

        foreach ($allowedKeys as $key) {
            if ($request->has($key)) {
                $newValue = $request->input($key);
                $oldValue = $oldSettings[$key] ?? null;

                // Simple comparison (loose)
                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                    Setting::set($key, $newValue);
                }
            }
        }

        if (!empty($changes)) {
            \App\Models\AuditLog::logAction(
                'SETTINGS_UPDATE',
                'settings',
                null,
                array_map(fn($c) => $c['old'], $changes),
                array_map(fn($c) => $c['new'], $changes)
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
