<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'hierarchy_level',
    ];

    protected $casts = [
        'hierarchy_level' => 'integer',
    ];

    /**
     * Get users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is warga level
     */
    public function isWarga(): bool
    {
        return $this->level === 'basic' || $this->name === 'warga';
    }

    /**
     * Check if role is Tamu level
     */
    public function isTamu(): bool
    {
        return $this->level === 'pending' || $this->name === 'tamu';
    }

    /**
     * Check if role is RT level
     */
    public function isRT(): bool
    {
        return $this->level === 'rt_admin';
    }

    /**
     * Check if role is RW level
     */
    public function isRW(): bool
    {
        return $this->level === 'rw_admin';
    }

    /**
     * Check if role is Admin level
     */
    public function isAdmin(): bool
    {
        return $this->level === 'admin';
    }

    /**
     * Check if role has higher or equal hierarchy than given level
     */
    public function hasAccessLevel(string $level): bool
    {
        $levelHierarchy = [
            'warga' => 1,
            'rt' => 2,
            'rw' => 3,
            'tamu' => 0,
        ];

        return $this->hierarchy_level >= ($levelHierarchy[$level] ?? 0);
    }

    /**
     * Scope for specific level
     */
    public function scopeLevel($query, string $level)
    {
        return $query->where('level', $level);
    }
}
