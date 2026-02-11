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
            RoleSeeder::class,
            CreateAllUsersSeeder::class,
            ChecksheetListSeeder::class,
            IronSolderInspectionSeeder::class,
            ListNgSeeder::class,
            TimeSlotShift1_7hSeeder::class,
            TimeSlotShift1_5hSeeder::class,
            TargetUphSeeder::class,
            ProcessSeeder::class,
            SopNameListSeeder::class,
            LeaderNameListSeeder::class,
            SpvNameListSeeder::class,
            TechnicianNameListSeeder::class,
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
