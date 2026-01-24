<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| NightmareMart Routes
| Contains intentional bugs for debugging episodes
|
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Products (public)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Search (Episode 9: Performance issues)
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{itemId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{itemId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});

// Checkout (requires auth)
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
});

// User Orders
Route::middleware('auth')->prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::post('/{order}/receipt', [OrderController::class, 'downloadReceipt'])->name('receipt');
    
    // ============================================
    // EPISODE 3 BUG: Missing admin middleware!
    // ============================================
    // This route should have ->middleware('admin') but doesn't!
    // Any authenticated user can refund any order!
    Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
    
    // CORRECT would be:
    // Route::post('/{order}/refund', [OrderController::class, 'refund'])
    //     ->middleware('admin')
    //     ->name('refund');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Orders (Episode 2: N+1 queries)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    
    // Admin order search (Episode 9)
    Route::get('/orders/search', [SearchController::class, 'adminOrderSearch'])->name('orders.search');
    
    // Products
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::post('/products/{id}/restore', [AdminProductController::class, 'restore'])->name('products.restore');
    
    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
});

// Debug routes (only in local environment)
if (app()->environment('local')) {
    Route::get('/debug/queries', function () {
        return view('debug.queries');
    })->name('debug.queries');
    
    Route::get('/debug/cache', function () {
        return view('debug.cache');
    })->name('debug.cache');
    
    Route::get('/debug/memory', function () {
        return response()->json([
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
        ]);
    })->name('debug.memory');
}
