<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoRt extends Model
{
    protected $table = 'no_rts';

    protected $primaryKey = 'no_rt_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nomor',
        'rw',
    ];

    public function wargas()
    {
        return $this->hasMany(Warga::class, 'no_rt_id');
    }

    public function ketuaRts()
    {
        return $this->hasMany(KetuaRt::class, 'no_rt_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->no_rt_id)) {
                $last = static::query()
                    ->where('no_rt_id', 'like', 'RT-%')
                    ->orderBy('no_rt_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->no_rt_id)) {
                    $parts = explode('-', $last->no_rt_id);
                    $suffix = end($parts);
                    $num = (int) $suffix;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->no_rt_id = 'RT-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
