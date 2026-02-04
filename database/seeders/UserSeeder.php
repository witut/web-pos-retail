<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed users:
     * 1. Admin user dengan PIN untuk supervisor override
     * 2. Sample cashier users untuk testing
     */
    public function run(): void
    {
        // 1. Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pos-retail.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'pin' => '123456', // PIN untuk void/override
            'status' => 'active',
        ]);

        echo "✓ Admin user created (email: admin@pos-retail.test, password: password, PIN: 123456)\n";

        // 2. Sample Cashier Users
        $cashiers = [
            [
                'name' => 'Kasir 1 - Budi',
                'email' => 'kasir1@pos-retail.test',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'pin' => null, // Cashier tidak perlu PIN
                'status' => 'active',
            ],
            [
                'name' => 'Kasir 2 - Siti',
                'email' => 'kasir2@pos-retail.test',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'pin' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Kasir 3 - Andi',
                'email' => 'kasir3@pos-retail.test',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'pin' => null,
                'status' => 'inactive', // Sample inactive user
            ],
        ];

        foreach ($cashiers as $cashier) {
            User::create($cashier);
        }

        echo "✓ 3 Cashier users created (kasir1, kasir2, kasir3 - password: password)\n";
        echo "\n";
        echo "=== LOGIN CREDENTIALS ===\n";
        echo "Admin: admin@pos-retail.test / password (PIN: 123456)\n";
        echo "Kasir 1: kasir1@pos-retail.test / password\n";
        echo "Kasir 2: kasir2@pos-retail.test / password\n";
        echo "========================\n";
    }
}
