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
                'name' => 'Burhan Mafazi',
                'username' => 'bmafazi',
                'email' => 'burhanburdev@gmail.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            $admin->assignRole('admin');

            Profile::create([
                'user_id' => $admin->id,
                'sid' => '1234567890123456',
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

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
        }
    }
}
