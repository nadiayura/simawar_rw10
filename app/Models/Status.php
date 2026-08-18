<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    public $timestamps = false;

    protected $primaryKey = 'status_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'keterangan',
        'fitur',
    ];

    public static function idByName(string $name): ?string
    {
        return static::where('keterangan', $name)->value('status_id');
    }

    public static function idForFitur(string $fitur, string $name): ?string
    {
        return static::where('fitur', $fitur)
            ->whereRaw('LOWER(keterangan) = ?', [strtolower($name)])
            ->value('status_id');
    }

    public static function nonLunasIds(): array
    {
        return static::where('fitur', 'keuangan')
            ->whereRaw('LOWER(keterangan) <> ?', ['lunas'])
            ->pluck('status_id')
            ->all();
    }
}
