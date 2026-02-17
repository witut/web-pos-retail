<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Cashier\POSController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public route - Landing page
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Require authentication + admin role
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories CRUD
    Route::resource('categories', CategoryController::class);

    // Products Import/Export
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('import', [ProductImportController::class, 'index'])->name('import');
        Route::post('import', [ProductImportController::class, 'import']);
        Route::get('export', [ProductImportController::class, 'export'])->name('export');
        Route::get('import/template', [ProductImportController::class, 'downloadTemplate'])->name('import.template');
    });

    // Products CRUD
    Route::resource('products', ProductController::class);

    // Suppliers CRUD
    Route::resource('suppliers', SupplierController::class);

    // Stock Management
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/receiving', [StockController::class, 'receiving'])->name('receiving.index');
        Route::get('/receiving/create', [StockController::class, 'createReceiving'])->name('receiving.create');
        Route::post('/receiving', [StockController::class, 'storeReceiving'])->name('receiving.store');
        Route::get('/receiving/{receiving}', [StockController::class, 'showReceiving'])->name('receiving.show');

        Route::get('/movements', [StockController::class, 'movements'])->name('movements.index');
        Route::get('/movements', [StockController::class, 'movements'])->name('movements.index');

        // Stock Opname
        Route::resource('opname', \App\Http\Controllers\Admin\StockOpnameController::class);
    });

    // Reports
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', 'sales')->name('sales');
        Route::get('/stock', 'stock')->name('stock');
        Route::get('/dead-stock', 'deadStock')->name('dead_stock');
        Route::get('/profit-loss', 'profitLoss')->name('profit-loss');
        Route::get('/customers', 'customers')->name('customers');
        Route::get('/points', 'points')->name('points');
        Route::get('/export', 'export')->name('export');
    });


    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Audit Logs
    Route::resource('audit-logs', \App\Http\Controllers\Admin\AuditLogController::class)->only(['index', 'show']);


    // Users management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('users/{user}/reset-pin', [\App\Http\Controllers\Admin\UserController::class, 'resetPin'])->name('users.reset-pin');
    Route::delete('users/{user}/remove-pin', [\App\Http\Controllers\Admin\UserController::class, 'removePin'])->name('users.remove-pin');

    // Backups
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('store');
        Route::get('/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
    });

    // Customers
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);

    // Promotions
    Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class);
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
});

/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
| Require authentication + cashier or admin role
*/
Route::middleware(['auth', 'role:cashier,admin'])->prefix('pos')->name('pos.')->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', [\App\Http\Controllers\Cashier\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\Cashier\DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\Cashier\DashboardController::class, 'updateProfile'])->name('profile.update');

    // POS Terminal
    Route::get('/', [POSController::class, 'index'])->name('index');

    // Product search (AJAX)
    Route::get('/search-product', [POSController::class, 'searchProduct'])->name('search-product');
    Route::get('/autocomplete', [POSController::class, 'autocomplete'])->name('autocomplete');

    // Customer search (AJAX)
    Route::get('/customers/search', [\App\Http\Controllers\Admin\CustomerController::class, 'search'])->name('customers.search');
    Route::post('/customers/quick-add', [\App\Http\Controllers\Admin\CustomerController::class, 'quickStore'])->name('customers.quick-add');


    // Checkout
    Route::post('/calculate', [POSController::class, 'calculate'])->name('calculate');
    Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout');

    // Transaction history
    Route::get('/history', [POSController::class, 'history'])->name('history');
    Route::get('/transaction/{transaction}', [POSController::class, 'show'])->name('transaction.show');

    // Print receipt
    Route::get('/transaction/{transaction}/print', [POSController::class, 'print'])->name('transaction.print');

    // Void transaction (requires Admin PIN in controller)
    Route::post('/transaction/{transaction}/void', [POSController::class, 'void'])->name('transaction.void');

    // Verify Admin PIN (AJAX)
    Route::post('/verify-pin', [POSController::class, 'verifyPin'])->name('verify-pin');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
