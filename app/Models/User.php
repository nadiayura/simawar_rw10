<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'warga_nik',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'role_id' => 8, // Default role_id untuk tamu
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the warga that owns the user.
     */
    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $role = $this->role;

        if (! $role) {
            return false;
        }

        // Jika panel adalah warga dan user memiliki role warga, izinkan akses
        if ($panel->getId() === 'warga') {
            return $role && ($role->isWarga() || $role->isTamu() || $role->isRT() || $role->isRW() || $role->isAdmin());
        }

        return $role && ($role->isRT() || $role->isRW() || $role->isAdmin());

        // Jika panel adalah admin dan user memiliki role selain warga (RT atau RW), izinkan akses
        if ($panel->getId() === 'admin') {
            return $role->isRT() || $role->isRW();
        }
    }

    /**
     * Get the panel that should be used as the user's home panel.
     */
    public function getHomePanel(): ?string
    {
        $role = $this->role;

        if (! $role) {
            return null;
        }

        // Jika user adalah warga, arahkan ke panel warga
        if ($role->isWarga()) {
            return 'warga';
        }

        // Untuk role selain warga (RT dan RW), arahkan ke panel admin
        return 'admin';
    }

    // Override method untuk mengabaikan verifikasi email
    public function hasVerifiedEmail(): bool
    {
        return true; // Selalu mengembalikan true untuk mengabaikan verifikasi email
    }

    public function getRTNumber(): ?string
    {
        if ($this->role && $this->role->isRT() && $this->warga_nik) {
            $warga = $this->warga;
            if ($warga && $warga->ketuaRt && $warga->ketuaRt->is_active) {
                return $warga->ketuaRt->no_rt;
            }
        }

        return null;
    }

    public function getRWNumber(): ?string
    {
        if ($this->warga_nik) {
            $warga = $this->warga;
            if ($warga) {
                return $warga->rw;
            }
        }

        return null;
    }
}
