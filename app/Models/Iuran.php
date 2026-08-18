<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    protected $primaryKey = 'iuran_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_iuran',
        'jumlah_default',
        'deskripsi',
    ];
    
    protected $casts = [
        'jumlah_default' => 'decimal:2',
    ];

    public function wargas()
    {
        return $this->hasMany(Warga::class, 'iuran_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->iuran_id)) {
                $last = static::query()
                    ->where('iuran_id', 'like', 'IURAN-JNS-%')
                    ->orderBy('iuran_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->iuran_id)) {
                    $parts = explode('-', $last->iuran_id);
                    $suffix = end($parts);
                    $num = (int) $suffix;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->iuran_id = 'IURAN-JNS-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
