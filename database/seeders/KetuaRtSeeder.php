<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KetuaRt;
use App\Models\Warga;

class KetuaRtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data ketua RT - menggunakan 6 warga pertama sebagai ketua RT
        $ketuaRtData = [
            [
                'no_rt' => '001',
                'nik_warga' => '3201234567890001', // Ahmad Suryadi
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'no_rt' => '002',
                'nik_warga' => '3201234567890002', // Siti Nurhaliza
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'no_rt' => '003',
                'nik_warga' => '3201234567890003', // Budi Santoso
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'no_rt' => '004',
                'nik_warga' => '3201234567890004', // Dewi Lestari
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'no_rt' => '005',
                'nik_warga' => '3201234567890005', // Eko Prasetyo
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'no_rt' => '006',
                'nik_warga' => '3201234567890006', // Fitri Handayani
                'periode_mulai' => '2024-01-01',
                'periode_selesai' => '2026-12-31',
                'is_active' => true,
            ],
        ];

        foreach ($ketuaRtData as $data) {
            // Cari warga berdasarkan NIK
            $warga = Warga::where('nik', $data['nik_warga'])->first();
            
            if ($warga) {
                KetuaRt::updateOrCreate(
                    [
                        'no_rt' => $data['no_rt'],
                        'id_warga' => $warga->id
                    ],
                    [
                        'periode_mulai' => $data['periode_mulai'],
                        'periode_selesai' => $data['periode_selesai'],
                        'is_active' => $data['is_active'],
                    ]
                );
            }
        }
    }
}
