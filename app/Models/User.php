<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
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
        'warga_id',
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
        return $this->belongsTo(Warga::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the tenants that belong to this user.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user');
    }

    /**
     * Get the tenants that a user can access.
     */
    public function getTenants(Panel $panel): Collection
    {
        // RW and admin users can access all tenants
        if ($this->role && ($this->role->isRW() || $this->role->isAdmin())) {
            return Tenant::where('is_active', true)->get();
        }

        // RT users can only access their own tenant
        return $this->tenants()->where('is_active', true)->get();
    }

    /**
     * Check if user can access a specific tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        // RW and admin users can access all tenants
        if ($this->role && ($this->role->isRW() || $this->role->isAdmin())) {
            return true;
        }

        // RT users can only access their assigned tenants
        return $this->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    /**
     * Get the user's RT number if they are an RT leader.
     */
    public function getRTNumber(): ?string
    {
        if ($this->role && $this->role->isRT() && $this->warga_id) {
            $warga = $this->warga;
            if ($warga && $warga->ketuaRt && $warga->ketuaRt->is_active) {
                return $warga->ketuaRt->no_rt;
            }
        }

        return null;
    }

    /**
     * Get the user's RW number.
     */
    public function getRWNumber(): ?string
    {
        if ($this->warga_id) {
            $warga = $this->warga;
            if ($warga) {
                return $warga->rw;
            }
        }

        return null;
    }


}
