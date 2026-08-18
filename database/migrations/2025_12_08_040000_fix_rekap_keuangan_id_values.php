<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nonaktifkan pemeriksaan foreign key sementara untuk menghindari masalah referensi
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $records = DB::table('rekap_keuangan')->orderBy('tanggal')->orderBy('created_at')->get();

        foreach ($records as $record) {
            $date = $record->tanggal ? Carbon::parse($record->tanggal) : Carbon::now();
            $month = $date->format('m');
            $year = $date->format('Y');
            $prefix = 'RKP-'.$month.$year.'-';

            // Cari sequence terakhir untuk prefix ini
            // Kita tidak bisa menggunakan query DB biasa karena kita sedang mengubah data secara massal
            // Jadi kita hitung manual atau cari yang sudah ada di DB yang *sudah* diupdate (tapi ini tricky)
            // Lebih aman kita hitung sequence berdasarkan data yang sedang kita proses dalam loop ini
            // Tapi karena kita ingin backfill, kita perlu tahu urutan per bulan.

            // Cara yang lebih baik: Group by month/year dulu
        }

        // Pendekatan yang lebih baik:
        // Ambil semua record, kelompokkan berdasarkan bulan-tahun, lalu update sequence

        $grouped = $records->groupBy(function ($item) {
            $date = $item->tanggal ? Carbon::parse($item->tanggal) : Carbon::now();

            return $date->format('mY');
        });

        foreach ($grouped as $monthYear => $items) {
            $month = substr($monthYear, 0, 2);
            $year = substr($monthYear, 2, 4);
            $prefix = 'RKP-'.$month.$year.'-';

            $sequence = 1;
            foreach ($items as $item) {
                $newId = $prefix.str_pad($sequence, 3, '0', STR_PAD_LEFT);

                DB::table('rekap_keuangan')
                    ->where('rekap_keuangan_id', $item->rekap_keuangan_id) // Gunakan ID lama (TEMP-...) sebagai referensi update
                    ->update(['rekap_keuangan_id' => $newId]);

                $sequence++;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke format TEMP-X jika perlu rollback (opsional, tapi sebaiknya ada)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $records = DB::table('rekap_keuangan')->orderBy('tanggal')->get();
        $i = 1;
        foreach ($records as $record) {
            DB::table('rekap_keuangan')
                ->where('rekap_keuangan_id', $record->rekap_keuangan_id)
                ->update(['rekap_keuangan_id' => 'TEMP-'.$i]);
            $i++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
