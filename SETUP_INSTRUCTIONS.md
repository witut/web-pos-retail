# Setup Laravel Project - LANGKAH SELANJUTNYA 🚀

## ✅ Yang Sudah Selesai

1. **Laravel 12** berhasil diinstall di `/home/gusti/Development/web/pos-retail`
2. **Tailwind CSS v4** + **Alpine.js** sudah dikonfigurasi
3. **16 Database Migrations** sudah dibuat dengan strict decimal typing:
   - `users` (dengan role, PIN, status)
   - `categories` (hierarchical)
   - `products` (decimal pricing & stock)
   - `product_barcodes` (multi-barcode support)
   - `product_units` (UOM system)
   - `suppliers`
   - `stock_receiving` + items
   - `transactions` + items (dengan void tracking)
   - `stock_movements` (kartu stok)
   - `stock_opname` + items
   - `audit_logs`
   - `settings`

## 📋 AKSI YANG DIPERLUKAN (USER)

### 1. Configure Database (.env)

Edit file `.env` di `/home/gusti/Development/web/pos-retail/.env`:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_retail      # <-- Sesuaikan dengan nama database Anda
DB_USERNAME=root            # <-- Sesuaikan username MySQL Anda
DB_PASSWORD=                # <-- Masukkan password MySQL Anda

# Session Driver (PENTING!)
SESSION_DRIVER=database

# Application
APP_NAME="Web-POS Ritel"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### 2. Buat Database MySQL

Jalankan di MySQL:

```sql
CREATE DATABASE pos_retail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau via command line:

```bash
mysql -u root -p -e "CREATE DATABASE pos_retail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. Generate Application Key

```bash
cd /home/gusti/Development/web/pos-retail
php artisan key:generate
```

### 4. Jalankan Migrations

```bash
php artisan migrate
```

**Expected output:**
```
Migration table created successfully.
Migrating: 2026_02_04_000001_create_users_table
Migrated:  2026_02_04_000001_create_users_table (XX.XXms)
Migrating: 2026_02_04_000010_create_categories_table
Migrated:  2026_02_04_000010_create_categories_table (XX.XXms)
...
(16 migrations total)
```

### 5. (Optional) Install Excel & PDF Libraries

Jika koneksi internet sudah stabil:

```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf
```

---

## 🔍 Verifikasi Setup

Setelah migration selesai, verifikasi dengan:

```bash
# 1. Cek tabel yang terbaru dibuat
php artisan migrate:status

# 2. Cek koneksi database
php artisan tinker
>>> \DB::connection()->getDatabaseName();
# Harus return: "pos_retail"

>>> \DB::table('users')->count();
# Harus return: 0 (belum ada user)
```

---

## 📊 Database Schema Summary

Total: **16 tables** dengan strict decimal typing

| Table | Purpose | Critical Fields |
|-------|---------|-----------------|
| `users` | Admin & cashier accounts | role, pin, status |
| `products` | Product master data | selling_price (decimal 15,2), stock_on_hand (decimal 10,2) |
| `transactions` | Sales transactions | total (decimal 15,2), void_reason, voided_by |
| `stock_movements` | Complete stock audit trail | qty (decimal 10,2), stock_before, stock_after |

**Key Design Decisions:**
- ✅ All money fields: `decimal(15,2)` (NOT float/double)
- ✅ All qty/stock fields: `decimal(10,2)` (support kg, liter)
- ✅ All foreign keys: `constrained()` with `onDelete('restrict'/'cascade')`
- ✅ Void tracking: `void_reason`, `void_notes`, `voided_by`, `voided_at`

---

## 🎯 Next Steps (After Migration)

Setelah migration berhasil, beritahu saya untuk melanjutkan ke:

1. **Create Models** dengan relationships
2. **Setup Seeders** (default admin user, sample data)
3. **Configure Tailwind & Alpine** di Vite
4. **Create Service Layer** (TransactionService, StockService)
5. **Build Controllers & Routes**

---

## 🆘 Troubleshooting

### Error: "Access denied for user"
```bash
# Pastikan kredensial MySQL benar di .env
# Test koneksi:
mysql -u root -p -e "SELECT 1;"
```

### Error: "Database does not exist"
```bash
# Buat database dulu:
mysql -u root -p -e "CREATE DATABASE pos_retail;"
```

### Error: "Syntax error... decimal"
```bash
# Pastikan MySQL versi >= 8.0:
mysql --version
# Jika < 8.0, upgrade dulu
```

---

**STATUS SAAT INI:** 🟡 **Menunggu User Configure .env & Run Migrations**

Setelah migration selesai, ketik: **"Migration selesai, lanjut ke step berikutnya"**
