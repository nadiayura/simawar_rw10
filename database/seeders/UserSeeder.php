<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warga;
use App\Models\Role;
use App\Models\KetuaRt;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil role yang sudah ada
        $roleWarga = Role::where('name', 'warga')->first();
        $roleRt = Role::where('name', 'rt')->first();
        $roleRw = Role::where('name', 'rw')->first();

        // Data user berdasarkan NIK warga
        $userData = [
            [
                'nik' => '3201234567890001',
                'name' => 'Ahmad Suryadi',
                'email' => 'ahmad.suryadi@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890002',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890003',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890004',
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890005',
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890006',
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@email.com',
                'password' => 'password123',
                'role' => 'rt', // Ketua RT
            ],
            [
                'nik' => '3201234567890007',
                'name' => 'Gunawan Wijaya',
                'email' => 'gunawan.wijaya@email.com',
                'password' => 'password123',
                'role' => 'warga', // Warga biasa
            ],
            [
                'nik' => '3201234567890008',
                'name' => 'Hesti Purwanti',
                'email' => 'hesti.purwanti@email.com',
                'password' => 'password123',
                'role' => 'warga', // Warga biasa
            ],
            [
                'nik' => '3201234567890009',
                'name' => 'Indra Kusuma',
                'email' => 'indra.kusuma@email.com',
                'password' => 'password123',
                'role' => 'warga', // Warga biasa
            ],
            [
                'nik' => '3201234567890010',
                'name' => 'Joko Widodo',
                'email' => 'joko.widodo@email.com',
                'password' => 'password123',
                'role' => 'rw', // Ketua RW
            ],
        ];

        foreach ($userData as $data) {
            // Cari warga berdasarkan NIK
            $warga = Warga::where('nik', $data['nik'])->first();
            
            if ($warga) {
                // Tentukan role_id berdasarkan role
                $roleId = null;
                switch ($data['role']) {
                    case 'warga':
                        $roleId = $roleWarga->id;
                        break;
                    case 'rt':
                        $roleId = $roleRt->id;
                        break;
                    case 'rw':
                        $roleId = $roleRw->id;
                        break;
                }

                User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make($data['password']),
                        'role_id' => $roleId,
                        'warga_id' => $warga->id,
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Buat user admin tambahan yang dapat mengakses semua tenant
        $admin = User::updateOrCreate(
            ['email' => 'admin@simawar.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role_id' => $roleRw->id, // Admin sebagai RW
                'email_verified_at' => now(),
            ]
        );

        // Associate admin with all tenants
        $allTenants = \App\Models\Tenant::all();
        $admin->tenants()->syncWithoutDetaching($allTenants->pluck('id'));
    }
}
