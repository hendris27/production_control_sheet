<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpvNameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'no' => 1,
                'nik' => '25096180',
                'name' => 'Hendri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 2,
                'nik' => '13010064',
                'name' => 'Renny Kumala',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 3,
                'nik' => '22075598',
                'name' => 'Juli Mangita N',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('spv_name_lists')->upsert($rows, ['nik'], ['no', 'name', 'updated_at', 'created_at']);
    }
}
