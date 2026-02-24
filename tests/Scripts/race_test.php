<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

// CONFIG
$productId = 47;
$buyers = 5;
$initialStock = 2;
$mode = $argv[1] ?? 'both';

$endpointMap = [
    'broken' => '/api/test-checkout',
    'fixed' => '/api/test-checkout-fixed',
];

echo "=== Race Condition Test ===\n\n";

// Reset product
$product = Product::find($productId);

if (! $product) {
    echo "ERROR: Product {$productId} not found\n";
    exit(1);
}

echo "Product: {$product->name}\n";
echo "Stock: {$initialStock}\n";
echo "Buyers: {$buyers}\n\n";

// Get test users
$users = User::where('email', 'like', 'racetest%')->take($buyers)->get();

if ($users->count() < $buyers) {
    echo "ERROR: Not enough test users. Run this in tinker first:\n";
    echo 'for ($i = 0; $i < 5; $i++) { User::create([\'name\' => "Race Test $i", \'email\' => "racetest$i@example.com", \'password\' => bcrypt("password")]); }' . PHP_EOL;
    exit(1);
}

$baseUrl = env('APP_URL', 'http://nightmare-mart.test');

$scenarios = [];

if ($mode === 'both') {
    $scenarios = [
        ['label' => 'BROKEN', 'endpoint' => $endpointMap['broken']],
        ['label' => 'FIXED', 'endpoint' => $endpointMap['fixed']],
    ];
} elseif (isset($endpointMap[$mode])) {
    $scenarios = [[
        'label' => strtoupper($mode),
        'endpoint' => $endpointMap[$mode],
    ]];
} elseif (str_starts_with($mode, '/api/')) {
    $scenarios = [[
        'label' => 'CUSTOM',
        'endpoint' => $mode,
    ]];
} else {
    echo "ERROR: Invalid mode '{$mode}'. Use: both | broken | fixed | /api/...\n";
    exit(1);
}

$results = [];

foreach ($scenarios as $scenario) {
    $endpoint = $scenario['endpoint'];
    $label = $scenario['label'];

    $product->update(['stock_quantity' => $initialStock]);
    Order::where('order_number', 'like', 'NM-' . now()->format('Ymd') . '%')->delete();

    echo "=== Scenario: {$label} ===\n";
    echo "Firing requests to {$baseUrl}{$endpoint}...\n";

    $multi = curl_multi_init();
    $handles = [];

    foreach ($users as $i => $user) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "{$baseUrl}{$endpoint}",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$i] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($multi, $running);
        curl_multi_select($multi);
    } while ($running > 0);

    echo "\n=== Results ({$label}) ===\n";
    $success = 0;
    $serverErrors = 0;
    $connectionsFailed = 0;

    foreach ($handles as $i => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($response, true);

        if ($httpCode === 0) {
            echo "User {$i}: [WARN] CONNECTION FAILED\n";
            $connectionsFailed++;
        } elseif ($httpCode >= 500) {
            echo "User {$i}: [FAIL] SERVER ERROR ({$httpCode})\n";
            echo '         ' . substr(strip_tags($response), 0, 100) . "\n";
            $serverErrors++;
        } elseif ($result && isset($result['success'])) {
            if ($result['success']) {
                echo "User {$i}: [OK] Order {$result['order_number']}\n";
                $success++;
            } else {
                echo "User {$i}: [FAIL] {$result['message']}\n";
            }
        } else {
            echo "User {$i}: [WARN] UNEXPECTED (HTTP {$httpCode})\n";
            echo '         ' . substr($response, 0, 100) . "\n";
        }

        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
    }

    curl_multi_close($multi);

    $product->refresh();
    $finalStock = $product->stock_quantity;

    echo "\n=== Final State ({$label}) ===\n";
    echo "Stock: {$finalStock}\n";
    echo "Orders created: {$success}\n";

    $isRaceCondition = $finalStock < 0 || $success > $initialStock;

    if ($finalStock < 0) {
        echo "\nRACE CONDITION! Stock negative by " . abs($finalStock) . "\n\n";
    } elseif ($success > $initialStock) {
        echo "\nRACE CONDITION! {$success} orders but only {$initialStock} in stock\n\n";
    } else {
        echo "\nOK: No race condition this run\n\n";
    }

    $results[] = [
        'label' => $label,
        'endpoint' => $endpoint,
        'success' => $success,
        'stock' => $finalStock,
        'race' => $isRaceCondition,
        'server_errors' => $serverErrors,
        'connection_failed' => $connectionsFailed,
    ];
}

echo "=== Comparison Summary ===\n";
echo str_pad('Scenario', 12) . str_pad('Endpoint', 28) . str_pad('Orders', 8) . str_pad('Stock', 8) . "Result\n";
echo str_repeat('-', 70) . "\n";

foreach ($results as $result) {
    $status = $result['race'] ? 'RACE DETECTED' : 'OK';
    echo str_pad($result['label'], 12)
        . str_pad($result['endpoint'], 28)
        . str_pad((string) $result['success'], 8)
        . str_pad((string) $result['stock'], 8)
        . $status
        . "\n";
}
