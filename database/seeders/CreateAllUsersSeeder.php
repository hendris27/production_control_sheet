<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAllUsersSeeder extends Seeder
{
    public function run()
    {
        // Pastikan semua role ada
        $roles = [
            'admin',
            'leader',
            'sop',
            'spv',
            'operator',
        ];

       foreach ($roles as $role) {
    Role::firstOrCreate([
        'name' => $role,
        'guard_name' => 'web',
    ]);
        }

        // Mapping NIK / nama ke role khusus
        $specialRoles = [
            '88888888' => 'admin',   // Administrator
            '25096180' => 'admin',   // Hendri
            '13020256' => 'leader',  // Andriyanto
            '14081426' => 'leader',     // Nur Hidayat
            '14112191' => 'leader',     // Akhmad Risfan Basyir
            '15093253' => 'leader',     // Dede Januar Permana
            '16073480' => 'leader',     // Ahmadi Bin Amsir
            '13060566' => 'spv',     // Indra Setiawan
            '15042671' => 'spv',     // Binu Hartoko
            '24116077' => 'spv',     // Hendi Rustandi

            // Prodduction 2
            '13020150' => 'spv',     // May Saroh
            '13010064' => 'spv',     // Renny Kumala
            '22075598' => 'spv',     // Juli Mangita N
            '14111902' => 'leader',     // Novalina Purba
            '15052919' => 'leader',     // Ertin Dwi Aryani
            '15042590' => 'leader',     // Dian Susanti
            '17053773' => 'leader',     // Yoni Diansutra H.
            '14071396' => 'leader',     // Noferina Sianturi
            '17033673' => 'leader',     // Febri Fitriyani
            '15113343' => 'sop',     // Neni Kunaetin
            '23075918' => 'sop',     // Rizki Rahayu
            '21065091' => 'sop',     // Siti Darmayanti
            '21125351' => 'sop',     // Sintiawati
            '21125445' => 'sop',     // Devi Sundari
            '23045868' => 'sop',     // Jessita Kurniawati
            '22015464' => 'sop',     // Tina Windara
            '22015467' => 'sop',     // Putri Kurnia
            '22015469' => 'sop',     // Sylvia Rostiani
            '22055555' => 'sop',     // Anggi Lestari
            '22065566' => 'sop',     // Tantri Susilawati
            '23115991' => 'sop',     // Rahmad Hidayat
            '22015486' => 'sop',     // Ukhti Nurul Fitriani
            '21105270' => 'sop',     // Siti Jubaedah
            '22015463' => 'sop',     // Zahra Jilan Sahirah
            '22035501' => 'sop',     // Trihanda Yani
            '22115696' => 'sop',     // Lirfa Lestari Ambarwati
            '22015468' => 'sop',     // Eva Ameliana
            '23015787' => 'sop',     // Camila Cantika Dewi
            '21045021' => 'sop',     // Nisa Nurfadiyah


        ];

        // Nama lengkap untuk specialRoles (jika tersedia)
        $specialNames = [
            '88888888' => 'Administrator',
            '25096180' => 'Hendri',
            '13020256' => 'Andriyanto',
            '14081426' => 'Nur Hidayat',
            '14112191' => 'Akhmad Risfan Basyir',
            '15093253' => 'Dede Januar Permana',
            '16073480' => 'Ahmadi Bin Amsir',
            '13060566' => 'Indra Setiawan',
            '15042671' => 'Binu Hartoko',
            '24116077' => 'Hendi Rustandi',

            '13020150' => 'May Saroh',
            '13010064' => 'Renny Kumala',
            '22075598' => 'Juli Mangita N',
            '14111902' => 'Novalina Purba',
            '15052919' => 'Ertin Dwi Aryani',
            '15042590' => 'Dian Susanti',
            '17053773' => 'Yoni Diansutra H.',
            '14071396' => 'Noferina Sianturi',
            '17033673' => 'Febri Fitriyani',
            '15113343' => 'Neni Kunaetin',
            '23075918' => 'Rizki Rahayu',
            '21065091' => 'Siti Darmayanti',
            '21125351' => 'Sintiawati',
            '21125445' => 'Devi Sundari',
            '23045868' => 'Jessita Kurniawati',
            '22015464' => 'Tina Windara',
            '22015467' => 'Putri Kurnia',
            '22015469' => 'Sylvia Rostiani',
            '22055555' => 'Anggi Lestari',
            '22065566' => 'Tantri Susilawati',
            '23115991' => 'Rahmad Hidayat',
            '22015486' => 'Ukhti Nurul Fitriani',
            '21105270' => 'Siti Jubaedah',
            '22015463' => 'Zahra Jilan Sahirah',
            '22035501' => 'Trihanda Yani',
            '22115696' => 'Lirfa Lestari Ambarwati',
            '22015468' => 'Eva Ameliana',
            '23015787' => 'Camila Cantika Dewi',
            '21045021' => 'Nisa Nurfadiyah',
        ];

        // Pastikan user untuk NIK khusus (specialRoles) ada dan dapatkan role-nya
        foreach ($specialRoles as $nik => $rName) {
            $u = User::firstOrCreate(
                ['nik' => $nik],
                [
                    'name' => $specialNames[$nik] ?? $nik,
                    'password' => Hash::make($nik),
                    'role' => $rName,
                ]
            );

            // Assign role for special users
            $u->syncRoles([$rName]);
            if (method_exists($u, 'updateRoleColumn')) {
                $u->updateRoleColumn();
            }
        }

        $list = [
            // Engineering 02
            ['nik' => '20034763','name' => 'Muhamad Al Ayubi'],
            ['nik' => '21055058','name' => 'Reza Maulana'],
            ['nik' => '21055059','name' => 'Rifqi Dwi Ashfian'],
            ['nik' => '21095229','name' => 'Ari Agus Setiyawan'],
            ['nik' => '21095230','name' => 'Candra Soleh'],
            ['nik' => '21095231','name' => 'Fazrin Arrizka'],
            ['nik' => '21095233','name' => 'Syaifullah'],
            ['nik' => '21105252','name' => 'Detar Linus Warasi'],
            ['nik' => '21125423','name' => 'Siti Rohimah'],
            ['nik' => '21125438','name' => 'Dedi Supriatna'],
            ['nik' => '21125439','name' => 'Fajar Kamil'],
            ['nik' => '22015478','name' => 'Dian Wahyudin'],
            ['nik' => '22035511','name' => 'Muhamad Hadi Darmawan'],
            ['nik' => '22045536','name' => 'Muhamad Hanapi'],
            ['nik' => '22085623','name' => 'Iqbal Mirza'],
            ['nik' => '22115710','name' => 'Awan Wahyu Setiawan'],
            ['nik' => '22125771','name' => 'Muhamad Iqbal Maulana A'],
            ['nik' => '23035842','name' => 'Ahmad Hamdani'],
            ['nik' => '23065899','name' => 'Iqbal Sulanjana'],
            ['nik' => '23105968','name' => 'Abdul Muktadir'],
            ['nik' => '23105983','name' => 'Zhiva Fazrian Ilhami'],
            ['nik' => '24016031','name' => 'Neng Ridah'],
            ['nik' => '24096066','name' => 'Fajar Nur Alam'],
            ['nik' => '24106070','name' => 'Hyldand Gufron Syidik'],
            ['nik' => '25016093','name' => 'Putut Hirawan'],
            ['nik' => '25026095','name' => 'Farid Khatamikuta'],
            ['nik' => '25066132','name' => 'Badru Jamal'],
            ['nik' => '25076142','name' => 'Risma Marisa'],
            ['nik' => '25086155','name' => 'Supardi'],
            ['nik' => '25096204','name' => 'Afifah Nur Sallamah'],
            ['nik' => '25106222','name' => 'Reza Ramadhani Setiawan'],
            ['nik' => '25106223','name' => 'Ully Nuha'],
            ['nik' => '25116250','name' => 'Rafly Fadhilla'],

            // Production 02 Operator
            ['nik' => '25000396', 'name' => 'Maryati'],
            ['nik' => '25000377', 'name' => 'Wiwin'],
            ['nik' => '26000520', 'name' => 'Selvi Ariska'],
            ['nik' => '26000509', 'name' => 'Puteri Najwaddawamiyah'],
            ['nik' => '26000494', 'name' => 'Salwa Salsabila'],
            ['nik' => '21045020', 'name' => 'Irmawati'],
            ['nik' => '21055047', 'name' => 'Siti Aminahtul Nuraeni'],
            ['nik' => '21065094', 'name' => 'Tasya Nadia Putri'],
            ['nik' => '21065109', 'name' => 'Fidiah Ramadhani'],
            ['nik' => '21075168', 'name' => 'Septiana Sianturi'],
            ['nik' => '21105274', 'name' => 'Yosi Alvira'],
            ['nik' => '21115286', 'name' => 'Herawati'],
            ['nik' => '21115320', 'name' => 'Devi Safitri'],
            ['nik' => '21115332', 'name' => 'Nersih'],
            ['nik' => '21115336', 'name' => 'Sumiati'],
            ['nik' => '21125353', 'name' => 'Aisah'],
            ['nik' => '21125407', 'name' => 'Kasti Kusmiati'],
            ['nik' => '21125428', 'name' => 'Yesa Salwa Meida'],
            ['nik' => '21125450', 'name' => 'Elia Nurmala'],
            ['nik' => '22045533', 'name' => 'Sri Yuhaeni'],
            ['nik' => '22045540', 'name' => 'Annas Tasya Wijayanti'],
            ['nik' => '22055544', 'name' => 'Reni Revina Ayuni'],
            ['nik' => '22055546', 'name' => 'Siti Fatimah'],
            ['nik' => '22075604', 'name' => 'Silfi Nur Fikawati'],
            ['nik' => '22085628', 'name' => 'Siti Nurazizah'],
            ['nik' => '22085632', 'name' => 'Devi Novitasari'],
            ['nik' => '22115712', 'name' => 'Bela Selvira'],
            ['nik' => '22125736', 'name' => 'Aliyah Wati'],
            ['nik' => '23015803', 'name' => 'Alya Andini'],
            ['nik' => '23045865', 'name' => 'Pipit Wulansari'],
            ['nik' => '23045867', 'name' => 'Yulianti'],
            ['nik' => '23055871', 'name' => 'Alvina Damayanti'],
            ['nik' => '23055872', 'name' => 'Devi Aprianti'],
            ['nik' => '23055877', 'name' => 'Sulistia Aprianti'],
            ['nik' => '23055878', 'name' => 'Tika Wuri Handayani'],
            ['nik' => '23065906', 'name' => 'Evi Fitria'],
            ['nik' => '23075912', 'name' => 'Siti Nuraeni'],
            ['nik' => '23095951', 'name' => 'Rifa Padilah'],
            ['nik' => '23095954', 'name' => 'Nendah Purnama Sari'],
            ['nik' => '23105972', 'name' => 'Auradiva Salsabilla Nursetiadi'],
            ['nik' => '25076145', 'name' => 'Fani Nurahmatilah'],
            ['nik' => '25076147', 'name' => 'Aliah'],
            ['nik' => '25096182', 'name' => 'Susi Bayu Anggraini'],
            ['nik' => '25096197', 'name' => 'Nagita Trisna Winata'],
            ['nik' => '25096215', 'name' => 'Putri Aliffiah'],
            ['nik' => '25106226', 'name' => 'Anisa'],
            ['nik' => '25106228', 'name' => 'Rini'],
            ['nik' => '25106229', 'name' => 'Annisa Putri Chintia'],
            ['nik' => '25106232', 'name' => 'Sipa Kusumawati'],
            ['nik' => '26016254', 'name' => 'Intan Nuraeni'],
            ['nik' => '25000380', 'name' => 'Aulia Azzahra'],
            ['nik' => '25000397', 'name' => 'Murtapiah'],
            ['nik' => '25000409', 'name' => 'Ayu Azizzah'],
            ['nik' => '25096200', 'name' => 'Syahbilal Ageng Artasya Lukman'],
            ['nik' => '25000443', 'name' => 'Indy Mulia Ramadhina'],
            ['nik' => '25000453', 'name' => 'Rohana'],
            ['nik' => '25096184', 'name' => 'Devi Tri Rahayu'],
            ['nik' => '25000442', 'name' => 'Asti'],
            ['nik' => '25066122', 'name' => 'Ahmad Maulana'],
            ['nik' => '25000408', 'name' => 'Deyan Nanda Septiyandi'],
            ['nik' => '26000488', 'name' => 'Bella Resti Fauzy'],
            ['nik' => '26000492', 'name' => 'Nurlela'],
            ['nik' => '26000496', 'name' => 'Handika Jaelani'],
            ['nik' => '25000464', 'name' => 'Nurafiani'],
            ['nik' => '25096186', 'name' => 'Kurnia'],
            ['nik' => '26000493', 'name' => 'Nuroh Wati'],
            ['nik' => '25000466', 'name' => 'Karina Yuraini'],
            ['nik' => '26000517', 'name' => 'Nadya Pratiwi'],
            ['nik' => '21035010', 'name' => 'Uun Inayah'],
            ['nik' => '21045018', 'name' => 'Dila Fardila'],
            ['nik' => '21045019', 'name' => 'Ira Suryani'],
            ['nik' => '21055045', 'name' => 'Reni Solihat'],
            ['nik' => '21055048', 'name' => 'Marshela Ananda'],
            ['nik' => '21075139', 'name' => 'Rafikah Firdausi'],
            ['nik' => '21075166', 'name' => 'Teten Rahmawati'],
            ['nik' => '21085212', 'name' => 'Putri Feriska'],
            ['nik' => '21115321', 'name' => 'Hartati'],
            ['nik' => '21115328', 'name' => 'Zulfa Nurmaulidiah'],
            ['nik' => '21115334', 'name' => 'Wenny Nurkholifah'],
            ['nik' => '21115343', 'name' => 'Nita Ratna Nengsih'],
            ['nik' => '21125357', 'name' => 'Anisa Sukriah'],
            ['nik' => '21125368', 'name' => 'Fathia Azzahra Nur H'],
            ['nik' => '21125372', 'name' => 'Ika Padilah'],
            ['nik' => '21125374', 'name' => 'Indah Wulansari Suganda'],
            ['nik' => '21125381', 'name' => 'Safitri Andini'],
            ['nik' => '21125389', 'name' => 'Yasika Camelia'],
            ['nik' => '21125395', 'name' => 'Aan Srinita'],
            ['nik' => '22015493', 'name' => 'Roshidyana Artasavira Pramesti'],
            ['nik' => '22075602', 'name' => 'Nuranisa'],
            ['nik' => '22075605', 'name' => 'Zikriana Putri Rahmadani'],
            ['nik' => '22075610', 'name' => 'Syavira Ananda Awalia'],
            ['nik' => '22085613', 'name' => 'Lia Sari'],
            ['nik' => '22095641', 'name' => 'Yulia Pramudita'],
            ['nik' => '22095642', 'name' => 'Siti Rumsiah'],
            ['nik' => '22095643', 'name' => 'Shyntia Pranciska'],
            ['nik' => '22115708', 'name' => 'Lilis Kurniati'],
            ['nik' => '22115716', 'name' => 'Julia Rahmawati'],
            ['nik' => '22115719', 'name' => 'Bening Fahruni Koesmiriyani'],
            ['nik' => '23015779', 'name' => 'Alpi Sahrin'],
            ['nik' => '23025827', 'name' => 'Yuyun Agustin'],
            ['nik' => '23065905', 'name' => 'Pooja Jaharawati'],
            ['nik' => '23075910', 'name' => 'Febi Edfrilia Ningsih'],
            ['nik' => '23075911', 'name' => 'Natasha Dwi Aulia'],
            ['nik' => '23075920', 'name' => 'Ainun Fauziah'],
            ['nik' => '23075921', 'name' => 'Adila Setiani'],
            ['nik' => '23085935', 'name' => 'Ela Agustin'],
            ['nik' => '24016027', 'name' => 'Natasya Gusti Putri'],
            ['nik' => '25066125', 'name' => 'Muhammad Rizki Haraqi'],
            ['nik' => '25066126', 'name' => 'Rifqi Muhammad Ramadhan'],
            ['nik' => '25086164', 'name' => 'Dhea'],
            ['nik' => '25086172', 'name' => 'Euis Sulistianingsih'],
            ['nik' => '25096212', 'name' => 'Aina Fatimah'],
            ['nik' => '25096214', 'name' => 'Nabila Kirana'],
            ['nik' => '25096173', 'name' => 'Neisya Nabila Putri'],
            ['nik' => '25106227', 'name' => 'Titi Rianti'],
            ['nik' => '25106234', 'name' => 'Alifya Zulfa Zhahira'],
            ['nik' => '25106235', 'name' => 'Amel Aprianti'],
            ['nik' => '25106239', 'name' => 'Ropiah Tulhasanah'],
            ['nik' => '25000350', 'name' => 'Iim Oktaviani'],
            ['nik' => '25000360', 'name' => 'Anita Rahmawati'],
            ['nik' => '25000375', 'name' => 'Tiara Dwi Anggraeni'],
            ['nik' => '25000378', 'name' => 'Nabilah Kataniya'],
            ['nik' => '25000379', 'name' => 'Sahwa Laeli Fitriani'],
            ['nik' => '25000385', 'name' => 'Fadilla Zuhro'],
            ['nik' => '25000386', 'name' => 'Julaeha'],
            ['nik' => '25000388', 'name' => 'Nur Ristiani'],
            ['nik' => '25000389', 'name' => 'Nurqiyah'],
            ['nik' => '25000351', 'name' => 'Nina Ramadani'],
            ['nik' => '26000503', 'name' => 'Desi Reza'],
            ['nik' => '26000518', 'name' => 'Rizkyka Putri Ramadhani'],
            ['nik' => '26000508', 'name' => 'Naila Pebriyanti'],
        ];

        foreach ($list as $item) {

            // Default role
            $roleName = 'operator';

            // Role khusus berdasarkan NIK
            if (isset($specialRoles[$item['nik']])) {
                $roleName = $specialRoles[$item['nik']];
            }

            $user = User::firstOrCreate(
                ['nik' => $item['nik']],
                [
                    'name'     => $item['name'],
                    'password' => Hash::make($item['nik']),
                    'role'     => $roleName,
                ]
            );

            $user->syncRoles([$roleName]);

            // Optional: sync ke kolom role di tabel users
            if (method_exists($user, 'updateRoleColumn')) {
                $user->updateRoleColumn();
            }
        }
    }
}
