<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListNgSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $items = [
            ['ng_name' => 'LED Broken', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'Low Current', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'Input Current', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'Output Current', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'Low Meassure', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'LED Lighting Confirmation', 'created_at' => $now, 'updated_at' => $now],
            ['ng_name' => 'Absoulte Intensity', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('list_ngs')->insert($items);
    }
}
