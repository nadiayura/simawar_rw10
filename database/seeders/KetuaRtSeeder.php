<?php

namespace Database\Seeders;

use App\Models\KetuaRt;
use App\Models\NoRt;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KetuaRtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodeMulai = '2023-01-01';
        $periodeSelesai = '2027-12-31';

        $mapping = [
            '02' => [
                'Ketua RT' => 'm.ismail',
                'Sekretaris RT' => 'Tri sumarjoko',
                'Bendahara RT' => 'Heri Agusti',
            ],
            '03' => [
                'Ketua RT' => 'saminanto',
                'Sekretaris RT' => 'kardiyo',
                'Bendahara RT' => 'Naat Sulaiman',
            ],
            '04' => [
                'Ketua RT' => 'Suwardi',
                'Sekretaris RT' => 'Indra',
                'Bendahara RT' => 'Tatang Hanapi',
            ],
            '05' => [
                'Ketua RT' => 'suawarno',
                'Sekretaris RT' => 'Rismansyah',
                'Bendahara RT' => 'Yudi',
            ],
            '06' => [
                'Ketua RT' => 'sarkum Nurhadi',
                'Sekretaris RT' => 'Ruly Anwar',
                'Bendahara RT' => 'Arif Surahman',
            ],
        ];

        foreach ($mapping as $rtNomor => $roles) {
            $rt = NoRt::query()->where('nomor', $rtNomor)->first();
            if (! $rt) {
                continue;
            }
            $rtId = $rt->no_rt_id;

            foreach ($roles as $jabatan => $nama) {
                $namaLower = Str::lower(trim((string) $nama));
                $warga = Warga::query()
                    ->where('no_rt_id', $rtId)
                    ->whereRaw('LOWER(nama) = ?', [$namaLower])
                    ->first();

                KetuaRt::updateOrCreate(
                    [
                        'no_rt_id' => $rtId,
                        'jabatan' => $jabatan,
                        'is_active' => true,
                    ],
                    [
                        'warga_nik' => $warga?->warga_nik,
                        'alamat' => $warga?->alamat,
                        'no_hp' => $warga?->no_hp,
                        'periode_mulai' => $periodeMulai,
                        'periode_selesai' => $periodeSelesai,
                    ]
                );
            }
        }
    }
}
