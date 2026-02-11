<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IronSolderInspectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('iron_solder_inspections')->insert([
            [
                'tanggal' => Carbon::now()->format('Y-m-d'),
                'shift' => 1,
                'line' => 1,
                'actual_setting' => '350',
                'esd_voltage' => '5',
                'eos_ground' => '2',
                'solder_tip_condition' => '✔️ OK',
                'solder_stand_condition' => '✔️ OK',
                'judgement' => '✔️ OK',
                'remarks' => 'Sample data',
                'pic' => 1,
            ],
        ]);
    }
}
