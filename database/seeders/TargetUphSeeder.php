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
            ['model_name' => 'A4V121-4000 9B2 RCLD US RF1_RH', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'A4V122-4000 9B2 RCLD US RF2_LH', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'A4W011-4000 9B2 RCLD UN RF1_RH', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'A4W012-4000 9B2 RCLD UN RF2_LH', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '61A-3002-1732 922B RCLD US RH_RF1', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '61A-3002-1731 922B RCLD US LH_RF2', 'target_per_hour' => 150, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADU181-3000_XF2 RCLD_RF1 RH', 'target_per_hour' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADU182-3000_XF2 RCLD_RF2 LH', 'target_per_hour' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADU171-3000_XF2 RCLT_RF1 RH', 'target_per_hour' => 35, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADU172-3000_XF2 RCLT_RF2 LH', 'target_per_hour' => 35, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '9B2 RCLT US RF1_RH', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '9B2 RCLT US RF2_LH', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'A4V111-4000 9B2 RCLT UN RH', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'A4V112-4000 9B2 RCLT UN LH', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '922B RCLT US RH_RF1', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => '922B RCLT US LH_RF2', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW011-8000_P59 RCLT RH_RF1', 'target_per_hour' => 55, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW012-8000_P59 RCLT LH_RF2', 'target_per_hour' => 55, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW021-8000_P59 RCLD RH_RF1', 'target_per_hour' => 135, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW022-8000_P59 RCLD LH_RF2', 'target_per_hour' => 135, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW031-2000_P59 FCL A1/A2_RH_RF', 'target_per_hour' => 130, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW032-2000_P59 FCL A1/A2_LH_RF', 'target_per_hour' => 130, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW022-1000_P59 FCL B_RF', 'target_per_hour' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW022-5000_P59 FCL B3_RF', 'target_per_hour' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW021-3000_P59 FCL B1-B2_RH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW022-3000_P59 FCL B1-B2_LH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW361-4000_9B8 RCLT B RH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW011-7000_P59 FCL D RH_RF', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW011-7000_P59 FCL D RH_RF', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW012-7000_P59 FCL D LH_RF', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ACW012-7000_P59 FCL D LH_RF', 'target_per_hour' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADW361-7000_9B8 RCLT CIR A RH', 'target_per_hour' => 75, 'created_at' =>$now,'updated_at' => $now],
            ['model_name' => 'ADX081-3000_9D5 RCLT RH', 'target_per_hour' => 61, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX082-3000_9D5 RCLT LH', 'target_per_hour' => 61, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX091-3000_9D5 RCLD RH', 'target_per_hour' => 66, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX092-3000_9D5 RCLD LH', 'target_per_hour' => 66, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX081-3000_9F9 RCLT RH', 'target_per_hour' => 61, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX082-3000_9F9 RCLT LH', 'target_per_hour' => 61, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX091-3000_9F9 RCLD RH', 'target_per_hour' => 66, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADX092-3000_9F9 RCLD LH', 'target_per_hour' => 66, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY091-4000_P63 RCLT LED ASSY E RH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY092-4000_P63 RCLT LED ASSY E LH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY091-5000_P63 RCLT LED ASSY F RH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY092-5000_P63 RCLT LED ASSY F LH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY091-6000_P63 RCLT CIR SASSY A RH_RF', 'target_per_hour' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY092-6000_P63 RCLT CIR SASSY A LH_RF', 'target_per_hour' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY101-4000_P63 RCLD_LED ASSY C RH_RF', 'target_per_hour' => 110, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY102-4000_P63 RCLD_LED ASSY C LH_RF', 'target_per_hour' => 110, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY101-6000_P63 RCLD_CIR ASSY A RH_RF', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY102-6000_P63 RCLD_CIR ASSY A LH_RF', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY112-6000_P63 RCLD_CIR ASSY B LH_RF', 'target_per_hour' => 45, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY101-7000_P63 RCLD_CIR ASSY C RH_RF', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY102-7000_P63 RCLD_CIR ASSY C LH_RF', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY112-7000_P63 RCLD_CIR ASSY D LH_RF', 'target_per_hour' => 45, 'created_at' =>$now,'updated_at'=>$now],
            ['model_name' => 'ADY102-3000_P63 RCLD_01 LED A LH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY101-3000_P63 RCLD_01 LED A RH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY102-3000_P63 RCLD_01 LED A LH_RF', 'target_per_hour' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY091-3000_P63 RCLT_01 A TAIL RH', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['model_name' => 'ADY092-3000_P63 RCLT_01 A TAIL LH', 'target_per_hour' => 50, 'created_at' => $now, 'updated_at' => $now],

        ];

        foreach ($rows as $row) {
            DB::table('target_uphs')->updateOrInsert(
                ['model_name' => $row['model_name']],
                $row
            );
        }
    }
}
