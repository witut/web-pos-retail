<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed default settings untuk aplikasi
     */
    public function run(): void
    {
        $settings = [
            // Tax & Financial
            [
                'key' => 'tax_rate',
                'value' => '11',
                'description' => 'Tarif PPN dalam persen (default: 11%)',
            ],

            // Store Information
            [
                'key' => 'store_name',
                'value' => 'Toko Ritel Modern',
                'description' => 'Nama toko (tampil di struk)',
            ],
            [
                'key' => 'store_address',
                'value' => 'Jl. Contoh No. 123, Jakarta',
                'description' => 'Alamat toko (tampil di struk)',
            ],
            [
                'key' => 'store_phone',
                'value' => '021-12345678',
                'description' => 'Telepon toko (tampil di struk)',
            ],

            // Receipt Settings
            [
                'key' => 'receipt_footer',
                'value' => 'Terima Kasih Atas Kunjungan Anda',
                'description' => 'Footer text di struk',
            ],

            // Transaction Settings
            [
                'key' => 'void_time_limit',
                'value' => '24',
                'description' => 'Batas waktu void transaksi dalam jam (default: 24 jam)',
            ],

            // Stock Settings
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'description' => 'Threshold default untuk low stock alert',
            ],

            // Currency
            [
                'key' => 'currency_symbol',
                'value' => 'Rp',
                'description' => 'Simbol mata uang',
            ],

            // PIN Settings
            [
                'key' => 'pin_attempt_limit',
                'value' => '3',
                'description' => 'Maksimal percobaan PIN salah sebelum lockout',
            ],
            [
                'key' => 'pin_lockout_duration',
                'value' => '15',
                'description' => 'Durasi lockout dalam menit setelah PIN salah',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        echo "✓ " . count($settings) . " default settings created\n";
        echo "  - Tax rate: 11%\n";
        echo "  - Store name: Toko Ritel Modern\n";
        echo "  - Void time limit: 24 hours\n";
        echo "  - Low stock threshold: 10 units\n";
    }
}
