<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Insert new settings for POS Pro Features
        DB::table('settings')->insert([
            [
                'key' => 'allow_negative_stock',
                'value' => 'false',
                'description' => 'Izinkan penjualan meskipun stok 0 (Stok akan menjadi minus)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pull_out_policy',
                'value' => 'warn', // options: warn, block
                'description' => 'Kebijakan saat produk melewati batas pajang (warn: hanya peringatan, block: tidak bisa dijual)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_debt_tracking',
                'value' => 'true',
                'description' => 'Aktifkan fitur pelacakan hutang ke supplier pada modul pembelian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'allow_negative_stock', 
            'pull_out_policy',
            'enable_debt_tracking'
        ])->delete();
    }
};
