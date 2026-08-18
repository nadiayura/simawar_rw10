<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTunai extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_tunai';

    protected $primaryKey = 'PembayaranTunai_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'PembayaranTunai_id',
        'tagihan_iuran_id',
        'nominal_dibayarkan',
        'status_id',
        'bukti',
        'penerima',
        'bulan_bayar',
        'periode_iuran_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal_dibayarkan' => 'decimal:2',
            'bukti' => 'array',
            'bulan_bayar' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->PembayaranTunai_id)) {
                $today = now();
                $prefix = 'BYR-TNI-'.$today->format('dmY').'-';
                $last = static::query()
                    ->whereDate('created_at', $today->toDateString())
                    ->where('PembayaranTunai_id', 'like', $prefix.'%')
                    ->orderBy('PembayaranTunai_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->PembayaranTunai_id)) {
                    $parts = explode('-', $last->PembayaranTunai_id);
                    $suffix = end($parts);
                    $num = (int) $suffix;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->PembayaranTunai_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->status_id)) {
                $model->status_id = \App\Models\Status::idForFitur('keuangan', 'pending')
                    ?? \App\Models\Status::idByName('pending');
            }
        });
        static::saved(function (self $model) {
            try {
                $settlementId = \App\Models\Status::idForFitur('keuangan', 'settlement') ?? $model->status_id;
                if ($model->status_id === $settlementId && $model->tagihan_iuran_id) {
                    \App\Models\TagihanIuranWarga::where('tagihan_iuran_id', $model->tagihan_iuran_id)->update([
                        'status_id' => $settlementId,
                        'tanggal_lunas' => now(),
                        'PembayaranTunai_id' => $model->PembayaranTunai_id,
                    ]);
                }
            } catch (\Throwable $e) {
            }
        });
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function tagihan()
    {
        return $this->belongsTo(TagihanIuranWarga::class, 'tagihan_iuran_id', 'tagihan_iuran_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeIuran::class, 'periode_iuran_id', 'periode_iuran_id');
    }

    public function getRouteKeyName(): string
    {
        return 'PembayaranTunai_id';
    }
}
