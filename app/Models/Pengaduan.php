<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';

    protected $fillable = [
        'tenant_id',
        'tgl_pengajuan',
        'id_warga',
        'jenis_pengaduan',
        'jdl_pengaduan',
        'status',
        'detail_pengaduan',
        'bukti'
    ];

    protected $casts = [
        'tgl_pengajuan' => 'date',
        'jenis_pengaduan' => 'string',
        'status' => 'string'
    ];

    // Relationship dengan Warga
    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

    // Relationship dengan Tenant
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    // Accessor untuk label jenis pengaduan
    public function getJenisPengaduanLabelAttribute(): string
    {
        return match($this->jenis_pengaduan) {
            'infrastruktur' => 'Infrastruktur',
            'kebersihan' => 'Kebersihan',
            'keamanan' => 'Keamanan',
            'sosial' => 'Sosial',
            'kesehatan' => 'Kesehatan',
            'pendidikan' => 'Pendidikan',
            'ekonomi' => 'Ekonomi',
            'lainnya' => 'Lainnya',
            default => ucfirst($this->jenis_pengaduan)
        };
    }

    // Accessor untuk label status
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status)
        };
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk filter berdasarkan jenis pengaduan
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_pengaduan', $jenis);
    }

    // Scope untuk filter berdasarkan RT (melalui warga)
    public function scopeByRt($query, $noRt)
    {
        return $query->whereHas('warga', function($q) use ($noRt) {
            $q->where('no_rt', $noRt);
        });
    }

    // Scope untuk filter berdasarkan tenant
    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Scope untuk RT - hanya melihat pengaduan dari RT sendiri
    public function scopeForRt($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Scope untuk RW - melihat semua pengaduan dari RT dalam RW
    public function scopeForRw($query, $rwNumber)
    {
        return $query->whereHas('tenant', function($q) use ($rwNumber) {
            $q->where('rw', $rwNumber);
        });
    }
}
