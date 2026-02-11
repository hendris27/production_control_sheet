<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotShift1_5hSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['label' => '07.00-08.00 (60 Menit)', 'start_time' => '07:00:00', 'end_time' => '08:00:00', 'minutes' => 60, 'slug' => '07_08', 'order' => 1],
            ['label' => '08.00-09.00 (60 Menit)', 'start_time' => '08:00:00', 'end_time' => '09:00:00', 'minutes' => 60, 'slug' => '08_09', 'order' => 2],
            ['label' => '09.00-10.00 (60 Menit)', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'minutes' => 60, 'slug' => '09_10', 'order' => 3],
            ['label' => '10.00-11.00 (45 Menit)', 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'minutes' => 45, 'slug' => '10_11', 'order' => 4],
            ['label' => '11.00-12.00 (60 Menit)', 'start_time' => '11:00:00', 'end_time' => '12:00:00', 'minutes' => 60, 'slug' => '11_12', 'order' => 5],
            ['label' => '12.00-13.00 (15 Menit)', 'start_time' => '12:00:00', 'end_time' => '13:00:00', 'minutes' => 15, 'slug' => '12_13', 'order' => 6],
        ];

        foreach ($rows as $row) {
            DB::table('time_slot_shift1_5hs')->updateOrInsert([
                'slug' => $row['slug']
            ], $row);
        }
    }
}
