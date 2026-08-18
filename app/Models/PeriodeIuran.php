<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeIuran extends Model
{
    protected $table = 'periode_iurans';

    protected $primaryKey = 'periode_iuran_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tahun',
        'bulan',
        'tanggal_jatuh_tempo',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jatuh_tempo' => 'date',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->periode_iuran_id)) {
                $tahun = (int) ($model->tahun ?? now()->year);
                $bulan = (int) ($model->bulan ?? now()->month);
                $model->periode_iuran_id = 'IURAN-'.str_pad((string) $tahun, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getNamaBulanAttribute(): string
    {
        $map = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $map[(int) $this->bulan] ?? (string) $this->bulan;
    }
}
