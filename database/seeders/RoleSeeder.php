<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'warga',
                'display_name' => 'Warga',
                'description' => 'Warga biasa yang hanya dapat mengakses data pribadi mereka sendiri',
                'level' => 'basic',
                'hierarchy_level' => 1,
            ],
            [
                'name' => 'tamu',
                'display_name' => 'Tamu',
                'description' => 'Pengguna baru menunggu verifikasi, akses terbatas',
                'level' => 'pending',
                'hierarchy_level' => 0,
            ],
            [
                'name' => 'rt',
                'display_name' => 'Ketua RT',
                'description' => 'Ketua RT yang dapat mengelola semua warga dalam RT yang dipimpinnya',
                'level' => 'rt_admin',
                'hierarchy_level' => 2,
            ],
            [
                'name' => 'rw',
                'display_name' => 'Ketua RW',
                'description' => 'Ketua RW yang dapat mengelola semua warga di semua RT dalam RW yang dipimpinnya',
                'level' => 'rw_admin',
                'hierarchy_level' => 3,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrator sistem dengan akses penuh',
                'level' => 'admin',
                'hierarchy_level' => 4,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']], // Kondisi untuk mencari
                $role // Data yang akan diupdate atau dibuat
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
