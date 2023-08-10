<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create([
            'code' => 'CAT001',
            'name' => 'Pengaduan Umum',
        ]);

        Category::create([
            'code' => 'CAT002',
            'name' => 'Pengaduan Kebakaran',
        ]);

        Category::create([
            'code' => 'CAT003',
            'name' => 'Pengaduan Layanan Masyarakat',
        ]);
    }
}
