<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'FV',
            'MI',
            'VMI',
            'ASSY',
            'HEATING',
            'COATING',
            'GREASE',
            'FCT',
            'LED TEST',
            'SELECTIVE',
            'ROOMWRITE',
            'KEYENCE',
            'INSERT',
            'LEVELING',
            'FLUXER',
            'TAPING',
            'SCREW',
            'ROBOTIC',
            'CAULKING',
            'JUMPER 1',
            'JUMPER 2',
            'TU1',
            'TU2',
            'TAKE OUT PALLET',
        ];

        $now = Carbon::now()->toDateTimeString();

        $rows = array_map(function ($name) use ($now) {
            return [
                'nama_proses' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $names);

        DB::table('processes')->insert($rows);
    }
}
