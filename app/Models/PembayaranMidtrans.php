<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranMidtrans extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_midtrans';

    protected $primaryKey = 'PembayaranMidtrans_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'jumlah',
        'status_id',
        'tipe_pembayaran',
        'snap_token',
        'redirect_url',
        'order_id',
        'transaksi_id',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->PembayaranMidtrans_id)) {
                $today = now();
                $dateStr = $today->format('d-m-Y');
                $prefix = 'BYR-MDRS-'.$dateStr.'-';

                $last = static::query()
                    ->whereDate('created_at', $today->toDateString())
                    ->where('PembayaranMidtrans_id', 'like', $prefix.'%')
                    ->orderBy('PembayaranMidtrans_id', 'desc')
                    ->first();

                $seq = 1;
                if ($last && is_string($last->PembayaranMidtrans_id)) {
                    $parts = explode('-', $last->PembayaranMidtrans_id);
                    $suffix = end($parts);
                    $num = (int) $suffix;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }

                $model->PembayaranMidtrans_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->order_id)) {
                $model->order_id = 'PMTRNS-'.now()->format('YmdHis').'-'.substr(md5(uniqid('', true)), 0, 6);
            }
            if (empty($model->status_id)) {
                $model->status_id = \App\Models\Status::idForFitur('keuangan', 'pending')
                    ?? \App\Models\Status::idByName('pending');
            }
        });
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\Status::class, 'status_id');
    }
}
