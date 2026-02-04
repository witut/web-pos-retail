<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed 8 sample suppliers with realistic data
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-00001',
                'name' => 'PT. Indo Elektronik',
                'address' => 'Jl. Sudirman No. 123, Jakarta 12190',
                'phone' => '021-5551234',
                'email' => 'sales@indoelektronik.com',
                'contact_person' => 'Budi Santoso (081234567890)',
                'payment_terms' => '30 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00002',
                'name' => 'CV. Fashion Indonesia',
                'address' => 'Jl. Thamrin No. 456, Jakarta 10230',
                'phone' => '021-5559876',
                'email' => 'order@fashionindo.com',
                'contact_person' => 'Siti Nurhaliza (081298765432)',
                'payment_terms' => '14 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00003',
                'name' => 'PT. Maju Bersama',
                'address' => 'Jl. Gatot Subroto No. 789, Bandung 40262',
                'phone' => '022-4445678',
                'email' => 'supplier@majubersama.co.id',
                'contact_person' => 'Andi Wijaya (085612345678)',
                'payment_terms' => '30 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00004',
                'name' => 'Toko Snack Jaya',
                'address' => 'Jl. Ahmad Yani No. 321, Surabaya 60234',
                'phone' => '031-7778899',
                'email' => 'info@snackjaya.com',
                'contact_person' => 'Dewi Lestari (082345678901)',
                'payment_terms' => 'COD',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00005',
                'name' => 'PT. Minuman Segar',
                'address' => 'Jl. Pemuda No. 654, Semarang 50132',
                'phone' => '024-6667788',
                'email' => 'sales@minumansegar.com',
                'contact_person' => 'Rudi Hartono (081876543210)',
                'payment_terms' => '7 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00006',
                'name' => 'CV. Alat Tulis Mandiri',
                'address' => 'Jl. Sisingamangaraja No. 111, Medan 20212',
                'phone' => '061-4443322',
                'email' => 'order@alattulis.co.id',
                'contact_person' => 'Lisa Anggraeni (081567890123)',
                'payment_terms' => '14 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00007',
                'name' => 'PT. Gadget Center',
                'address' => 'Jl. Kuningan No. 222, Jakarta 12940',
                'phone' => '021-3334455',
                'email' => 'b2b@gadgetcenter.com',
                'contact_person' => 'Arif Rahman (082198765432)',
                'payment_terms' => '30 Days',
                'status' => 'active',
            ],
            [
                'code' => 'SUP-00008',
                'name' => 'Toko Grosir Sejahtera',
                'address' => 'Jl. Malioboro No. 88, Yogyakarta 55271',
                'phone' => '0274-888999',
                'email' => 'grosir@sejahtera.com',
                'contact_person' => 'Joko Widodo (085234567890)',
                'payment_terms' => 'COD',
                'status' => 'inactive', // Sample inactive supplier
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        echo "✓ 8 Suppliers created (7 active, 1 inactive)\n";
        echo "✓ Supplier codes: SUP-00001 to SUP-00008\n";
    }
}
