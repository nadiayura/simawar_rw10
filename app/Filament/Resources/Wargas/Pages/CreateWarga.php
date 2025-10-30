<?php

namespace App\Filament\Resources\Wargas\Pages;

use App\Filament\Resources\Wargas\WargaResource;
use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateWarga extends CreateRecord
{
    protected static string $resource = WargaResource::class;

    protected function afterCreate(): void
    {
        $warga = $this->record;
        $data = $this->data;

        // Always create user account if email is provided
        if ($warga->email) {
            // Check if user with this email already exists
            $existingUser = User::where('email', $warga->email)->first();
            
            if (!$existingUser) {
                // Get the Warga role
                $wargaRole = Role::where('name', 'warga')->first();
                
                if ($wargaRole) {
                    // Use custom password if provided, otherwise use default 'password123'
                    $password = !empty($data['user_password']) ? $data['user_password'] : 'password123';
                    
                    // Create user account
                    $user = User::create([
                        'name' => $warga->nama,
                        'email' => $warga->email,
                        'password' => Hash::make($password),
                        'role_id' => $wargaRole->id,
                        'warga_id' => $warga->id,
                    ]);

                    // Assign tenant based on RT
                    $tenant = Tenant::where('no_rt', $warga->id_rt)
                                   ->where('rw', $warga->rw)
                                   ->first();
                    
                    if ($tenant) {
                        $user->tenants()->attach($tenant->id);
                    }

                    // Show success message
                    $passwordUsed = !empty($data['user_password']) ? 'password yang diatur' : 'password123';
                    $this->getCreatedNotification()
                         ->title('Warga berhasil ditambahkan!')
                         ->body("Akun login telah dibuat dengan email: {$warga->email}. Password: {$passwordUsed}");
                }
            } else {
                // Show warning if email already exists
                $this->getCreatedNotification()
                     ->title('Warga berhasil ditambahkan!')
                     ->body("Warga berhasil ditambahkan, tetapi akun login tidak dibuat karena email {$warga->email} sudah digunakan.");
            }
        } else {
            // Show info if no email provided
            $this->getCreatedNotification()
                 ->title('Warga berhasil ditambahkan!')
                 ->body('Warga berhasil ditambahkan. Akun login tidak dibuat karena email tidak diisi.');
        }
    }
}
