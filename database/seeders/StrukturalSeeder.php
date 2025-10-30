<?php

namespace Database\Seeders;

use App\Models\Struktural;
use Illuminate\Database\Seeder;

class StrukturalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $strukturals = [
            [
                'nama' => 'Budi Santoso',
                'jabatan' => 'Ketua RW',
                'no_rt' => null,
                'periode_mulai' => '2023',
                'periode_selesai' => '2026',
                'foto' => null,
                'deskripsi' => 'Ketua RW yang berpengalaman dalam mengelola administrasi warga.',
                'is_active' => true,
                'urutan' => 1,
            ],
            [
                'nama' => 'Ahmad Wijaya',
                'jabatan' => 'Ketua RT 01',
                'no_rt' => '01',
                'periode_mulai' => '2023',
                'periode_selesai' => '2026',
                'foto' => null,
                'deskripsi' => 'Ketua RT 01 yang aktif dalam kegiatan kemasyarakatan.',
                'is_active' => true,
                'urutan' => 2,
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'jabatan' => 'Ketua RT 02',
                'no_rt' => '02',
                'periode_mulai' => '2023',
                'periode_selesai' => '2026',
                'foto' => null,
                'deskripsi' => 'Ketua RT 02 yang peduli terhadap kesejahteraan warga.',
                'is_active' => true,
                'urutan' => 3,
            ],
            [
                'nama' => 'Joko Susilo',
                'jabatan' => 'Ketua RT 03',
                'no_rt' => '03',
                'periode_mulai' => '2023',
                'periode_selesai' => '2026',
                'foto' => null,
                'deskripsi' => 'Ketua RT 03 yang berdedikasi tinggi.',
                'is_active' => true,
                'urutan' => 4,
            ],
        ];

        foreach ($strukturals as $struktural) {
            Struktural::create($struktural);
        }
    }
}