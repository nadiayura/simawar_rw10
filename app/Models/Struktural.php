<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Struktural extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jabatan',
        'periode_mulai',
        'periode_selesai',
        'foto',
        'deskripsi',
        'is_active',
        'urutan',
        'id_warga',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope untuk data yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk Ketua RW
     */
    public function scopeKetuaRw($query)
    {
        return $query->where('jabatan', 'like', '%Ketua RW%');
    }

    /**
     * Scope untuk Ketua RT
     */
    public function scopeKetuaRt($query)
    {
        return $query->where('jabatan', 'like', '%Ketua RT%');
    }

    /**
     * Scope untuk sorting berdasarkan urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('created_at');
    }

    /**
     * Check if this is Ketua RW
     */
    public function isKetuaRw(): bool
    {
        return str_contains(strtolower($this->jabatan), 'ketua rw');
    }

    /**
     * Check if this is Ketua RT
     */
    public function isKetuaRt(): bool
    {
        return str_contains(strtolower($this->jabatan), 'ketua rt');
    }

    /**
     * Get formatted periode
     */
    public function getPeriodeAttribute(): string
    {
        return $this->periode_mulai . ' - ' . $this->periode_selesai;
    }

    /**
     * Relationship dengan tenant (untuk Ketua RT)
     * Catatan: Relasi ini dihapus karena tidak lagi menggunakan no_rt
     */

    /**
     * Relationship dengan warga
     */
    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

}
