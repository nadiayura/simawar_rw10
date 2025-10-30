<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant;


class KetuaRt extends Model
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     */

    protected $fillable = [
        'no_rt',
        'jabatan',
        'id_warga',
        'periode_mulai',
        'periode_selesai',
        'is_active',
    ];
    
    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'no_rt', 'no_rt');
    }

    // Scope methods for filtering by jabatan
    public function scopeKetuaRt($query)
    {
        return $query->where('jabatan', 'Ketua RT');
    }

    public function scopeSekretarisRt($query)
    {
        return $query->where('jabatan', 'Sekretaris RT');
    }

    public function scopeBendaharaRt($query)
    {
        return $query->where('jabatan', 'Bendahara RT');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function isKetuaRt()
    {
        return $this->jabatan === 'Ketua RT';
    }

    public function isSekretarisRt()
    {
        return $this->jabatan === 'Sekretaris RT';
    }

    public function isBendaharaRt()
    {
        return $this->jabatan === 'Bendahara RT';
    }
}
