<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use App\Models\Department;

class Holiday2026Seeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua tenant (Dinas/OPD) yang ada
        $departments = Department::all();

        // Daftar Libur Nasional 2026 (Estimasi)
        $holidays = [
            ['date' => '2026-01-01', 'desc' => 'Tahun Baru 2026'],
            ['date' => '2026-02-17', 'desc' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['date' => '2026-02-17', 'desc' => 'Tahun Baru Imlek 2577 Kongzili'],
            ['date' => '2026-03-20', 'desc' => 'Hari Suci Nyepi Tahun Baru Saka 1948'],
            ['date' => '2026-03-20', 'desc' => 'Hari Raya Idul Fitri 1447 H'],
            ['date' => '2026-03-21', 'desc' => 'Hari Raya Idul Fitri 1447 H'],
            ['date' => '2026-04-03', 'desc' => 'Wafat Yesus Kristus'],
            ['date' => '2026-04-05', 'desc' => 'Hari Paskah'],
            ['date' => '2026-05-01', 'desc' => 'Hari Buruh Internasional'],
            ['date' => '2026-05-14', 'desc' => 'Kenaikan Yesus Kristus'],
            ['date' => '2026-05-22', 'desc' => 'Hari Raya Waisak 2570 BE'],
            ['date' => '2026-05-27', 'desc' => 'Hari Raya Idul Adha 1447 H'],
            ['date' => '2026-06-01', 'desc' => 'Hari Lahir Pancasila'],
            ['date' => '2026-06-16', 'desc' => 'Tahun Baru Islam 1448 H'],
            ['date' => '2026-08-17', 'desc' => 'Hari Kemerdekaan RI'],
            ['date' => '2026-08-25', 'desc' => 'Maulid Nabi Muhammad SAW'],
            ['date' => '2026-12-25', 'desc' => 'Hari Raya Natal'],
        ];

        foreach ($departments as $dept) {
            foreach ($holidays as $h) {
                Holiday::updateOrCreate(
                    [
                        'tenant_id' => $dept->id,
                        'date' => $h['date']
                    ],
                    ['description' => $h['desc']]
                );
            }
        }

        $this->command->info('Berhasil memasukkan data libur 2026 untuk semua dinas!');
    }
}