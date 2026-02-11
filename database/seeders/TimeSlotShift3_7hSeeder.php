<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotShift3_7hSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['label' => '23.00-00.00 (60 Menit)', 'start_time' => '23:00:00', 'end_time' => '00:00:00', 'minutes' => 60, 'slug' => '23_00', 'order' => 1],
            ['label' => '00.00-01.00 (60 Menit)', 'start_time' => '00:00:00', 'end_time' => '01:00:00', 'minutes' => 60, 'slug' => '00_01', 'order' => 2],
            ['label' => '01.00-02.00 (60 Menit)', 'start_time' => '01:00:00', 'end_time' => '02:00:00', 'minutes' => 60, 'slug' => '01_02', 'order' => 3],
            ['label' => '02.00-03.00 (40 Menit)', 'start_time' => '02:00:00', 'end_time' => '03:00:00', 'minutes' => 40, 'slug' => '02_03', 'order' => 4],
            ['label' => '03.00-04.00 (40 Menit)', 'start_time' => '03:00:00', 'end_time' => '04:00:00', 'minutes' => 40, 'slug' => '03_04', 'order' => 5],
            ['label' => '04.00-05.00 (60 Menit)', 'start_time' => '04:00:00', 'end_time' => '05:00:00', 'minutes' => 60, 'slug' => '04_05', 'order' => 6],
            ['label' => '05.00-06.00 (40 Menit)', 'start_time' => '05:00:00', 'end_time' => '06:00:00', 'minutes' => 40, 'slug' => '05_06', 'order' => 7],
            ['label' => '06.00-07.00 (60 Menit)', 'start_time' => '06:00:00', 'end_time' => '07:00:00', 'minutes' => 60, 'slug' => '06_07', 'order' => 8],
        ];

        foreach ($rows as $row) {
            DB::table('time_slot_shift3_7hs')->updateOrInsert([
                'slug' => $row['slug']
            ], $row);
        }
    }
}
