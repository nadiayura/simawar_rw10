<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetuaRt extends Model
{
    use HasFactory;

    protected $primaryKey = 'ketua_rt_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'ketua_rt_id',
        'no_rt_id',
        'jabatan',
        'warga_nik',
        'alamat',
        'no_hp',
        'periode_mulai',
        'periode_selesai',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if (! $model->warga_nik) {
                return;
            }

            $warga = \App\Models\Warga::find($model->warga_nik);
            if (! $warga) {
                return;
            }

            if ($warga->alamat) {
                $model->alamat = $warga->alamat;
            }

            if (empty($model->no_hp) && $warga->no_hp) {
                $model->no_hp = $warga->no_hp;
            }
        });

        static::creating(function ($model) {
            if (empty($model->ketua_rt_id)) {
                $rt = (string) $model->no_rt_id;
                $prefix = 'K-'.$rt.'-';
                $last = static::query()
                    ->where('no_rt_id', $rt)
                    ->where('ketua_rt_id', 'like', $prefix.'%')
                    ->orderBy('ketua_rt_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->ketua_rt_id)) {
                    $parts = explode('-', $last->ketua_rt_id);
                    $num = (int) end($parts);
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->ketua_rt_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }
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
