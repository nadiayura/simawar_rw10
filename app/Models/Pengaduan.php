<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';

    protected $primaryKey = 'pengaduan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tgl_pengajuan',
        'warga_nik',
        'jenis_pengaduan_id',
        'jdl_pengaduan',
        'status_id',
        'detail_pengaduan',
        'bukti',
        'solusi_admin',
    ];

    protected $casts = [
        'tgl_pengajuan' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->status_id)) {
                $model->status_id = \App\Models\Status::idForFitur('pengaduan', 'pending')
                    ?? \App\Models\Status::idByName('pending');
            }
        });

        static::created(function (self $model) {
            $model->loadMissing('warga');

            $warga = $model->warga;

            if (! $warga) {
                return;
            }

            $rtId = $warga->no_rt_id;

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

            $title = 'Pengaduan baru';

            if ($warga->nama) {
                $title .= ' dari '.$warga->nama;
            }

            $body = $model->jdl_pengaduan ?: $model->detail_pengaduan;

            \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-megaphone')
                ->color('warning');
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->pengaduan_id)) {
                $baseDate = $model->tgl_pengajuan ? \Carbon\Carbon::parse($model->tgl_pengajuan) : now();
                $dateStr = $baseDate->format('dmY');
                $prefix = 'ADU-WRG-';
                $last = static::query()
                    ->where('pengaduan_id', 'like', $prefix.'%')
                    ->orderBy('pengaduan_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->pengaduan_id)) {
                    $parts = explode('-', $last->pengaduan_id);
                    $num = isset($parts[2]) ? (int) $parts[2] : 0;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->pengaduan_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
            }
        });
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function jenisPengaduan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\JenisPengaduan::class, 'jenis_pengaduan_id');
    }

    public function getJenisPengaduanLabelAttribute(): string
    {
        if ($this->jenisPengaduan) {
            return (string) $this->jenisPengaduan->nama;
        }

        return ucfirst((string) ($this->getOriginal('jenis_pengaduan') ?? ''));
    }
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status?->keterangan) {
            'pending' => 'Menunggu',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => $this->status?->keterangan ?? ''
        };
    }

    public function scopeByStatus($query, $status)
    {
        return $query->whereHas('status', function ($q) use ($status) {
            $q->where('keterangan', $status);
        });
    }

    public function scopeByJenis($query, $jenis)
    {
        if (is_numeric($jenis)) {
            return $query->where('jenis_pengaduan_id', (int) $jenis);
        }

        return $query->whereHas('jenisPengaduan', function ($q) use ($jenis) {
            $q->whereRaw('LOWER(nama) = ?', [strtolower((string) $jenis)]);
        });
    }

    public function scopeByRt($query, $noRt)
    {
        return $query->whereHas('warga', function ($q) use ($noRt) {
            $q->where('no_rt', $noRt);
        });
    }
}
