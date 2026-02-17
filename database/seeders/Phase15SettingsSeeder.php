<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class Phase15SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Customer Management Settings
            [
                'key' => 'customer.required',
                'value' => '0',
                'group' => 'customer',
                'description' => 'Wajibkan input pelanggan di setiap transaksi'
            ],
            [
                'key' => 'customer.loyalty_enabled',
                'value' => '1',
                'group' => 'customer',
                'description' => 'Aktifkan sistem poin loyalty'
            ],
            [
                'key' => 'customer.points_earn_rate',
                'value' => '10000:1',
                'group' => 'customer',
                'description' => 'Konversi pembelian ke poin (Rp:Poin)'
            ],
            [
                'key' => 'customer.points_redeem_rate',
                'value' => '100:10000',
                'group' => 'customer',
                'description' => 'Konversi poin ke diskon (Poin:Rp)'
            ],
            [
                'key' => 'customer.points_expiry_days',
                'value' => '365',
                'group' => 'customer',
                'description' => 'Masa berlaku poin (hari, 0 = tidak ada batas)'
            ],
            [
                'key' => 'customer.points_min_transaction',
                'value' => '0',
                'group' => 'customer',
                'description' => 'Minimal total belanja untuk dapat poin'
            ],
            [
                'key' => 'customer.points_with_discount',
                'value' => '1',
                'group' => 'customer',
                'description' => 'Poin bisa digunakan bersamaan dengan diskon'
            ],
            [
                'key' => 'customer.cashier_can_create',
                'value' => '1',
                'group' => 'customer',
                'description' => 'Izinkan kasir untuk menambahkan pelanggan baru di POS'
            ],
            [
                'key' => 'customer.cashier_limit_enabled',
                'value' => '0',
                'group' => 'customer',
                'description' => 'Aktifkan pembatasan jumlah pelanggan baru per hari per kasir'
            ],
            [
                'key' => 'customer.cashier_daily_limit',
                'value' => '20',
                'group' => 'customer',
                'description' => 'Maksimal pelanggan baru yang bisa dibuat kasir per hari'
            ],

            // Discount Settings
            [
                'key' => 'discount.cashier_manual_allowed',
                'value' => '1',
                'group' => 'discount',
                'description' => 'Izinkan kasir input diskon manual'
            ],
            [
                'key' => 'discount.cashier_max_percent',
                'value' => '10',
                'group' => 'discount',
                'description' => 'Maksimal diskon % tanpa approval admin'
            ],
            [
                'key' => 'discount.cashier_max_amount',
                'value' => '50000',
                'group' => 'discount',
                'description' => 'Maksimal diskon Rp tanpa approval admin'
            ],
            [
                'key' => 'discount.allow_stacking',
                'value' => '1',
                'group' => 'discount',
                'description' => 'Izinkan diskon bertumpuk (produk + voucher + poin)'
            ],
            [
                'key' => 'discount.rounding',
                'value' => 'down',
                'group' => 'discount',
                'description' => 'Pembulatan diskon (down/up/none)'
            ],

            // Shift Management Settings
            [
                'key' => 'shift.mode',
                'value' => 'multiple',
                'group' => 'shift',
                'description' => 'Mode shift (single/multiple)'
            ],
            [
                'key' => 'shift.shifts_per_day',
                'value' => '3',
                'group' => 'shift',
                'description' => 'Jumlah shift per hari (jika mode multiple)'
            ],
            [
                'key' => 'shift.require_opening_balance',
                'value' => '1',
                'group' => 'shift',
                'description' => 'Wajib input modal awal saat buka shift'
            ],
            [
                'key' => 'shift.require_close_before_logout',
                'value' => '1',
                'group' => 'shift',
                'description' => 'Wajib tutup shift sebelum kasir logout'
            ],
            [
                'key' => 'shift.cash_variance_tolerance',
                'value' => '5000',
                'group' => 'shift',
                'description' => 'Toleransi selisih kas (Rp)'
            ],
            [
                'key' => 'shift.require_pin_on_variance',
                'value' => '1',
                'group' => 'shift',
                'description' => 'Require PIN admin jika selisih > toleransi'
            ],

            // Return & Refund Settings
            [
                'key' => 'return.enabled',
                'value' => '1',
                'group' => 'return',
                'description' => 'Aktifkan fitur retur'
            ],
            [
                'key' => 'return.max_days',
                'value' => '7',
                'group' => 'return',
                'description' => 'Batas waktu retur (hari sejak pembelian)'
            ],
            [
                'key' => 'return.auto_approve_limit',
                'value' => '100000',
                'group' => 'return',
                'description' => 'Auto-approve untuk nilai ≤ Rp (0 = semua butuh approval)'
            ],
            [
                'key' => 'return.refund_methods',
                'value' => 'cash,exchange,credit',
                'group' => 'return',
                'description' => 'Metode refund yang diizinkan (comma-separated)'
            ],
            [
                'key' => 'return.restore_stock',
                'value' => '1',
                'group' => 'return',
                'description' => 'Kembalikan stok otomatis saat retur approved'
            ],
            [
                'key' => 'return.require_photo',
                'value' => '0',
                'group' => 'return',
                'description' => 'Require foto bukti untuk retur (rusak/cacat)'
            ],

            // Printer Settings
            [
                'key' => 'printer.type',
                'value' => 'browser',
                'group' => 'printer',
                'description' => 'Tipe printer (browser/escpos)'
            ],
            [
                'key' => 'printer.server_url',
                'value' => 'http://localhost:9100',
                'group' => 'printer',
                'description' => 'URL print server (untuk ESC/POS)'
            ],
            [
                'key' => 'printer.auto_cut',
                'value' => '1',
                'group' => 'printer',
                'description' => 'Auto-cut kertas setelah print'
            ],
            [
                'key' => 'printer.open_drawer',
                'value' => '1',
                'group' => 'printer',
                'description' => 'Buka cash drawer otomatis (pembayaran tunai)'
            ],
            [
                'key' => 'printer.paper_width',
                'value' => '80',
                'group' => 'printer',
                'description' => 'Lebar kertas thermal (mm: 58/80)'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('Phase 1.5 settings seeded successfully!');
    }
}
