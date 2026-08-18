<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::where('name', 'admin')->first();

        $defaultPassword = 'password123';
        $defaultRoleId = 1;

        $wargas = Warga::query()->get(['warga_nik', 'nama', 'email']);
        foreach ($wargas as $warga) {
            $email = trim((string) $warga->email);
            if ($email === '') {
                $localPart = Str::lower((string) $warga->warga_nik);
                $localPart = preg_replace('/[^a-z0-9]+/i', '.', $localPart) ?: 'warga';
                $email = $localPart.'@simawar.test';
            }

            $suffix = preg_replace('/\D/', '', (string) $warga->warga_nik);
            if ($suffix === '') {
                $suffix = Str::random(6);
            }

            $baseEmail = $email;
            $local = Str::contains($baseEmail, '@') ? Str::before($baseEmail, '@') : $baseEmail;
            $domain = Str::contains($baseEmail, '@') ? Str::after($baseEmail, '@') : 'simawar.test';
            $local = $local !== '' ? $local : 'warga';
            $domain = $domain !== '' ? $domain : 'simawar.test';

            $conflict = User::query()
                ->where('email', $email)
                ->where(function ($q) use ($warga) {
                    $q->whereNull('warga_nik')->orWhere('warga_nik', '!=', $warga->warga_nik);
                })
                ->exists();
            if ($conflict) {
                $email = $local.'.'.$suffix.'@'.$domain;
            }

            User::updateOrCreate(
                ['warga_nik' => $warga->warga_nik],
                [
                    'name' => $warga->nama ?: $warga->warga_nik,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'role_id' => $defaultRoleId,
                    'email_verified_at' => now(),
                ]
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@simawar.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role_id' => $roleAdmin?->id,
                'email_verified_at' => now(),
            ]
        );

        // role-based only; no tenant association
    }
}
