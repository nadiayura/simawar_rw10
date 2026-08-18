<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapKeuangan extends Model
{
    use HasFactory;

    protected $table = 'rekap_keuangan';

    protected $primaryKey = 'rekap_keuangan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'rekap_keuangan_id',
        'tanggal',
        'jenis',
        'sumber',
        'nominal',
        'PembayaranMidtrans_id',
        'tagihan_iuran_id',
        'bukti',
        'metode',
        'keterangan',
        'no_rt_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'bukti' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->rekap_keuangan_id)) {
                $date = $model->tanggal ? \Carbon\Carbon::parse($model->tanggal) : now();
                $month = $date->format('m');
                $year = $date->format('Y');
                $prefix = 'RKP-'.$month.$year.'-';

                $lastRecord = self::query()->where('rekap_keuangan_id', 'like', $prefix.'%')->orderBy('rekap_keuangan_id', 'desc')->first();

                $sequence = 1;
                if ($lastRecord) {
                    $lastId = $lastRecord->rekap_keuangan_id;
                    $lastSequence = (int) substr($lastId, -3);
                    $sequence = $lastSequence + 1;
                }

                $model->rekap_keuangan_id = $prefix.str_pad($sequence, 3, '0', STR_PAD_LEFT);
            }
        });

    }

    public function getJenisAttribute(): ?string
    {
        return $this->attributes['jenis_trans'] ?? null;
    }

    public function setJenisAttribute($value): void
    {
        $this->attributes['jenis_trans'] = $value;
    }

    public function getPaymentTransactionIdAttribute(): ?string
    {
        return $this->attributes['PembayaranMidtrans_id'] ?? null;
    }

    public function setPaymentTransactionIdAttribute($value): void
    {
        $this->attributes['PembayaranMidtrans_id'] = $value;
    }

    public function getPembayaranMidtransIdAttribute(): ?string
    {
        return $this->attributes['PembayaranMidtrans_id'] ?? null;
    }

    public function setPembayaranMidtransIdAttribute($value): void
    {
        $this->attributes['PembayaranMidtrans_id'] = $value;
    }

    public function tagihan()
    {
        return $this->belongsTo(\App\Models\TagihanIuranWarga::class, 'tagihan_iuran_id', 'tagihan_iuran_id');
    }

    public function noRt()
    {
        return $this->belongsTo(\App\Models\NoRt::class, 'no_rt_id', 'no_rt_id');
    }
}
