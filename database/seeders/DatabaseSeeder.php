<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Order penting:
     * 1. Users (untuk created_by foreign key)
     * 2. Settings (untuk konfigurasi sistem)
     * 3. Categories (untuk product foreign key)
     * 4. Products (sample data untuk testing)
     */
    public function run(): void
    {
        echo "\n";
        echo "===========================================\n";
        echo "     POS RETAIL - DATABASE SEEDER\n";
        echo "===========================================\n";
        echo "\n";

        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        echo "\n";
        echo "===========================================\n";
        echo "✓ All seeders completed successfully!\n";
        echo "===========================================\n";
        echo "\n";
        echo "Next steps:\n";
        echo "1. php artisan serve (start development server)\n";
        echo "2. Visit: http://localhost:8000\n";
        echo "3. Login dengan kredensial di atas\n";
        echo "\n";
    }
}
