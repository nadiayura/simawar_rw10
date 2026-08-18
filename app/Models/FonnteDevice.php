<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonnteDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device',
        'token',
        'status',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }
}
