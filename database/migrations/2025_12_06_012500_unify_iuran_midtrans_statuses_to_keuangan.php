<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('statuses')
            ->whereIn('fitur', ['iuran', 'midtrans'])
            ->update(['fitur' => 'keuangan']);
    }

    public function down(): void
    {
        $midtransNames = [
            'pending', 'settlement', 'deny', 'cancel', 'expire', 'refund', 'capture', 'chargeback',
        ];
        $iuranNames = [
            'Belum bayar', 'Menunggu pembayaran', 'Lunas', 'Kedaluwarsa',
        ];

        DB::table('statuses')
            ->whereIn(DB::raw('LOWER(keterangan)'), array_map('strtolower', $midtransNames))
            ->update(['fitur' => 'midtrans']);

        DB::table('statuses')
            ->whereIn('keterangan', $iuranNames)
            ->update(['fitur' => 'iuran']);
    }
};
