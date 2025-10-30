<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegKesehatan extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'keg_kesehatans';

    /**
     * The attributes that are mass assignable.
     */
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
        'status_kegiatan',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tgl' => 'date',
        'jumlah_peserta' => 'integer',
        'status_kegiatan' => 'string',
        'rincian_peserta' => 'array',
        'aktivitas_dilakukan' => 'string',
        'dokumentasi' => 'array',
    ];

    /**
     * Konstanta untuk jenis kegiatan.
     */
    public const JENIS_KEGIATAN = [
        'posyandu' => 'Posyandu',
        'posmaja' => 'Posmaja',
    ];

    /**
     * Konstanta untuk aktivitas Posyandu (pink).
     */
    public const AKTIVITAS_POSYANDU = [
        'pemeriksaan_balita_ibu_hamil' => 'Pemeriksaan Balita & Ibu Hamil',
        'imunisasi' => 'Imunisasi',
        'pemberian_vitamin' => 'Pemberian Vitamin',
        'penyuluhan_kesehatan' => 'Penyuluhan Kesehatan',
    ];

    /**
     * Konstanta untuk aktivitas Posmaja (amber).
     */
    public const AKTIVITAS_POSMAJA = [
        'pemeriksaan_tekanan_darah_berat_badan' => 'Pemeriksaan Tekanan Darah & Berat Badan',
        'cek_tht_kesehatan_paru' => 'Cek THT & Kesehatan Paru',
        'penyuluhan_gaya_hidup_sehat' => 'Penyuluhan Gaya Hidup Sehat',
    ];

    /**
     * Get the possible values for jenis_kegiatan.
     */
    public static function getJenisKegiatanOptions(): array
    {
        return self::JENIS_KEGIATAN;
    }

    /**
     * Get the possible values for status_kegiatan.
     */
    public static function getStatusKegiatanOptions(): array
    {
        return [
            'Terjadwal' => 'Terjadwal',
            'Selesai' => 'Selesai',
            'Dibatalkan' => 'Dibatalkan',
        ];
    }

    /**
     * Get aktivitas options based on jenis kegiatan.
     */
    public static function getAktivitasOptions(string $jenisKegiatan): array
    {
        return match ($jenisKegiatan) {
            'posyandu' => self::AKTIVITAS_POSYANDU,
            'posmaja' => self::AKTIVITAS_POSMAJA,
            default => [],
        };
    }

    /**
     * Get default rincian peserta structure.
     */
    public static function getDefaultRincianPeserta(): array
    {
        return [
            'anak' => 0,
            'bayi' => 0,
            'ibu_hamil' => 0,
            'remaja' => 0,
        ];
    }

    /**
     * Mutator untuk rincian_peserta.
     */
    public function setRincianPesertaAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['rincian_peserta'] = json_encode($value);
        } else {
            $this->attributes['rincian_peserta'] = $value;
        }
    }

    /**
     * Accessor untuk rincian_peserta.
     */
    public function getRincianPesertaAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?: self::getDefaultRincianPeserta();
        }
        return $value ?: self::getDefaultRincianPeserta();
    }

    /**
     * Mutator untuk dokumentasi.
     */
    public function setDokumentasiAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['dokumentasi'] = json_encode($value);
        } else {
            $this->attributes['dokumentasi'] = $value;
        }
    }

    /**
     * Accessor untuk dokumentasi.
     */
    public function getDokumentasiAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?: [];
        }
        return $value ?: [];
    }

    /**
     * Scope untuk filter berdasarkan status kegiatan.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_kegiatan', $status);
    }

    /**
     * Scope untuk filter berdasarkan jenis kegiatan.
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_kegiatan', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan tanggal.
     */
    public function scopeByTanggal($query, $tanggal)
    {
        return $query->whereDate('tgl', $tanggal);
    }

    /**
     * Scope untuk filter berdasarkan bulan dan tahun.
     */
    public function scopeByBulanTahun($query, $bulan, $tahun)
    {
        return $query->whereMonth('tgl', $bulan)->whereYear('tgl', $tahun);
    }

    // Relationship with Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
