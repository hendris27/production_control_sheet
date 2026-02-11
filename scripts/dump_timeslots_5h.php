<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the framework
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeSlotShift1_5h;

$rows = TimeSlotShift1_5h::orderBy('order')->get();
$out = [];
foreach ($rows as $r) {
    $out[] = ['slug' => $r->slug, 'minutes' => (int)$r->minutes, 'label' => $r->label, 'start' => $r->start_time, 'end' => $r->end_time];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
