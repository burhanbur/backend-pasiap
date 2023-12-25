<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Profile;

use Exception;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        try {
            $admin = User::create([
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@pasiappaluta.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $admin->assignRole('admin');

            Profile::create([
                'user_id' => $admin->id,
                'sid' => '1234567890123457s',
                'full_name' => $admin->name,
                'email' => $admin->email,
                'phone' => '085695682973'
            ]);

            $user = User::create([
                'name' => 'Twin Edo Nugraha',
                'username' => 'tnugraha',
                'email' => 'twinedo.dev@gmail.com',
                'password' => Hash::make('user123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $user->assignRole('user');

            Profile::create([
                'user_id' => $user->id,
                'sid' => '6543210987654321',
                'full_name' => $user->name,
                'email' => $user->email,
                'phone' => '089602664936'
            ]);

            $satpolpp = User::create([
                'name' => 'Satpol PP',
                'username' => 'satpolpp',
                'email' => 'pogocok@gmail.com',
                'password' => Hash::make('satpolpp123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $satpolpp->assignRole('satpol_pp');

            Profile::create([
                'user_id' => $satpolpp->id,
                'sid' => '6543210987654322',
                'full_name' => $satpolpp->name,
                'email' => $satpolpp->email,
                'phone' => '089602664936'
            ]);

            $linmas = User::create([
                'name' => 'Bayu Wicaksono',
                'username' => 'bwicaksono',
                'email' => 'bikinkotetsu@gmail.com',
                'password' => Hash::make('burhan123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $linmas->assignRole('linmas');

            Profile::create([
                'user_id' => $linmas->id,
                'sid' => '6543210987654352',
                'full_name' => $linmas->name,
                'email' => $linmas->email,
                'phone' => '089602664936'
            ]);

            $petugas = User::create([
                'name' => 'Burhan Mafazi',
                'username' => 'bmafazi',
                'email' => 'burhanburdev@gmail.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $petugas->assignRole('petugas');

            Profile::create([
                'user_id' => $petugas->id,
                'sid' => '1234567890123456',
                'full_name' => $petugas->name,
                'email' => $petugas->email,
                'phone' => '085695682973'
            ]);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
        }
    }
}
