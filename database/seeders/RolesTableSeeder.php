<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         Role::create([
            'name' => 'admin', 
            'alias' => 'Admin',
            'guard_name' => 'api'
         ]);

         Role::create([
            'name' => 'user', 
            'alias' => 'User',
            'guard_name' => 'api'
         ]);

         Role::create([
            'name' => 'satpol_pp', 
            'alias' => 'Satpol PP',
            'guard_name' => 'api'
         ]);

         Role::create([
            'name' => 'linmas', 
            'alias' => 'Linmas',
            'guard_name' => 'api'
         ]);

         Role::create([
            'name' => 'petugas', 
            'alias' => 'Petugas',
            'guard_name' => 'api'
         ]);
    }
}
