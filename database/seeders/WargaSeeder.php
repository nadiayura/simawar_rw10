<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warga;

class WargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wargaData = [
            [
                'nik' => '3201234567890001',
                'nama' => 'Ahmad Suryadi',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Merdeka No. 10',
                'id_rt' => '001',
                'rw' => '01',
                'no_hp' => '081234567890',
                'email' => 'ahmad.suryadi@email.com',
            ],
            [
                'nik' => '3201234567890002',
                'nama' => 'Siti Nurhaliza',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Merdeka No. 15',
                'id_rt' => '002',
                'rw' => '01',
                'no_hp' => '081234567891',
                'email' => 'siti.nurhaliza@email.com',
            ],
            [
                'nik' => '3201234567890003',
                'nama' => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'agama' => 'Kristen',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Proklamasi No. 5',
                'id_rt' => '003',
                'rw' => '01',
                'no_hp' => '081234567892',
                'email' => 'budi.santoso@email.com',
            ],
            [
                'nik' => '3201234567890004',
                'nama' => 'Dewi Lestari',
                'jenis_kelamin' => 'P',
                'agama' => 'Hindu',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Proklamasi No. 12',
                'id_rt' => '004',
                'rw' => '02',
                'no_hp' => '081234567893',
                'email' => 'dewi.lestari@email.com',
            ],
            [
                'nik' => '3201234567890005',
                'nama' => 'Eko Prasetyo',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Diponegoro No. 8',
                'id_rt' => '005',
                'rw' => '02',
                'no_hp' => '081234567894',
                'email' => 'eko.prasetyo@email.com',
            ],
            [
                'nik' => '3201234567890006',
                'nama' => 'Fitri Handayani',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Diponegoro No. 20',
                'id_rt' => '006',
                'rw' => '02',
                'no_hp' => '081234567895',
                'email' => 'fitri.handayani@email.com',
            ],
            [
                'nik' => '3201234567890007',
                'nama' => 'Ramadhani Prasetyo',
                'jenis_kelamin' => 'L',
                'agama' => 'Budha',
                'status_tinggal' => 'Kontrak',
                'alamat' => 'Jl. Sudirman No. 3',
                'id_rt' => '001',
                'rw' => '01',
                'no_hp' => '081234567896',
                'email' => 'gunawan.wijaya@email.com',
            ],
            [
                'nik' => '3201234567890008',
                'nama' => 'Hesti Purwanti',
                'jenis_kelamin' => 'P',
                'agama' => 'Kristen',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Sudirman No. 18',
                'id_rt' => '002',
                'rw' => '01',
                'no_hp' => '081234567897',
                'email' => 'hesti.purwanti@email.com',
            ],
            [
                'nik' => '3201234567890009',
                'nama' => 'Indra Kusuma',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Gatot Subroto No. 7',
                'id_rt' => '003',
                'rw' => '01',
                'no_hp' => '081234567898',
                'email' => 'indra.kusuma@email.com',
            ],
            [
                'nik' => '3201234567890010',
                'nama' => 'Joko Widodo',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_tinggal' => 'Tetap',
                'alamat' => 'Jl. Gatot Subroto No. 25',
                'id_rt' => '004',
                'rw' => '02',
                'no_hp' => '081234567899',
                'email' => 'joko.widodo@email.com',
            ],
        ];

        foreach ($wargaData as $data) {
            Warga::updateOrCreate(
                ['nik' => $data['nik']], // Cek berdasarkan NIK
                $data
            );
        }
    }
}
