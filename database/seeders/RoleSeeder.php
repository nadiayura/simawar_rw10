<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
