<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanIuranWarga extends Model
{
    use HasFactory;

    protected $table = 'tagihan_iuran_wargas';

    protected $primaryKey = 'tagihan_iuran_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tagihan_iuran_id',
        'warga_nik',
        'iuran_id',
        'periode_iuran_id',
        'nominal_tagihan',
        'status_id',
        'tanggal_lunas',
        'PembayaranMidtrans_id',
        'PembayaranTunai_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal_tagihan' => 'decimal:2',
            'tanggal_lunas' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (! $model->iuran_id) {
                return;
            }

            $nominalEmpty = $model->nominal_tagihan === null || $model->nominal_tagihan === '';
            $nominalZero = ! $nominalEmpty && (float) $model->nominal_tagihan === 0.0;
            $shouldSyncFromIuran = $nominalEmpty
                || $nominalZero
                || ($model->exists && $model->isDirty('iuran_id') && ! $model->isDirty('nominal_tagihan'));

            if (! $shouldSyncFromIuran) {
                return;
            }

            $iuran = Iuran::query()->whereKey($model->iuran_id)->first();
            if ($iuran) {
                $model->nominal_tagihan = $iuran->jumlah_default;
            }
        });

        static::creating(function (self $model) {
            if (empty($model->tagihan_iuran_id)) {
                $per = \App\Models\PeriodeIuran::find($model->periode_iuran_id);
                $mm = $per ? str_pad((string) ((int) $per->bulan), 2, '0', STR_PAD_LEFT) : date('m');
                $yyyy = $per ? (string) ((int) $per->tahun) : date('Y');
                $dd = date('d');
                $base = 'TG-'.$dd.$mm.$yyyy.'-';

                $last = static::query()
                    ->where('tagihan_iuran_id', 'like', $base.'%')
                    ->orderBy('tagihan_iuran_id', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->tagihan_iuran_id)) {
                    $id = $last->tagihan_iuran_id;
                    if (str_starts_with($id, $base)) {
                        $suffix = substr($id, strlen($base));
                        $num = (int) $suffix;
                        if ($num > 0) {
                            $seq = $num + 1;
                        }
                    }
                }
                $model->tagihan_iuran_id = $base.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->status_id)) {
                $model->status_id = Status::idForFitur('keuangan', 'Belum bayar');
            }
        });

        static::updated(function (self $model) {
            if (! $model->wasChanged('tanggal_lunas') || ! $model->tanggal_lunas) {
                return;
            }

            $model->loadMissing('warga');

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

            $title = 'Pembayaran iuran lunas';

            if ($warga->nama) {
                $title .= ' oleh '.$warga->nama;
            }

            $body = 'Tagihan iuran telah dibayar.';

            \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-banknotes')
                ->color('success');

            $actor = request()->user();
            $isWargaPanel = request()->is('warga/*');
            $isWargaRole = $actor && $actor->role && method_exists($actor->role, 'isWarga') && $actor->role->isWarga();
            if (! ($isWargaPanel || $isWargaRole)) {
                return;
            }

            $ketua = \App\Models\KetuaRt::query()->ketuaRt()->active()->where('no_rt_id', $rtId)->first();
            $hp = $ketua?->no_hp;
            if (! $hp) {
                $hp = \App\Models\KetuaRt::query()
                    ->ketuaRt()
                    ->where('no_rt_id', $rtId)
                    ->orderByDesc('periode_mulai')
                    ->value('no_hp');
            }
            if (! $hp) {
                \Illuminate\Support\Facades\Log::warning('Iuran WA notify aborted: Ketua RT no_hp missing', ['rt_id' => $rtId, 'tagihan' => $model->getKey()]);

                return;
            }
            $deviceToken = \App\Models\FonnteDevice::query()
                ->whereIn('status', ['connected', 'connect'])
                ->orderByDesc('last_synced_at')
                ->value('token');
            if (! $deviceToken) {
                $deviceToken = \App\Models\FonnteDevice::query()
                    ->whereIn('status', ['connected', 'connect'])
                    ->latest('updated_at')
                    ->value('token');
            }
            if (! $deviceToken) {
                \Illuminate\Support\Facades\Log::warning('Iuran WA notify aborted: no connected device', ['tagihan' => $model->getKey()]);

                return;
            }
            $digits = preg_replace('/\D+/', '', (string) $hp);
            if ($digits === null || $digits === '') {
                \Illuminate\Support\Facades\Log::warning('Iuran WA notify aborted: invalid phone', ['hp' => $hp, 'tagihan' => $model->getKey()]);

                return;
            }
            if (str_starts_with($digits, '0')) {
                $digits = '62'.substr($digits, 1);
            } elseif (str_starts_with($digits, '8')) {
                $digits = '62'.$digits;
            }
            $tanggal = $model->tanggal_lunas ? $model->tanggal_lunas->format('d-m-Y') : now()->format('d-m-Y');
            $nominal = $model->nominal_tagihan;
            $model->loadMissing('periode');
            $periodeLabel = $model->periode
                ? trim(($model->periode->nama_bulan ?? (string) $model->periode->bulan).' '.$model->periode->tahun)
                : null;
            $rtNomor = $rtId ? (\App\Models\NoRt::find($rtId)?->nomor ?? $rtId) : null;
            $message =
                '✅ *Pembayaran iuran warga terbaru*'."
                \n".
                'Halo Ketua RT '.($rtNomor ?: '').', terdapat pembayaran iuran baru oleh :'."\n\n".
                ($warga?->nama ? ' Nama : '.$warga->nama."\n" : '').
                ($rtNomor ? ' RT : '.$rtNomor."\n" : '').
                ($periodeLabel ? ' Pembayaran Bulan : '.$periodeLabel."\n" : '').
                ($nominal ? 'Nominal : Rp '.number_format((float) $nominal, 0, ',', '.') : '').
                "\n\n".'Silakan cek aplikasi untuk detail.';
            $resp = app(\App\Services\FonnteService::class)->sendWhatsAppMessage($digits, $message, $deviceToken);
            if (! ($resp['status'] ?? false) || (isset($resp['data']['status']) && ! $resp['data']['status'])) {
                $err = $resp['error'] ?? ($resp['data']['reason'] ?? 'Error');
                \Illuminate\Support\Facades\Log::error('Iuran WA notify failed', ['id' => $model->getKey(), 'reason' => $err]);
            } else {
                \Illuminate\Support\Facades\Log::info('Iuran WA notify sent', ['id' => $model->getKey(), 'target' => $digits]);
            }
        });
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeIuran::class, 'periode_iuran_id');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_nik');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function pembayaranMidtrans()
    {
        return $this->belongsTo(PembayaranMidtrans::class, 'PembayaranMidtrans_id', 'PembayaranMidtrans_id');
    }

    public function pembayaranTunai()
    {
        return $this->belongsTo(PembayaranTunai::class, 'PembayaranTunai_id', 'PembayaranTunai_id');
    }

    public function getRouteKeyName(): string
    {
        return 'tagihan_iuran_id';
    }
}
