<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegKesehatan extends Model
{
    protected $primaryKey = 'keg_kesehatan_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'keg_kesehatans';

    protected $fillable = [
        'jenis_kegiatan',
        'nama_kegiatan',
        'tgl',
        'penanggung_jawab',
        'jumlah_peserta',
        'rincian_peserta',
        'aktivitas_dilakukan',
        'hasil_pelaksanaan',
        'dokumentasi',
        'status_id',
    ];

    protected $casts = [
        'tgl' => 'date',
        'jumlah_peserta' => 'integer',
        'rincian_peserta' => 'array',
        'aktivitas_dilakukan' => 'string',
        'dokumentasi' => 'array',
    ];

    public const JENIS_KEGIATAN = [
        'posyandu' => 'Posyandu',
        'posbindu' => 'Posbindu',
    ];

    public const AKTIVITAS_POSYANDU = [
        'balita_ibu_hamil' => 'Pemeriksaan Balita & Ibu Hamil',
        'imunisasi' => 'Imunisasi',
        'pemberian_vitamin' => 'Pemberian Vitamin',
        'penyuluhan_kesehatan' => 'Penyuluhan Kesehatan',
    ];

    public const AKTIVITAS_POSBINDU = [
        'pemeriksaan_tekanan_darah_berat_badan' => 'Pemeriksaan Tekanan Darah & Berat Badan',
        'cek_tht_kesehatan_paru' => 'Cek THT & Kesehatan Paru',
        'edukasi_gaya_hidup_sehat' => 'Edukasi Gaya Hidup Sehat',
    ];

    public static function getJenisKegiatanOptions(): array
    {
        return self::JENIS_KEGIATAN;
    }

    public static function getStatusKegiatanOptions(): array
    {
        return [
            'Terjadwal' => 'Terjadwal',
            'Selesai' => 'Selesai',
            'Dibatalkan' => 'Dibatalkan',
        ];
    }

    public static function getAktivitasOptions(string $jenisKegiatan): array
    {
        return match ($jenisKegiatan) {
            'posyandu' => self::AKTIVITAS_POSYANDU,
            'posbindu' => self::AKTIVITAS_POSBINDU,
            default => [],
        };
    }

    public static function getDefaultRincianPeserta(): array
    {
        return [
            'anak' => 0,
            'bayi' => 0,
            'ibu_hamil' => 0,
            'lansia' => 0,
        ];
    }

    public function setRincianPesertaAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['rincian_peserta'] = json_encode($value);
        } else {
            $this->attributes['rincian_peserta'] = $value;
        }
    }

    public function getRincianPesertaAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return $decoded ?: self::getDefaultRincianPeserta();
        }

        return $value ?: self::getDefaultRincianPeserta();
    }

    public function setDokumentasiAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['dokumentasi'] = json_encode($value);
        } else {
            $this->attributes['dokumentasi'] = $value;
        }
    }

    public function getDokumentasiAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return $decoded ?: [];
        }

        return $value ?: [];
    }


    public function scopeByStatus($query, $status)
    {
        return $query->where('status_id', $status);
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_kegiatan', $jenis);
    }

    public function scopeByTanggal($query, $tanggal)
    {
        return $query->whereDate('tgl', $tanggal);
    }


    public function scopeByBulanTahun($query, $bulan, $tahun)
    {
        return $query->whereMonth('tgl', $bulan)->whereYear('tgl', $tahun);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->keg_kesehatan_id)) {
                $baseDate = $model->tgl ? \Carbon\Carbon::parse($model->tgl) : now();
                $dateStr = $baseDate->format('dmY');
                $prefix = 'KSHTN-';
                $last = static::query()
                    ->where('keg_kesehatan_id', 'like', $prefix.'%')
                    ->orderBy('created_at', 'desc')
                    ->first();
                $seq = 1;
                if ($last && is_string($last->keg_kesehatan_id)) {
                    $parts = explode('-', $last->keg_kesehatan_id);
                    $num = isset($parts[2]) ? (int) $parts[2] : 0;
                    if ($num > 0) {
                        $seq = $num + 1;
                    }
                }
                $model->keg_kesehatan_id = $prefix.$dateStr.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->status_id)) {
                $default = \App\Models\Status::idForFitur('keg_warga', 'Dijadwalkan')
                    ?? \App\Models\Status::idByName('Dijadwalkan');
                $model->status_id = $default;
            }
        });
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
