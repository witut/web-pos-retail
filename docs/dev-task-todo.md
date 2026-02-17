# POS Retail - Development Task List

## Project Overview
Building a comprehensive Point of Sale (POS) system for retail businesses with inventory management, reporting, and multi-user support.

**Tech Stack**: Laravel 11, MySQL, Alpine.js, Tailwind CSS  
**Timeline**: 16 weeks (4 months)  
**Current Phase**: Phase 1.5 - Advanced Features

---

## Phase 1: Foundation (Week 1-5) ✅ COMPLETED

### Week 1-2: Project Setup & Authentication ✅
- [x] Laravel 11 project initialization
- [x] Database design & migrations
- [x] Authentication system (Laravel Breeze)
- [x] Role-based middleware (Admin, Cashier)
- [x] Basic layouts (Admin, Cashier, POS)

### Week 3-4: Core Models & Relationships ✅
- [x] Product model (SKU, name, price, stock)
- [x] Category model
- [x] Supplier model
- [x] Stock Movement model
- [x] Transaction model
- [x] User model (with roles)

### Week 5: Admin CRUD ✅
- [x] Product management (CRUD)
- [x] Category management
- [x] Supplier management
- [x] User management

---

## Phase 2: Inventory Management (Week 6-9) ✅ COMPLETED

### Week 6-7: Stock Receiving ✅
- [x] Stock receiving form (supplier, products, qty, cost)
- [x] HPP calculation (weighted average)
- [x] Stock movement logging
- [x] Receiving history & reports

### Week 8: Stock Opname ✅
- [x] Stock opname interface (physical count)
- [x] Variance calculation (system vs physical)
- [x] Stock adjustment with approval
- [x] Opname reports

### Week 9: Stock Reports ✅
- [x] Stock card (per product movement history)
- [x] Low stock alerts
- [x] Dead stock report (no sales > 30 days)
- [x] Stock valuation report

---

## Phase 3: POS Terminal (Week 10-11) ✅ COMPLETED

### Week 10: POS Interface ✅

#### Models & Database
- [x] [Transaction](file:///home/gusti/Development/web/pos-retail/app/Models/Transaction.php#38-284) model (invoice, total, payment method, status)
- [x] [TransactionItem](file:///home/gusti/Development/web/pos-retail/database/factories/TransactionItemFactory.php#12-36) model (product, qty, price, subtotal)
- [x] Migration: [transactions](file:///home/gusti/Development/web/pos-retail/app/Models/User.php#78-87) table
- [x] Migration: `transaction_items` table

#### Service Layer
- [x] `TransactionService::createTransaction()` - Main checkout logic
- [x] `TransactionService::voidTransaction()` - Cancel with stock restore
- [x] `TransactionService::generateInvoiceNumber()` - Sequential
- [x] `TransactionService::calculateTotal()` - Subtotal, tax, total

#### Controllers
- [x] `Cashier/POSController::index()` - Main POS screen
- [x] `Cashier/POSController::searchProduct()` - AJAX barcode scan
- [x] `Cashier/POSController::checkout()` - Process payment
- [x] `Cashier/POSController::history()` - Today's transactions
- [x] `Cashier/POSController::void()` - Void with admin PIN
- [x] `Cashier/POSController::print()` - Receipt

#### Views & Frontend
- [x] Layout: [layouts/pos.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/components/layouts/pos.blade.php) (fullscreen)
- [x] View: [cashier/pos/index.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/cashier/pos/index.blade.php) (split screen)
- [x] View: `cashier/pos/history.blade.php`
- [x] View: `cashier/pos/detail.blade.php`
- [x] View: [cashier/pos/receipt.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/cashier/pos/receipt.blade.php) (thermal 80mm)
- [x] Alpine.js component: Shopping cart with real-time calc
- [x] Alpine.js component: Payment modal
- [x] Alpine.js component: Void modal (PIN input)

#### Features
- [x] Barcode scanner integration (auto-focus input)
- [x] Product search autocomplete
- [x] Shopping cart management (add/edit/delete)
- [x] Real-time total calculation (subtotal, tax, total)
- [x] Multiple payment methods (cash, card, QRIS, transfer)
- [x] Change calculation for cash payment
- [x] Keyboard shortcuts (F1, F2, F9, ESC, DEL)
- [x] Cart LocalStorage persistence (restore on refresh)
- [x] Pre-checkout: Delete item (no PIN required)
- [x] Pre-checkout: Cancel transaction (simple confirm)
- [x] Post-checkout: Void transaction (PIN required)
- [x] Receipt printing (browser window.print)
- [x] Reprint from history

---

## Week 12: Dashboard & Reports ✅ COMPLETED

### Service Layer
- [x] `ReportService::salesDaily()` - Daily sales report
- [x] `ReportService::salesPeriod()` - Period comparison
- [x] `ReportService::productSales()` - Per product analysis
- [x] `ReportService::cashierPerformance()` - Cashier KPIs
- [x] `ReportService::stockValuation()` - Inventory value
- [x] `ReportService::profitLoss()` - Basic P&L

### Controllers & Views
- [x] `Admin/DashboardController::index()` - Stats & charts
- [x] `Admin/ReportController` - All report methods
- [x] View: Dashboard with KPI cards
- [x] View: Sales reports (daily, period, product, cashier)
- [x] View: Stock reports (valuation, movement, low stock)
- [x] View: Stock reports (dead stock/slow moving items)
- [x] View: Financial reports (P&L, cash flow)

### Features
- [x] Dashboard KPI cards (today, this month)
- [x] Sales trend chart (last 30 days)
- [x] Cashier Dashboard (Stats & Profile)
- [x] Tax Inclusive/Exclusive Setting
- [x] Payment method breakdown (in Sales Report)
- [x] Top 5 selling products
- [x] Low stock alerts (stock < min threshold)
- [x] Dead stock alerts (no sales > 30 days)
- [x] Export reports to Excel
- [x] Export reports to PDF (via DomPDF)
- [x] POS UX Improvements (Auto-focus, Currency Formatting)
- [x] Multi-Unit Support (UOM)
- [x] UI Improvements (SweetAlert2, Stock Validation Tooltip)

---

## Week 13: Settings, Security & Audit ✅ COMPLETED

### Models & Services
- [x] Model: `AuditLog`
- [x] Model: [Setting](file:///home/gusti/Development/web/pos-retail/app/Models/Setting.php#28-208)
- [x] `AuditLog::logAction()` - Audit trail logging
- [x] Middleware: [CheckRole](file:///home/gusti/Development/web/pos-retail/app/Http/Middleware/CheckRole.php#19-52) - Role-based access

### Controllers & Views
- [x] `Admin/UserController` - User management
- [x] `Admin/SettingController` - Store settings
- [x] View: User management (create cashier, reset PIN)
- [x] View: Settings form (tax rate, void limit, receipt template)

### Features
- [x] User CRUD (admin creates cashiers)
- [x] Admin PIN setup (hashed with bcrypt)
- [x] PIN attempt limit (3 attempts, 15-min lockout)
- [x] Store settings (tax rate, void time limit, receipt template)
- [x] Audit logging (void, price change, admin PIN usage, settings update, login/logout, user mgmt)
- [x] Stock opname interface (Create, Edit, Finalize with Snapshot & Adjustment)
- [x] Failed login attempt tracking (via Audit Log)

---

## Week 14: Testing & QA ✅ PARTIALLY COMPLETED

### Unit Tests
- [x] Test: HPP weighted average calculation
- [x] Test: Stock deduction with UOM conversion
- [ ] Test: Invoice number generation (sequential)
- [x] Test: Void transaction stock restoration (Service Level)
- [ ] Test: Cart total calculation accuracy
- [x] Test: StockService (Basic Operations)

### Feature Tests
- [x] Test: Complete POS transaction flow
- [x] Test: Void transaction (with PIN validation)
- [ ] Test: Stock receiving HPP update
- [ ] Test: Stock insufficient warning
- [ ] Test: Pre-checkout delete (no PIN)
- [ ] Test: Post-checkout void (PIN required)

### Integration Testing
- [ ] Test: Barcode scanner hardware
- [ ] Test: Thermal printer (58mm & 80mm)
- [ ] Test: Keyboard shortcuts (all F-keys)
- [ ] Test: Browser compatibility (Chrome, Firefox, Edge, Safari)
- [ ] Test: Performance (50 concurrent users simulation)

### Bug Fixing
- [ ] Fix P0 bugs (critical)
- [ ] Fix P1 bugs (high priority)
- [ ] Fix P2 bugs (medium priority)
- [ ] Code review & refactoring
- [x] Fix: Prevent auto-submit on barcode scan (create/edit product) ✅
- [x] Fix: Auto-focus new barcode input field (create/edit product) ✅
- [x] Feature: Currency formatting on input (Selling Price & POS Amount Paid) ✅
- [x] Feature: Currency formatting on input (Cost Price) ✅

---

## Phase 1.5: Advanced Features (Week 15-18) 🔨 IN PROGRESS

### Week 15: Foundation & Data Management

#### 1.1 Settings Infrastructure Enhancement ✅ COMPLETED
- [x] Add `group` column to settings table
- [x] Create [SettingService](file:///home/gusti/Development/web/pos-retail/app/Services/SettingService.php#12-189) with type-safe helpers
- [x] Seed default settings for Phase 1.5 modules
- [x] Extend [SettingController](file:///home/gusti/Development/web/pos-retail/app/Http/Controllers/Admin/SettingController.php#9-141) for new settings
- [x] Refactor settings UI into modular tabs
  - [x] Customer settings tab
  - [x] Discount settings tab
  - [x] Shift settings tab
  - [x] Return settings tab
  - [x] Printer settings tab

**Files:**
- [database/migrations/2026_02_12_090334_extend_settings_table_add_group.php](file:///home/gusti/Development/web/pos-retail/database/migrations/2026_02_12_090334_extend_settings_table_add_group.php)
- [app/Services/SettingService.php](file:///home/gusti/Development/web/pos-retail/app/Services/SettingService.php)
- [database/seeders/Phase15SettingsSeeder.php](file:///home/gusti/Development/web/pos-retail/database/seeders/Phase15SettingsSeeder.php)
- [app/Http/Controllers/Admin/SettingController.php](file:///home/gusti/Development/web/pos-retail/app/Http/Controllers/Admin/SettingController.php)
- [resources/views/admin/settings/index.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/admin/settings/index.blade.php)
- `resources/views/admin/settings/tabs/*.blade.php`

---

#### 1.2a Product Form Enhancement ✅ COMPLETED
- [x] Add `product_type` column (inventory/service)
- [x] Update Product model with product_type
- [x] Add Product Type selector in create form
- [x] Implement Margin Calculator (2-way binding)
  - [x] Calculate selling price from margin %
  - [x] Calculate margin % from selling price
- [x] Add Barcode field per Unit in UOM section
- [x] Add Margin Calculator per Unit
- [x] Update Alpine.js with calculation functions
- [x] Update ProductController validation
  - [x] Add product_type validation
  - [x] Add unit barcode validation
  - [x] Handle barcode storage per unit
- [x] Update edit.blade.php with same enhancements
- [x] Manual testing (create form tested by user)

**Files:**
- [database/migrations/2026_02_12_190210_add_product_type_to_products_table.php](file:///home/gusti/Development/web/pos-retail/database/migrations/2026_02_12_190210_add_product_type_to_products_table.php)
- [app/Models/Product.php](file:///home/gusti/Development/web/pos-retail/app/Models/Product.php)
- [app/Http/Controllers/Admin/ProductController.php](file:///home/gusti/Development/web/pos-retail/app/Http/Controllers/Admin/ProductController.php)
- [resources/views/admin/products/create.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/admin/products/create.blade.php)
- [resources/views/admin/products/edit.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/admin/products/edit.blade.php)

---

#### 1.2b Import/Export Products ✅ COMPLETED
- [x] Install `maatwebsite/excel` package
- [x] Create [ProductImport](file:///home/gusti/Development/web/pos-retail/app/Imports/ProductImport.php#13-155) class
  - [x] Validasi: SKU, Nama, Harga required
  - [x] Validasi: Harga Jual ≥ Harga Beli
  - [x] Handle SKU duplicate (update existing)
  - [x] Auto-create kategori jika belum ada
- [x] Create [ProductImportController](file:///home/gusti/Development/web/pos-retail/app/Http/Controllers/Admin/ProductImportController.php#11-141)
- [x] Create import UI
  - [x] Upload form (.xlsx atau .csv)
  - [x] Download template button
  - [x] Error display with toggle details
  - [x] Success/warning messages
- [x] Create template Excel/CSV (auto-generated)
- [x] Create [ProductExport](file:///home/gusti/Development/web/pos-retail/app/Exports/ProductExport.php#12-89) class
- [x] Add import/export buttons to product index

**Files:**
- [app/Imports/ProductImport.php](file:///home/gusti/Development/web/pos-retail/app/Imports/ProductImport.php)
- [app/Exports/ProductExport.php](file:///home/gusti/Development/web/pos-retail/app/Exports/ProductExport.php)
- [app/Http/Controllers/Admin/ProductImportController.php](file:///home/gusti/Development/web/pos-retail/app/Http/Controllers/Admin/ProductImportController.php)
- [resources/views/admin/products/import.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/admin/products/import.blade.php)
- [resources/views/admin/products/index.blade.php](file:///home/gusti/Development/web/pos-retail/resources/views/admin/products/index.blade.php) (updated)
- [routes/web.php](file:///home/gusti/Development/web/pos-retail/routes/web.php) (updated)

**Template Columns:**
SKU | Nama Produk | Product Type | Kategori | Brand | Harga Pokok (HPP) | Harga Jual | Stok Awal | Min Stock Alert | Unit Dasar | Barcode | Status | Deskripsi


---

#### 1.3 Backup & Restore Database
- [ ] Install `spatie/laravel-backup` package
- [ ] Configure backup destinations (local + cloud)
- [ ] Create `BackupController`
  - [ ] Manual backup
  - [ ] List backup files
  - [ ] Download backup
  - [ ] Restore dengan safety check
- [ ] Setup scheduled backup
  - [ ] Daily at 23:00
  - [ ] Email notification jika gagal
- [ ] Create backup management UI
  - [ ] List backups (size, date, type)
  - [ ] Download button
  - [ ] Restore button (dengan konfirmasi)
  - [ ] Auto-backup before restore
  - [ ] Configure schedule settings

**Files:**
- `app/Http/Controllers/Admin/BackupController.php`
- `resources/views/admin/backups/index.blade.php`
- `config/backup.php`

**Scheduled Task:**
```php
$schedule->command('backup:run')->daily()->at('23:00');

Week 16: Direct Printing System
2.1 Print Server (Node.js)
 Setup Node.js project dengan Express
 Install dependencies
 express
 escpos
 escpos-usb
 Implement /print endpoint
 Implement ESC/POS receipt formatting
 Header (store name, address, phone)
 Items (name, qty, price, subtotal)
 Footer (total, payment, change)
 Add auto-cut command
 Add cash drawer open command
 Test dengan berbagai printer
 Epson TM-T82, TM-T88
 Star TSP100, TSP650
 Xprinter XP-58, XP-80
 Zjiang ZJ-5890K
Files:

print-server/server.js
print-server/package.json
2.2 Print Server Installer
 Create Windows installer (NSIS)
 Auto-start on boot option
 System tray icon
 Uninstaller
 Create Linux installer (.deb)
 Create installation guide (PDF + Video)
Files:

print-server/installer/windows.nsi
print-server/installer/linux.sh
docs/print-server-installation.pdf
2.3 Backend Integration
 Create getReceiptData() method
 Format receipt data untuk ESC/POS
 Include store info dari settings
 Include transaction details
 Add printer settings
 Printer type (USB/Network)
 Auto-cut enabled
 Open drawer enabled
Files Modified:


app/Http/Controllers/Cashier/POSController.php
Routes:

GET /pos/transaction/{id}/receipt-data
2.4 Frontend Integration
 Implement printReceipt() function
 Check print server status (localhost:9100)
 Send receipt data to print server
 Fallback to window.print() if server offline
 Add print server status indicator
 Add "Open Cash Drawer" manual button
Files Modified:


resources/views/cashier/pos/index.blade.php


Week 17: Customer Management
3.1 Database & Models
 Create customers table migration
 id, name, phone, email, address
 points_balance, total_spent
 created_at, updated_at
 Create customer_points table
 id, customer_id, transaction_id
 points, type (earn/redeem)
 description, created_at
 Create Customer model
 Relations: transactions, points
 Create CustomerService
 earnPoints(customer, amount)
 redeemPoints(customer, points)
 calculatePoints(amount)
Files:

database/migrations/xxxx_create_customers_table.php
database/migrations/xxxx_create_customer_points_table.php
app/Models/Customer.php
app/Services/CustomerService.php
3.2 CRUD Customer
 Create CustomerController
 Implement CRUD operations
 index() - List customers
 create() - Form
 store() - Save
 show() - Detail + transaction history
 edit() - Form
 update() - Save
 destroy() - Soft delete
 Search & filter customers
 By name, phone, email
 Sort by total_spent, points_balance
Files:

app/Http/Controllers/Admin/CustomerController.php
resources/views/admin/customers/index.blade.php
resources/views/admin/customers/create.blade.php
resources/views/admin/customers/edit.blade.php
resources/views/admin/customers/show.blade.php
3.3 Customer di POS
 Add customer_id to 

transactions
 table
 Update 

TransactionService
 Handle customer selection
 Calculate & earn points
 Redeem points untuk diskon
 Update POS UI
 Customer search/select
 Display customer info & points
 Option to use points
 Show points earned after checkout
Files Modified:

database/migrations/xxxx_add_customer_to_transactions.php

app/Services/TransactionService.php

resources/views/cashier/pos/index.blade.php
Settings:

customer.required (boolean) - Wajib pilih customer
customer.loyalty_enabled (boolean) - Aktifkan poin
customer.points_earn_rate (string) - "10000:1" (Rp 10k = 1 poin)
customer.points_redeem_rate (string) - "100:10000" (100 poin = Rp 10k)
customer.points_expiry_days (int) - 365 hari

Week 18-19: Discount & Promotion System
4.1 Database & Models
 Create discounts table migration
 id, name, code (voucher)
 type (percentage/fixed/buy_x_get_y)
 value, min_purchase
 applicable_to (all/category/product)
 applicable_ids (JSON)
 start_date, end_date
 max_usage, usage_count
 is_active, created_at, updated_at
 Create Discount model
 Create DiscountService
 validateDiscount(discount, cart)
 calculateDiscount(discount, cart)
 applyDiscount(transaction, discount)
Files:

database/migrations/xxxx_create_discounts_table.php
app/Models/Discount.php
app/Services/DiscountService.php
Discount Types:

percentage - Diskon % (10%)
fixed - Diskon nominal (Rp 10.000)
buy_x_get_y - Beli X gratis Y
4.2 Admin Discount Management
 Create DiscountController
 Implement CRUD operations
 index() - List discounts
 create() - Form dengan conditional fields
 store() - Save dengan validasi
 edit() - Form
 update() - Save
 destroy() - Delete
 toggle() - Active/inactive
 Display usage statistics
Files:

app/Http/Controllers/Admin/DiscountController.php
resources/views/admin/discounts/index.blade.php
resources/views/admin/discounts/create.blade.php
resources/views/admin/discounts/edit.blade.php
4.3 Discount di POS
 Add discount_id, discount_amount to 

transactions
 Update 

TransactionService
 Validate discount eligibility
 Calculate discount amount
 Handle discount stacking
 Require admin PIN untuk diskon > threshold
 Update POS UI
 Manual discount input (kasir)
 Voucher code input
 Auto-apply eligible discounts
 Display discount breakdown
 PIN modal untuk diskon besar
Files Modified:

database/migrations/xxxx_add_discount_to_transactions.php

app/Services/TransactionService.php

resources/views/cashier/pos/index.blade.php
Settings:

discount.cashier_manual_allowed (boolean)
discount.cashier_max_percent (int) - 10
discount.cashier_max_amount (int) - 50000
discount.allow_stacking (boolean)


Week 20: Shift Management
5.1 Database & Models
 Create shifts table migration
 id, user_id, shift_number
 opening_balance, closing_balance
 expected_cash, actual_cash, variance
 opened_at, closed_at
 notes, status (open/closed)
 Create shift_transactions pivot table
 Create Shift model
 Relations: user, transactions
 Create ShiftService
 openShift(user, opening_balance)
 closeShift(shift, actual_cash)
 calculateExpectedCash(shift)
Files:

database/migrations/xxxx_create_shifts_table.php
database/migrations/xxxx_create_shift_transactions_table.php
app/Models/Shift.php
app/Services/ShiftService.php
5.2 Shift Operations
 Create ShiftController
 Implement shift operations
 openShift() - Input modal awal
 closeShift() - Input uang fisik, hitung selisih
 index() - Shift history
 show() - Shift detail
 print() - Print shift report
 Require admin PIN jika variance > tolerance
Files:

app/Http/Controllers/Cashier/ShiftController.php
resources/views/cashier/shifts/open.blade.php
resources/views/cashier/shifts/close.blade.php
resources/views/cashier/shifts/index.blade.php
resources/views/cashier/shifts/show.blade.php
resources/views/cashier/shifts/report.blade.php
5.3 Shift Integration di POS
 Add shift status indicator di POS
 Block POS jika shift belum dibuka (jika required)
 Auto-assign transactions ke active shift
 Warning jika shift sudah lama dibuka (> 12 jam)
Files Modified:


resources/views/cashier/pos/index.blade.php

app/Http/Controllers/Cashier/POSController.php
Settings:

shift.mode (string) - "single" atau "multiple"
shift.require_opening_balance (boolean)
shift.require_close_before_logout (boolean)
shift.cash_variance_tolerance (int) - 5000

Week 21: Return & Refund Management
6.1 Database & Models
 Create returns table migration
 id, transaction_id, customer_id
 return_number, total_amount
 refund_method (cash/exchange/credit)
 reason, notes
 status (pending/approved/rejected)
 approved_by, approved_at
 created_by, created_at
 Create return_items table
 id, return_id, transaction_item_id
 product_id, qty, unit_price, subtotal
 reason
 Create Return model
 Relations: transaction, customer, items
 Create ReturnService
 createReturn(transaction, items, reason)
 approveReturn(return)
 rejectReturn(return, reason)
 

restoreStock(return)
Files:

database/migrations/xxxx_create_returns_table.php
database/migrations/xxxx_create_return_items_table.php
app/Models/Return.php
app/Services/ReturnService.php
6.2 Return Operations
 Create ReturnController
 Implement return operations
 index() - List returns
 create() - Search transaction, select items
 store() - Save return
 show() - Return detail
 approve() - Approve dengan PIN
 reject() - Reject dengan reason
 print() - Print return receipt
 Validate return eligibility
 Check max_days
 Check item sudah diretur atau belum
 Auto-approve jika nilai ≤ threshold
Files:

app/Http/Controllers/Admin/ReturnController.php
resources/views/admin/returns/index.blade.php
resources/views/admin/returns/create.blade.php
resources/views/admin/returns/show.blade.php
resources/views/admin/returns/receipt.blade.php
Settings:

return.enabled (boolean)
return.max_days (int) - 7
return.auto_approve_limit (int) - 100000
return.refund_methods (string) - "cash,exchange,credit"
return.restore_stock (boolean)


Week 22: Testing, Documentation & Polish
7.1 Automated Testing
 Unit tests untuk semua Services
 CustomerService
 DiscountService
 ShiftService
 ReturnService
 Feature tests untuk critical flows
 Customer loyalty flow
 Discount application
 Shift open/close
 Return approval
 Test import/export functionality
 Test backup/restore
 Test print server integration
7.2 Documentation
 User manual (PDF)
 Admin guide
 Cashier guide
 Settings configuration
 Print server installation guide
 Windows installation
 Linux installation
 Troubleshooting
 Video tutorials (screen recording)
 Onboarding walkthrough
 Import produk
 Setup printer
 Daily operations
 API documentation (jika ada)
7.3 Bug Fixing & Polish
 Fix reported bugs
 Performance optimization
 Database indexing
 Query optimization
 Caching (Redis)
 UI/UX improvements
 Responsive design
 Loading states
 Error messages
 Code refactoring
 Security audit
UAT & Deployment (Week 23-24)
UAT Preparation
 Setup UAT environment
 Recruit 2-3 pilot stores
 Prepare training materials