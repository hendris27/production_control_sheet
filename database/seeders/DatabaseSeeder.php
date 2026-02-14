<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            ChecksheetListSeeder::class,
            CreateAllUsersSeeder::class,
            CustomerSeeder::class,
            IPQCNameListSeeder::class,
            IronSolderInspectionSeeder::class,
            LeaderNameListSeeder::class,
            ListNgSeeder::class,
            OperatorsNameListSeeder::class,
            ProcessSeeder::class,
            RoleSeeder::class,
            SopNameListSeeder::class,
            SpvNameListSeeder::class,
            TargetUphSeeder::class,
            TechnicianNameListSeeder::class,
            TimeSlotShift1_7hSeeder::class,
            TimeSlotShift1_5hSeeder::class,
            TimeSlotShift2_7hSeeder::class,
            TimeSlotShift2_5hSeeder::class,
            TimeSlotShift3_7hSeeder::class,
            TimeSlotShift3_5hSeeder::class,

        ]);

        $user = User::updateOrCreate(
            ['nik' => '1234567890'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
        $user->assignRole('admin');
    }
}
