<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TargetUphSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            ['model_name' => 'ADW371-3000_9B8 RCLD RH', 'target_per_hour' => 90, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX092-3000_9D5 RCLD LH', 'target_per_hour' => 66, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $row) {
            DB::table('target_uphs')->updateOrInsert(
                ['model_name' => $row['model_name']],
                $row
            );
        }
    }
}
