<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            RefReligionsTableSeeder::class,
            RefReportStatusTableSeeder::class,
            UserRolesTableSeeder::class,
            SettingsTableSeeder::class,
        ]);
    }
}
