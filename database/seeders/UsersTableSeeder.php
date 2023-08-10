<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'name' => 'Burhan Mafazi',
            'username' => 'bmafazi',
            'email' => 'burhanburdev@gmail.com',
            'password' => Hash::make('admin123')
        ]);

        $admin->assignRole('admin');

        $user = User::create([
            'name' => 'Twin Edo Nugraha',
            'username' => 'tnugraha',
            'email' => 'twinedo.dev@gmail.com',
            'password' => Hash::make('user123')
        ]);

        $user->assignRole('user');
    }
}
