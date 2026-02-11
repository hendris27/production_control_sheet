<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotShift2_7hSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['label' => '15.00-16.00 (60 Menit)', 'start_time' => '15:00:00', 'end_time' => '16:00:00', 'minutes' => 60, 'slug' => '15_16', 'order' => 1],
            ['label' => '16.00-17.00 (45 Menit)', 'start_time' => '16:00:00', 'end_time' => '17:00:00', 'minutes' => 45, 'slug' => '16_17', 'order' => 2],
            ['label' => '17.00-18.00 (60 Menit)', 'start_time' => '17:00:00', 'end_time' => '18:00:00', 'minutes' => 60, 'slug' => '17_18', 'order' => 3],
            ['label' => '18.00-19.00 (30 Menit)', 'start_time' => '18:00:00', 'end_time' => '19:00:00', 'minutes' => 30, 'slug' => '18_19', 'order' => 4],
            ['label' => '19.00-20.00 (45 Menit)', 'start_time' => '19:00:00', 'end_time' => '20:00:00', 'minutes' => 45, 'slug' => '19_20', 'order' => 5],
            ['label' => '20.00-21.00 (60 Menit)', 'start_time' => '20:00:00', 'end_time' => '21:00:00', 'minutes' => 60, 'slug' => '20_21', 'order' => 6],
            ['label' => '21.00-22.00 (60 Menit)', 'start_time' => '21:00:00', 'end_time' => '22:00:00', 'minutes' => 60, 'slug' => '21_22', 'order' => 7],
            ['label' => '22.00-23.00 (60 Menit)', 'start_time' => '22:00:00', 'end_time' => '23:00:00', 'minutes' => 60, 'slug' => '22_23', 'order' => 8],

        ];

        foreach ($rows as $row) {
            DB::table('time_slot_shift2_7hs')->updateOrInsert([
                'slug' => $row['slug']
            ], $row);
        }
    }
}
