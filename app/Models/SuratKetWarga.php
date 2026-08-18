<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKetWarga extends Model
{
    use HasFactory;

    protected $primaryKey = 'surat_ket_warga_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'warga_nik',
        'jenis_surat_id',
        'tgl_pengajuan',
        'tgl_acara_mulai',
        'tgl_acara_selesai',
        'tgl_selesai',
        'keperluan',
        'dok_pendukung',
        'status_id',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pengajuan' => 'date',
            'tgl_acara_mulai' => 'date',
            'tgl_acara_selesai' => 'date',
            'tgl_selesai' => 'date',
            'dok_pendukung' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->surat_ket_warga_id)) {
                $baseDate = $model->tgl_pengajuan ? \Carbon\Carbon::parse($model->tgl_pengajuan) : now();
                $dateStr = $baseDate->format('dmY');
                $prefix = 'SUKET-WRG-';
                $last = static::query()
                    ->where('surat_ket_warga_id', 'like', $prefix.'%')
                    ->orderBy('surat_ket_warga_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->surat_ket_warga_id)) {
                    $parts = explode('-', $last->surat_ket_warga_id);
                    $num = isset($parts[2]) ? (int) $parts[2] : 0;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->surat_ket_warga_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
            }
            if (empty($model->status_id)) {
                $model->status_id = \App\Models\Status::idForFitur('surat', 'pending')
                    ?? \App\Models\Status::idByName('pending');
            }
        });

        static::created(function (self $model) {
            $model->loadMissing(['warga', 'jenisSurat']);

            $warga = $model->warga;

            if (! $warga) {
                return;
            }

            $rtId = $warga->no_rt_id ?? null;

            $rtRecipients = \App\Models\User::query()
                ->whereHas('role', function ($query) {
                    $query->where('level', 'rt_admin');
                })
                ->when($rtId, function ($query) use ($rtId) {
                    $query->whereHas('warga', function ($wargaQuery) use ($rtId) {
                        $wargaQuery->where('no_rt_id', $rtId);
                    });
                })
                ->get();

            $rwRecipients = \App\Models\User::query()
                ->whereHas('role', function ($query) {
                    $query->where('level', 'rw_admin');
                })
                ->get();

            $adminRecipients = \App\Models\User::query()
                ->whereHas('role', function ($query) {
                    $query->where('level', 'admin');
                })
                ->get();

            $recipients = $rtRecipients
                ->merge($rwRecipients)
                ->merge($adminRecipients)
                ->unique('id');

            if ($recipients->isEmpty()) {
                return;
            }

            $title = 'Pengajuan surat baru';

            if ($warga->nama) {
                $title .= ' dari '.$warga->nama;
            }

            $jenisSurat = $model->jenisSurat?->nama_surat;

            $body = $jenisSurat ?: $model->keperluan;

            \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-envelope')
                ->color('info');
        });

        static::saving(function (self $model) {
            if (empty($model->tgl_selesai) && $model->status_id) {
                $status = \App\Models\Status::find($model->status_id);
                if ($status && strtolower((string) $status->keterangan) === 'selesai') {
                    $model->tgl_selesai = $model->tgl_selesai ?? now();
                }
            }
        });
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
