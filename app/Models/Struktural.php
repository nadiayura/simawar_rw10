<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Struktural extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jabatan',
        'periode_mulai',
        'periode_selesai',
        'deskripsi',
        'is_active',
        'urutan',
        'warga_nik',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeKetuaRw($query)
    {
        return $query->where('jabatan', 'like', '%Ketua RW%');
    }

    public function scopeKetuaRt($query)
    {
        return $query->where('jabatan', 'like', '%Ketua RT%');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('created_at');
    }

    public function isKetuaRw(): bool
    {
        return str_contains(strtolower($this->jabatan), 'ketua rw');
    }

    public function isKetuaRt(): bool
    {
        return str_contains(strtolower($this->jabatan), 'ketua rt');
    }

    public function getPeriodeAttribute(): string
    {
        return $this->periode_mulai.' - '.$this->periode_selesai;
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }
}
