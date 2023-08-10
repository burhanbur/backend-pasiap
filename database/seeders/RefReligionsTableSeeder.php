<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\RefReligion;

class RefReligionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RefReligion::create([
            'name' => 'Islam',
        ]);

        RefReligion::create([
            'name' => 'Kristen Protestan',
        ]);

        RefReligion::create([
            'name' => 'Kristen Katolik',
        ]);

        RefReligion::create([
            'name' => 'Hindu',
        ]);

        RefReligion::create([
            'name' => 'Buddha',
        ]);

        RefReligion::create([
            'name' => 'Khonghucu',
        ]);
    }
}
