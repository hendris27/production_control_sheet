<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecksheetList;

class ChecksheetListSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 70; $i++) {
            ChecksheetList::create([
                'name' => 'Line ' . $i,
            ]);
        }
    }
}
