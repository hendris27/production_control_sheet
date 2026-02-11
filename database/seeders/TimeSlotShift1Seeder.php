<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotShift1Seeder extends Seeder
{
    public function run(): void
    {
        // 5-hour shift sample slots (5 entries)
        $slots5 = [
            ['order'=>1,'slug'=>'07-08','label'=>'07.00 - 08.00','start_time'=>'07:00','end_time'=>'08:00','minutes'=>60],
            ['order'=>2,'slug'=>'08-09','label'=>'08.00 - 09.00','start_time'=>'08:00','end_time'=>'09:00','minutes'=>60],
            ['order'=>3,'slug'=>'09-10','label'=>'09.00 - 10.00','start_time'=>'09:00','end_time'=>'10:00','minutes'=>60],
            ['order'=>4,'slug'=>'10-11','label'=>'10.00 - 11.00','start_time'=>'10:00','end_time'=>'11:00','minutes'=>60],
            ['order'=>5,'slug'=>'11-12','label'=>'11.00 - 12.00','start_time'=>'11:00','end_time'=>'12:00','minutes'=>60],
        ];

        // 7-hour shift sample slots (7 entries)
        $slots7 = [
            ['order'=>1,'slug'=>'07-08','label'=>'07.00 - 08.00','start_time'=>'07:00','end_time'=>'08:00','minutes'=>60],
            ['order'=>2,'slug'=>'08-09','label'=>'08.00 - 09.00','start_time'=>'08:00','end_time'=>'09:00','minutes'=>60],
            ['order'=>3,'slug'=>'09-10','label'=>'09.00 - 10.00','start_time'=>'09:00','end_time'=>'10:00','minutes'=>60],
            ['order'=>4,'slug'=>'10-11','label'=>'10.00 - 11.00','start_time'=>'10:00','end_time'=>'11:00','minutes'=>60],
            ['order'=>5,'slug'=>'11-12','label'=>'11.00 - 12.00','start_time'=>'11:00','end_time'=>'12:00','minutes'=>60],
            ['order'=>6,'slug'=>'12-13','label'=>'12.00 - 13.00','start_time'=>'12:00','end_time'=>'13:00','minutes'=>60],
            ['order'=>7,'slug'=>'13-14','label'=>'13.00 - 14.00','start_time'=>'13:00','end_time'=>'14:00','minutes'=>60],
        ];

        DB::table('time_slot_shift1_5h')->truncate();
        DB::table('time_slot_shift1_7h')->truncate();

        foreach ($slots5 as $s) {
            DB::table('time_slot_shift1_5h')->insert(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }

        foreach ($slots7 as $s) {
            DB::table('time_slot_shift1_7h')->insert(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
