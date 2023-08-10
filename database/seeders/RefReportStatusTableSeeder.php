<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\RefReportStatus;

class RefReportStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RefReportStatus::create([
            'name' => 'PROSES',
        ]);

        RefReportStatus::create([
            'name' => 'AMBIL',
        ]);

        RefReportStatus::create([
            'name' => 'SELESAI',
        ]);
    }
}
