<?php

/**
 * Shop Configuration
 * 
 * Episode 1 Learning: ALWAYS access environment variables through config files
 * Using env() directly in controllers/models will break when config:cache runs
 * 
 * CORRECT: config('shop.tax_rate')
 * WRONG: env('TAX_RATE')
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    | Default tax rate applied to all orders.
    | Stored as decimal (0.08 = 8%)
    */
    'tax_rate' => env('TAX_RATE', 0.08),

    /*
    |--------------------------------------------------------------------------
    | Shop Name
    |--------------------------------------------------------------------------
    */
    'name' => env('SHOP_NAME', 'NightmareMart'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('SHOP_CURRENCY', 'USD'),
    'currency_symbol' => '$',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'items_per_page' => env('SHOP_ITEMS_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    | Episode 4: Race condition prevention settings
    */
    'inventory' => [
        // Enable pessimistic locking for checkout
        'use_locking' => true,
        
        // Allow backorders (negative stock)
        'allow_backorder' => false,
        
        // Low stock threshold for notifications
        'low_stock_threshold' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'number_prefix' => 'NM-',
        'statuses' => [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'refunded' => 'Refunded',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Settings
    |--------------------------------------------------------------------------
    | Episode 9: Search query constraints
    */
    'search' => [
        'min_length' => 3,
        'max_results' => 100,
        'cache_ttl' => 3600, // 1 hour
    ],

];
