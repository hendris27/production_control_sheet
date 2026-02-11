<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('time_slot_shift1_7hs')->get();
foreach ($rows as $r) {
    $start = $r->start_time;
    $end = $r->end_time;
    $minutes = (int) $r->minutes;
    if ($start && $end) {
        $label = date('H.i', strtotime($start)) . '-' . date('H.i', strtotime($end)) . " ({$minutes} Menit)";
    } else {
        $label = $r->label;
    }
    DB::table('time_slot_shift1_7hs')->where('id', $r->id)->update(['label' => $label]);
    echo "Updated id={$r->id} slug={$r->slug} -> label='{$label}'\n";
}

echo "Done\n";
