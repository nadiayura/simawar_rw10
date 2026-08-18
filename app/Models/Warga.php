<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Warga extends Model
{
    use HasFactory;

    protected $primaryKey = 'warga_nik';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'warga_nik',
        'nama',
        'jenis_kelamin',
        'agama',
        'status_tinggal',
        'alamat',
        'rw',
        'no_hp',
        'email',
        'iuran_id',
        'no_rt_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::updated(function ($warga) {
            $user = User::where('warga_nik', $warga->warga_nik)->first();

            if ($user) {
                $user->update([
                    'name' => $warga->nama,
                    'email' => $warga->email ?? $user->email, // Keep existing email if new one is empty
                ]);
            }
        });
    }

    public function ketuaRt()
    {
        return $this->hasOne(KetuaRt::class, 'warga_nik');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'warga_nik');
    }

    public function tagihanIuranWarga()
    {
        return $this->hasMany(TagihanIuranWarga::class, 'warga_nik');
    }

    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'warga_nik');
    }

    public function iuran()
    {
        return $this->belongsTo(Iuran::class, 'iuran_id');
    }

    public function getRouteKeyName(): string
    {
        return 'warga_nik';
    }

    public static function maskedNik(?string $wargaNik): ?string
    {
        if ($wargaNik === null || $wargaNik === '') {
            return $wargaNik;
        }

        $prefix = 'WRG-';
        $hasPrefix = substr((string) $wargaNik, 0, strlen($prefix)) === $prefix;
        $raw = $hasPrefix ? substr((string) $wargaNik, strlen($prefix)) : (string) $wargaNik;
        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return $wargaNik;
        }

        $length = strlen($digits);

        if ($length <= 4) {
            return ($hasPrefix ? $prefix : '').$digits;
        }

        $first = substr($digits, 0, 2);
        $last = substr($digits, -2);
        $maskedCount = max(0, $length - 4);

        return ($hasPrefix ? $prefix : '').$first.str_repeat('*', $maskedCount).$last;
    }

    public function createUserAccount(?string $password = null): ?User
    {
        $existing = User::query()->where('warga_nik', $this->warga_nik)->first();
        if ($existing) {
            return $existing;
        }

        $emailNorm = $this->email ? strtolower($this->email) : null;
        if ($emailNorm && User::query()->where('email', $emailNorm)->exists()) {
            $emailNorm = null;
        }

        $finalEmail = $emailNorm ?: Str::of($this->warga_nik)->lower()->append('@simawar.local')->toString();
        if (User::query()->where('email', $finalEmail)->exists()) {
            $finalEmail = Str::of($this->warga_nik)->lower()->append('+user@simawar.local')->toString();
        }

        $pwd = $password ?: 'password123';

        return User::create([
            'name' => $this->nama,
            'email' => $finalEmail,
            'password' => Hash::make($pwd),
            'warga_nik' => $this->warga_nik,
        ]);
    }
}
