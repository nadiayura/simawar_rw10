<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegKarangTaruna extends Model
{
    protected $table = 'keg_karang_taruna';

    protected $primaryKey = 'keg_karang_taruna_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal',
        'penanggung_jawab',
        'status_id',
        'dokumentasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'dokumentasi' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->keg_karang_taruna_id)) {
                $baseDate = $model->tanggal ? \Carbon\Carbon::parse($model->tanggal) : now();
                $dateStr = $baseDate->format('dmY');
                $prefix = 'KRGTRN-';
                $last = static::query()
                    ->where('keg_karang_taruna_id', 'like', $prefix.'%')
                    ->orderBy('keg_karang_taruna_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->keg_karang_taruna_id)) {
                    $parts = explode('-', $last->keg_karang_taruna_id);
                    $num = isset($parts[1]) ? (int) $parts[1] : 0;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->keg_karang_taruna_id = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT).'-'.$dateStr;
            }
            if (empty($model->status_id)) {
                $default = \App\Models\Status::idForFitur('keg_warga', 'Terjadwal')
                    ?? \App\Models\Status::idByName('Terjadwal');
                $model->status_id = $default;
            }
        });

        static::saving(function ($model) {
            if (is_array($model->dokumentasi)) {
                $model->dokumentasi = array_values(array_filter($model->dokumentasi));
            }
        });

        static::created(function ($model) {
            $model->loadMissing('pjWarga');
            $warga = $model->pjWarga;

            $user = \Illuminate\Support\Facades\Auth::user();

            $rtId = null;

            if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
                $rtId = $user->warga->no_rt_id;
            } elseif ($warga && $warga->no_rt_id) {
                $rtId = $warga->no_rt_id;
            }

            if (! $rtId) {
                return;
            }
            $recipients = \App\Models\User::query()
                ->whereHas('role', function ($query) {
                    $query->where('level', 'basic');
                })
                ->whereHas('warga', function ($query) use ($rtId) {
                    $query->where('no_rt_id', $rtId);
                })
                ->get();
            if ($recipients->isEmpty()) {
                return;
            }
            $title = 'Kegiatan Karang Taruna baru';
            $body = $model->nama_kegiatan ?: null;
            if ($model->tanggal) {
                $body = trim(($body ? $body.' • ' : '').'Tanggal: '.optional($model->tanggal)->translatedFormat('d F Y, H.i'));
            }
            \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body ?: $model->deskripsi)
                ->icon('heroicon-o-calendar')
                ->color('primary');
        });
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\Status::class, 'status_id', 'status_id');
    }

    public function getFotoUtamaAttribute(): ?string
    {
        $docs = $this->dokumentasi ?? [];
        if (is_string($docs)) {
            $decoded = json_decode($docs, true);
            $docs = is_array($decoded) ? $decoded : ($docs ? [$docs] : []);
        }

        return $docs[0] ?? null;
    }

    public function getFotoKegiatanAttribute(): ?string
    {
        return $this->foto_utama;
    }

    public function getJenisKegiatanLabelAttribute(): string
    {
        return 'Karang Taruna';
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString());
    }

    public function scopePast($query)
    {
        return $query->where('tanggal', '<', now()->toDateString());
    }

    public function pjWarga()
    {
        return $this->belongsTo(Warga::class, 'penanggung_jawab', 'warga_nik');
    }
}
