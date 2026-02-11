<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaderNameList;

class LeaderNameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Upsert provided leader entries (prevents duplicate key errors on re-run)
        LeaderNameList::upsert([
            ['no' => 1, 'name' => 'May Saroh', 'nik' => '13020150'],
            ['no' => 2, 'name' => 'Novalina Purba', 'nik' => '14111902'],
            ['no' => 3, 'name' => 'Ertin Dwi Aryani', 'nik' => '15052919'],
            ['no' => 4, 'name' => 'Dian Susanti', 'nik' => '15042590'],
            ['no' => 5, 'name' => 'Yoni Diansutra H.', 'nik' => '17053773'],
            ['no' => 6, 'name' => 'Noferina Sianturi', 'nik' => '14071396'],
            ['no' => 7, 'name' => 'Febri Fitriyani', 'nik' => '17033673'],
        ], ['nik'], ['no', 'name']);
    }
}
