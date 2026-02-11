<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TechnicianNameListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['no' => 1, 'nik' => '13020256', 'name' => 'Andriyanto', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 2, 'nik' => '13060566', 'name' => 'Indra Setiawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 3, 'nik' => '14081426', 'name' => 'Nur Hidayat', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 4, 'nik' => '14112191', 'name' => 'Akhmad Risfan Basyir', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 5, 'nik' => '15042671', 'name' => 'Binu Hartoko', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 6, 'nik' => '15093253', 'name' => 'Dede Januar Permana', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 7, 'nik' => '16053374', 'name' => 'Andri Franki , ST', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 8, 'nik' => '16073480', 'name' => 'Ahmadi Bin Amsir', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 9, 'nik' => '20034763', 'name' => 'Muhamad Al Ayubi', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 10, 'nik' => '21055058', 'name' => 'Reza Maulana', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 11, 'nik' => '21055059', 'name' => 'Rifqi Dwi Ashfian', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 12, 'nik' => '21095229', 'name' => 'Ari Agus Setiyawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 13, 'nik' => '21095230', 'name' => 'Candra Soleh', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 14, 'nik' => '21095231', 'name' => 'Fazrin Arrizka', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 15, 'nik' => '21095233', 'name' => 'Syaifullah', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 16, 'nik' => '21105252', 'name' => 'Detar Linus Warasi', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 17, 'nik' => '21125423', 'name' => 'Siti Rohimah', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 18, 'nik' => '21125438', 'name' => 'Dedi Supriatna', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 19, 'nik' => '21125439', 'name' => 'Fajar Kamil', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 20, 'nik' => '22015478', 'name' => 'Dian Wahyudin', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 21, 'nik' => '22035511', 'name' => 'Muhamad Hadi Darmawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 22, 'nik' => '22045536', 'name' => 'Muhamad Hanapi', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 23, 'nik' => '22085623', 'name' => 'Iqbal Mirza', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 24, 'nik' => '22115710', 'name' => 'Awan Wahyu Setiawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 25, 'nik' => '22125771', 'name' => 'Muhamad Iqbal Maulana A', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 26, 'nik' => '23035842', 'name' => 'Ahmad Hamdani', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 27, 'nik' => '23065899', 'name' => 'Iqbal Sulanjana', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 28, 'nik' => '23105968', 'name' => 'Abdul Muktadir', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 29, 'nik' => '23105983', 'name' => 'Zhiva Fazrian Ilhami', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 30, 'nik' => '24016031', 'name' => 'Neng Ridah', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 31, 'nik' => '24096066', 'name' => 'Fajar Nur Alam', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 32, 'nik' => '24106070', 'name' => 'Hyldand Gufron Syidik', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 33, 'nik' => '24116077', 'name' => 'Hendi Rustandi', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 34, 'nik' => '25016093', 'name' => 'Putut Hirawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 35, 'nik' => '25026095', 'name' => 'Farid Khatamikuta', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 36, 'nik' => '25066132', 'name' => 'Badru Jamal', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 37, 'nik' => '25076142', 'name' => 'Risma Marisa', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 38, 'nik' => '25086155', 'name' => 'Supardi', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 39, 'nik' => '25096180', 'name' => 'Hendri', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 40, 'nik' => '25096204', 'name' => 'Afifah Nur Sallamah', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 41, 'nik' => '25106222', 'name' => 'Reza Ramadhani Setiawan', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 42, 'nik' => '25106223', 'name' => 'Ully Nuha', 'created_at' => now(), 'updated_at' => now()],
            ['no' => 43, 'nik' => '25116250', 'name' => 'Rafly Fadhilla', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('technician_name_lists')->upsert($rows, ['nik'], ['no', 'name', 'updated_at', 'created_at']);
    }
}
