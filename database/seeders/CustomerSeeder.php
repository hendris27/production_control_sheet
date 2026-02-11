<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            ['code' => '1123', 'name' => 'IC', 'Description' => 'ICHIKOH'],
            ['code' => '1153', 'name' => 'KT', 'Description' => 'Koito Manufacturing'],
            ['code' => '1187', 'name' => 'MT', 'Description' => 'Mitsuba'],
            ['code' => '1359', 'name' => 'TD', 'Description' => 'Toyodenso'],
            ['code' => '1019', 'name' => 'AV', 'Description' => 'AVI Automotive'],
            ['code' => '1112', 'name' => 'MS', 'Description' => 'MAS-I Corporation'],
            ['code' => '1156', 'name' => 'KO', 'Description' => 'Kojima Industries'],
            ['code' => '1405', 'name' => 'HN', 'Description' => 'Hino Motors'],
            ['code' => '1147', 'name' => 'KA', 'Description' => 'Kawai Industries'],
            ['code' => '1018', 'name' => 'AJ', 'Description' => 'AJI Manufacturing'],
            ['code' => '1011', 'name' => 'AP', 'Description' => 'Ampassindo Pratama'],
            ['code' => '1610', 'name' => 'GW', 'Description' => 'Green Way Co.'],
        ];

        foreach ($data as $index => $item) {
            DB::table('customers')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'no' => $index + 1,
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'description' => $item['Description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
