<?php

// One-off runner for GenerateProductionReports job. Adjust ID(s) below.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// IDs to test - change as needed
$ids = [1];

$job = new App\Jobs\GenerateProductionReports($ids);
try {
    $job->handle();
    echo "Job executed for IDs: " . implode(',', $ids) . "\n";
} catch (Throwable $e) {
    echo "Job error: " . $e->getMessage() . "\n";
}
