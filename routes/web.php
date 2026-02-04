<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\StockController;
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
        Route::get('/opname', [StockController::class, 'opname'])->name('opname.index');
    });

    // Reports (placeholder for future)
    // Route::prefix('reports')->name('reports.')->group(function () {
    //     Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    //     Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
    // });

    // Settings (placeholder for future)
    // Route::get('/settings', [SettingController::class, 'index'])->name('settings');

    // Users management (placeholder for future)
    // Route::resource('users', UserController::class);
});

/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
| Require authentication + cashier or admin role
*/
Route::middleware(['auth', 'role:cashier,admin'])->prefix('pos')->name('pos.')->group(function () {

    // POS Terminal
    Route::get('/', [POSController::class, 'index'])->name('index');

    // Product search (AJAX)
    Route::get('/search-product', [POSController::class, 'searchProduct'])->name('search-product');
    Route::get('/autocomplete', [POSController::class, 'autocomplete'])->name('autocomplete');

    // Checkout
    Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout');

    // Transaction history
    Route::get('/history', [POSController::class, 'history'])->name('history');
    Route::get('/transaction/{transaction}', [POSController::class, 'show'])->name('transaction.show');

    // Print receipt
    Route::get('/transaction/{transaction}/print', [POSController::class, 'print'])->name('transaction.print');

    // Void transaction (requires Admin PIN in controller)
    Route::post('/transaction/{transaction}/void', [POSController::class, 'void'])->name('transaction.void');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
| Laravel Breeze will add these routes
| - login, register, password reset, etc.
*/

// NOTE: Install Laravel Breeze for authentication:
// composer require laravel/breeze --dev
// php artisan breeze:install blade
// npm install && npm run dev
