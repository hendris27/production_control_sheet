<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IPQCNameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('i_p_q_c_name_lists')->insert([
            [
                'no' => 1,
                'nik' => '25096180',
                'name' => 'Hendri',
                 'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 2,
                'nik' => '21075136',
                'name' => 'Indah Nopiyanti',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['no' => 3,
                'nik' => '21125406',
                'name' => 'Jelita Puspa Dewi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['no' => 4,
                'nik' => '24016032',
                'name' => 'Rina Indriyani',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
