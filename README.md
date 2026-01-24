# 🌙 NightmareMart

**A Laravel 12 application with intentional production bugs for debugging education.**

> **Warning:** This application contains 10 intentional bugs! It's designed for learning how to debug real-world production issues.

## 📋 Overview

NightmareMart is a realistic e-commerce application built with Laravel 12 that demonstrates common production nightmares. Each "episode" focuses on a specific bug pattern that developers encounter in the real world.

## 🐛 The 10 Episodes

| Episode | Bug | Description |
|---------|-----|-------------|
| 1 | **Config Caching** | `env()` returns null after `config:cache` |
| 2 | **N+1 Queries** | Admin orders page triggers thousands of queries |
| 3 | **Authorization Hole** | Regular users can refund orders |
| 4 | **Race Conditions** | Two users can buy the last item in stock |
| 5 | **Cache Key Collision** | Users see each other's cart totals |
| 6 | **Memory Leak** | Queue worker crashes after ~50 orders |
| 7 | **Timezone Nightmare** | International users see wrong dates |
| 8 | **Log Explosion** | Debug logging fills the disk |
| 9 | **Search Performance** | One search query takes down the site |
| 10 | **Money Bug** | Floating-point math causes penny discrepancies |

## 🚀 Quick Start

### Requirements

- PHP 8.2+
- Composer 2.6+
- Node.js 20+ (for asset compilation)
- SQLite (default) or MySQL 8.0+

### Installation

```bash
# Clone the repository
git clone <repo-url> nightmare-mart
cd nightmare-mart

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database (creates 10,000+ products, 5,000+ orders)
php artisan db:seed

# Start the development server
php artisan serve
```

### Test Accounts

After seeding, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@nightmaremart.test | password |
| User | user@nightmaremart.test | password |

## 🔍 Bug Locations

### Episode 1: Config Caching
- **File:** `app/Http/Controllers/CheckoutController.php`
- **Line:** `$taxRate = env('TAX_RATE', 0.08);`
- **Fix:** Use `config('shop.tax_rate')` instead

### Episode 2: N+1 Queries
- **File:** `app/Http/Controllers/Admin/OrderController.php`
- **Symptom:** Admin orders page is extremely slow
- **Fix:** Add eager loading with `->with(['user', 'items.product'])`

### Episode 3: Authorization Hole
- **File:** `routes/web.php`
- **Line:** `Route::post('/{order}/refund', ...)->name('refund');`
- **Fix:** Add `->middleware('admin')` to the route

### Episode 4: Race Conditions
- **File:** `app/Http/Controllers/CheckoutController.php`
- **Symptom:** Inventory goes negative under concurrent load
- **Fix:** Use `lockForUpdate()` in a transaction

### Episode 5: Cache Key Collision
- **File:** `app/Services/CartService.php`
- **Line:** `Cache::remember('cart_total', ...)`
- **Fix:** Scope cache key to user: `"cart_total_{$userId}"`

### Episode 6: Memory Leak
- **File:** `app/Jobs/GenerateBulkReceiptsJob.php`
- **Symptom:** Queue worker crashes with memory exhaustion
- **Fix:** Use `lazy()` or `chunk()` instead of `get()`

### Episode 7: Timezone Nightmare
- **Files:** Various views showing `$order->placed_at`
- **Symptom:** Dates show in UTC instead of user's timezone
- **Fix:** Use `$order->placed_at->timezone(auth()->user()->timezone)`

### Episode 8: Log Explosion
- **File:** `config/logging.php`
- **Symptom:** Log file grows to GB in size
- **Fix:** Use `daily` channel and set `LOG_LEVEL=warning` in production

### Episode 9: Search Performance
- **File:** `app/Http/Controllers/SearchController.php`
- **Line:** `Product::where('name', 'LIKE', "%{$query}%")->get()`
- **Fix:** Add pagination, limits, and full-text search indexes

### Episode 10: Money Bug
- **Files:** `CheckoutController.php`, `OrderCalculator.php`
- **Symptom:** Order totals don't match line items
- **Fix:** Use integer cents, not floating-point dollars

## 📁 Project Structure

```
nightmare-mart/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Admin controllers
│   │   │   ├── Auth/            # Authentication
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php  # Episodes 1, 4
│   │   │   ├── OrderController.php     # Episode 3
│   │   │   └── SearchController.php    # Episode 9
│   │   └── Middleware/
│   │       └── IsAdmin.php      # Episode 3
│   ├── Jobs/
│   │   └── GenerateBulkReceiptsJob.php  # Episode 6
│   ├── Models/                  # Eloquent models
│   ├── Policies/
│   │   └── OrderPolicy.php      # Episode 3
│   ├── Services/
│   │   ├── CartService.php      # Episode 5
│   │   └── OrderCalculator.php  # Episode 10
│   └── ValueObjects/
│       └── Money.php            # Episode 10 fix
├── config/
│   ├── logging.php              # Episode 8
│   └── shop.php                 # Episode 1
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│       └── NightmareMartSeeder.php
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php        # Main layout
│   │   └── admin.blade.php      # Admin layout
│   ├── admin/                   # Admin views
│   ├── auth/                    # Login/Register
│   ├── cart/
│   ├── checkout/
│   ├── orders/
│   ├── products/
│   └── search/
└── routes/
    └── web.php                  # Episode 3 bug
```

## 🛠️ Development Commands

```bash
# Run development server
php artisan serve

# Run queue worker (for Episode 6 testing)
php artisan queue:work

# Clear config cache (Episode 1)
php artisan config:clear

# Cache config (triggers Episode 1 bug)
php artisan config:cache

# Monitor slow queries
php artisan db:monitor

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## 🎓 Learning Path

1. **Start with Episode 1** - Understand environment/config basics
2. **Episode 2** - Learn database query optimization
3. **Episode 3** - Security and authorization
4. **Episode 4** - Concurrency and database locking
5. **Episode 5** - Caching strategies
6. **Episode 6** - Memory management
7. **Episode 7** - Datetime/timezone handling
8. **Episode 8** - Logging best practices
9. **Episode 9** - Query performance
10. **Episode 10** - Financial calculations

## 📺 Recording Setup

If using this for video content:

1. Install Laravel Debugbar for query visualization
2. Use the seeded data (10,000+ products) to demonstrate slowness
3. Test accounts with different timezones for Episode 7
4. Low-stock products (stock=1) for Episode 4 race conditions

## 🤝 Contributing

This is an educational project. Feel free to:
- Add more bug scenarios
- Improve the UI
- Add tests that catch the bugs
- Create fix branches for each episode

## 📄 License

MIT License - Feel free to use for learning and teaching!

---

**Happy Debugging! 🐛🔍**
