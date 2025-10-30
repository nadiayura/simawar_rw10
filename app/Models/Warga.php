<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;


class Warga extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'nik',
        'nama',
        'jenis_kelamin',
        'agama',
        'status_tinggal',
        'alamat',
        'id_rt',
        'rw',
        'no_hp',
        'email',
    ];

    protected static function boot()
    {
        parent::boot();

        // Update user when warga is updated
        static::updated(function ($warga) {
            $user = User::where('warga_id', $warga->id)->first();
            
            if ($user) {
                $user->update([
                    'name' => $warga->nama,
                    'email' => $warga->email ?? $user->email, // Keep existing email if new one is empty
                ]);
            }
        });
    }
    public function ketuaRt()
    {
        return $this->hasOne(KetuaRt::class, 'id_warga');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'warga_id');
    }

    public function tenant()
    {
        // Simplified relationship - will be filtered by the resource query
        return $this->belongsTo(Tenant::class, 'id_rt', 'no_rt');
    }

    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'id_warga');
    }

    /**
     * Create user account for this warga if it doesn't exist
     */
    public function createUserAccount()
    {
        if (!$this->email || $this->user) {
            return null; // No email or user already exists
        }

        // Check if user with this email already exists
        $existingUser = User::where('email', $this->email)->first();
        if ($existingUser) {
            return null; // Email already taken
        }

        // Get the Warga role
        $wargaRole = Role::where('name', 'warga')->first();
        if (!$wargaRole) {
            return null; // Warga role not found
        }

        // Create user account
        $user = User::create([
            'name' => $this->nama,
            'email' => $this->email,
            'password' => Hash::make('password123'), // Use 'password123' as default password
            'role_id' => $wargaRole->id,
            'warga_id' => $this->id,
        ]);

        // Assign tenant based on RT
        $tenant = Tenant::where('no_rt', $this->id_rt)
                       ->where('rw', $this->rw)
                       ->first();
        
        if ($tenant) {
            $user->tenants()->attach($tenant->id);
        }

        return $user;
    }
}
