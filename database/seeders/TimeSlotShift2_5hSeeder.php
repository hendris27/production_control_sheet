<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotShift2_5hSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['label' => '12.00-13.00 (30 Menit)', 'start_time' => '12:00:00', 'end_time' => '13:00:00', 'minutes' => 30, 'slug' => '12_13', 'order' => 1],
            ['label' => '13.00-14.00 (60 Menit)', 'start_time' => '13:00:00', 'end_time' => '14:00:00', 'minutes' => 60, 'slug' => '13_14', 'order' => 2],
            ['label' => '14.00-15.00 (60 Menit)', 'start_time' => '14:00:00', 'end_time' => '15:00:00', 'minutes' => 60, 'slug' => '14_15', 'order' => 3],
            ['label' => '15.00-16.00 (45 Menit)', 'start_time' => '15:00:00', 'end_time' => '16:00:00', 'minutes' => 45, 'slug' => '15_16', 'order' => 4],
            ['label' => '16.00-17.00 (60 Menit)', 'start_time' => '16:00:00', 'end_time' => '17:00:00', 'minutes' => 60, 'slug' => '16_17', 'order' => 5],
            ['label' => '17.00-18.00 (45 Menit)', 'start_time' => '17:00:00', 'end_time' => '18:00:00', 'minutes' => 45, 'slug' => '17_18', 'order' => 6],

        ];

        foreach ($rows as $row) {
            DB::table('time_slot_shift2_5hs')->updateOrInsert([
                'slug' => $row['slug']
            ], $row);
        }
    }
}
