<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal',
        'jenis_kegiatan',
        'foto_kegiatan',
        'tenant_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Enum values for jenis_kegiatan
    public const JENIS_KEGIATAN = [
        'karang_taruna' => 'Karang Taruna',
        'posyandu' => 'Posyandu',
        'posbindu' => 'Posbindu',
        'umum' => 'Umum',
    ];

    // Accessor for jenis_kegiatan label
    public function getJenisKegiatanLabelAttribute()
    {
        return self::JENIS_KEGIATAN[$this->jenis_kegiatan] ?? $this->jenis_kegiatan;
    }

    // Scope for filtering by jenis_kegiatan
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_kegiatan', $jenis);
    }

    // Scope for upcoming activities
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString());
    }

    // Scope for past activities
    public function scopePast($query)
    {
        return $query->where('tanggal', '<', now()->toDateString());
    }

    // Relationship with Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
