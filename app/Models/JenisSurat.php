<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $primaryKey = 'jenis_surat_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'jenis_surat_id',
        'nama_surat',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->jenis_surat_id)) {
                $last = static::query()
                    ->where('jenis_surat_id', 'like', 'JNS-SRT-KET-%')
                    ->orderBy('jenis_surat_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->jenis_surat_id)) {
                    $parts = explode('-', $last->jenis_surat_id);
                    $suffix = end($parts);
                    $num = (int) $suffix;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->jenis_surat_id = 'JNS-SRT-KET-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
