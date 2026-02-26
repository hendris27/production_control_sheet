<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SopNameList;

class SopNameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert provided SOP name list
        SopNameList::insert([
            ['no' => 1, 'name' => 'Neni Kunaetin', 'nik' => '15113343'],
            ['no' => 2, 'name' => 'Rizki Rahayu', 'nik' => '23075918'],
            ['no' => 3, 'name' => 'Siti Darmayanti', 'nik' => '21065091'],
            ['no' => 4, 'name' => 'Sintiawati', 'nik' => '21125351'],
            ['no' => 5, 'name' => 'Devi Sundari', 'nik' => '21125445'],
            ['no' => 6, 'name' => 'Jessita Kurniawati', 'nik' => '23045868'],
            ['no' => 7, 'name' => 'Tina Windara', 'nik' => '22015464'],
            ['no' => 8, 'name' => 'Putri Kurnia', 'nik' => '22015467'],
            ['no' => 9, 'name' => 'Sylvia Rostiani', 'nik' => '22015469'],
            ['no' => 10, 'name' => 'Anggi Lestari', 'nik' => '22055555'],
            ['no' => 11, 'name' => 'Tantri Susilawati', 'nik' => '22065566'],
            ['no' => 12, 'name' => 'Rahmad Hidayat', 'nik' => '23115991'],
            ['no' => 13, 'name' => 'Ukhti Nurul Fitriani', 'nik' => '22015486'],
            ['no' => 14, 'name' => 'Siti Jubaedah', 'nik' => '21105270'],
            ['no' => 15, 'name' => 'Zahra Jilan Sahirah', 'nik' => '22015463'],
            ['no' => 16, 'name' => 'Trihanda Yani', 'nik' => '22035501'],
            ['no' => 17, 'name' => 'Lirfa Lestari Ambarwati', 'nik' => '22115696'],
            ['no' => 18, 'name' => 'Eva Ameliana', 'nik' => '22015468'],
            ['no' => 19, 'name' => 'Camila Cantika Dewi', 'nik' => '23015787'],
            ['no' => 20, 'name' => 'Nisa Nurfadiyah', 'nik' => '21045021'],
            ['no' => 21, 'name' => 'Hendri', 'nik' => '25096180'],

        ]);
    }
}
