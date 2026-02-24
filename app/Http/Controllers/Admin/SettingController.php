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
            'customer.points_min_transaction',
            'customer.points_with_discount',
            'customer.points_min_redeem',
            'customer.points_max_redeem_percent',
            'customer.points_rounding',

            // Discount
            'discount.cashier_manual_allowed',
            'discount.cashier_max_percent',
            'discount.cashier_max_amount',
            'discount.allow_stacking',
            'discount.rounding',

            // Shift
            'max_active_registers',
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

            // Backup
            'backup_enabled',
            'backup_time',
            'backup_retention_days',
            'backup_email_notification',
            'backup_notification_email',
        ];

        // Track changes
        $changes = [];
        $oldSettings = Setting::allSettings();

        foreach ($allowedKeys as $key) {
            // Because HTML inputs like name="shift.mode" or name="shift[mode]" 
            // are converted to nested arrays by PHP (e.g. ['shift' => ['mode' => '...']])
            // We use dot notation access via $request->input() to retrieve the value properly.
            // But checkboxes might not be present in request if unchecked.

            // To handle unchecked checkboxes (which don't send anything in POST request)
            // we should assume an empty/false value if it's an expected boolean checkbox key.
            // However, a simpler approach is to flatten the incoming request array first
            // to match our dot-notation keys.

            $inputVal = \Illuminate\Support\Arr::get($request->all(), str_replace('.', '_', $key)); // Form sends shift_mode
            // Actually, HTML names like `shift.mode` become `shift_mode` in plain PHP $_POST
            // Wait, Laravel handles `name="shift.mode"` weirdly. Let's look at the view:
            // The view uses: name="shift.shifts_per_day" (literal dot name)
            // PHP automatically converts dots in input names to underscores!
            // So 'shift.shifts_per_day' becomes 'shift_shifts_per_day' in the request!

            $phpKey = str_replace('.', '_', $key);

            if ($request->has($phpKey)) {
                $newValue = $request->input($phpKey);
                $oldValue = $oldSettings[$key] ?? null;

                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                    Setting::set($key, $newValue);
                }
            } else {
                // If it's a checkbox and wasn't sent, it means it was unchecked.
                // We know these keys are checkboxes from the View.
                $checkboxKeys = [
                    'shift.require_opening_balance',
                    'shift.require_close_before_logout',
                    'shift.require_pin_on_variance',
                    'return.enabled',
                    'return.restore_stock',
                    'return.require_photo',
                    'printer.auto_cut',
                    'printer.open_drawer',
                    'backup_enabled',
                    'backup_email_notification',
                    'customer.required',
                    'customer.loyalty_enabled',
                    'customer.points_with_discount',
                    'discount.cashier_manual_allowed',
                    'discount.allow_stacking'
                ];

                if (in_array($key, $checkboxKeys)) {
                    $newValue = '0';
                    $oldValue = $oldSettings[$key] ?? '0';
                    if ($oldValue != $newValue) {
                        $changes[$key] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                        ];
                        Setting::set($key, $newValue);
                    }
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
