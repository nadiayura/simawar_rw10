<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'no_rt',
        'rw',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to this tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user');
    }

    /**
     * Get the warga records for this tenant (RT).
     */
    public function warga(): HasMany
    {
        return $this->hasMany(Warga::class, 'id_rt', 'no_rt')
                    ->where('rw', $this->rw);
    }

    /**
     * Get the ketua RT record for this tenant.
     */
    public function ketuaRt(): HasMany
    {
        return $this->hasMany(KetuaRt::class, 'no_rt', 'no_rt');
    }

    /**
     * Get the pengaduan records for this tenant (RT).
     */
    public function pengaduan(): HasMany
    {
        return $this->hasMany(Pengaduan::class, 'tenant_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
