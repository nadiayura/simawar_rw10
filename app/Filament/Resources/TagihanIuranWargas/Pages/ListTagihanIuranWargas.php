<?php

namespace App\Filament\Resources\TagihanIuranWargas\Pages;

use App\Filament\Resources\TagihanIuranWargas\TagihanIuranWargaResource;
use App\Models\Iuran;
use App\Models\PeriodeIuran;
use App\Models\Status;
use App\Models\TagihanIuranWarga;
use App\Models\Warga;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListTagihanIuranWargas extends ListRecords
{
    protected static string $resource = TagihanIuranWargaResource::class;

    protected static ?string $title = 'Tagihan Iuran Warga';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generate_tagihan')
                ->label('Generate Tagihan Otomatis')
                ->form([
                    Toggle::make('semua_warga')
                        ->label('Semua Warga')
                        ->inline(false),
                    Select::make('warga_nik')
                        ->label('Warga')
                        ->multiple()
                        ->searchable()
                        ->hidden(fn (Get $get) => (bool) $get('semua_warga') === true)
                        ->required(fn (Get $get) => (bool) $get('semua_warga') !== true)
                        ->options(function () {
                            $query = Warga::query();

                            $user = Auth::user();
                            if ($user && $user->warga && $user->warga->no_rt_id) {
                                $query->where('no_rt_id', $user->warga->no_rt_id);
                            }

                            $options = $query
                                ->orderBy('nama')
                                ->get()
                                ->mapWithKeys(function (Warga $warga) {
                                    return [
                                        $warga->warga_nik => $warga->nama.' - '.\App\Models\Warga::maskedNik($warga->warga_nik),
                                    ];
                                })
                                ->toArray();

                            return $options;
                        }),
                    Select::make('iuran_id')
                        ->label('Iuran')
                        ->options(function () {
                            return Iuran::query()
                                ->orderBy('nama_iuran')
                                ->get()
                                ->mapWithKeys(function (Iuran $iuran) {
                                    return [
                                        $iuran->iuran_id => $iuran->nama_iuran.' - '.$iuran->jumlah_default,
                                    ];
                                })
                                ->toArray();
                        })
                        ->required(),
                    Select::make('periode_iuran_id')
                        ->label('Periode Iuran')
                        ->multiple()
                        ->searchable()
                        ->options(function () {
                            $map = [
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ];

                            return PeriodeIuran::query()
                                ->orderBy('tahun')
                                ->orderBy('bulan')
                                ->get()
                                ->mapWithKeys(function (PeriodeIuran $periode) use ($map) {
                                    $label = ($map[(int) $periode->bulan] ?? $periode->bulan).' '.$periode->tahun;

                                    return [
                                        $periode->periode_iuran_id => $label,
                                    ];
                                })
                                ->toArray();
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    DB::beginTransaction();
                    try {
                        $wargaNiks = (array) ($data['warga_nik'] ?? []);
                        $iuranId = $data['iuran_id'] ?? null;
                        $periodeIds = (array) ($data['periode_iuran_id'] ?? []);

                        if (($data['semua_warga'] ?? false) === true) {
                            $query = Warga::query();
                            $user = Auth::user();
                            if ($user && $user->warga && $user->warga->no_rt_id) {
                                $query->where('no_rt_id', $user->warga->no_rt_id);
                            }
                            $wargaNiks = $query->pluck('warga_nik')->all();
                        }

                        if (empty($wargaNiks) || empty($iuranId) || empty($periodeIds)) {
                            Notification::make()
                                ->title('Data belum lengkap')
                                ->body('Silakan pilih warga (atau centang Semua Warga), iuran, dan periode iuran.')
                                ->danger()
                                ->send();
                            DB::rollBack();

                            return;
                        }

                        $created = 0;
                        $existingPairs = TagihanIuranWarga::query()
                            ->whereIn('warga_nik', $wargaNiks)
                            ->where('iuran_id', $iuranId)
                            ->whereIn('periode_iuran_id', $periodeIds)
                            ->get(['warga_nik', 'periode_iuran_id'])
                            ->mapWithKeys(fn ($row) => [((string) $row->warga_nik).'|'.((string) $row->periode_iuran_id) => true])
                            ->all();

                        $periodeRecords = PeriodeIuran::query()
                            ->whereIn('periode_iuran_id', $periodeIds)
                            ->get()
                            ->keyBy('periode_iuran_id');

                        $iuranNominal = Iuran::query()->whereKey($iuranId)->value('jumlah_default');
                        $statusId = Status::idForFitur('keuangan', 'Belum bayar');
                        $now = now();
                        $day = $now->format('d');

                        $prefixSeq = [];
                        foreach ($periodeIds as $periodeId) {
                            $per = $periodeRecords->get($periodeId);
                            if (! $per) {
                                continue;
                            }
                            $mm = str_pad((string) ((int) $per->bulan), 2, '0', STR_PAD_LEFT);
                            $yyyy = (string) ((int) $per->tahun);
                            $base = 'TG-'.$day.$mm.$yyyy.'-';

                            $last = TagihanIuranWarga::query()
                                ->where('tagihan_iuran_id', 'like', $base.'%')
                                ->orderByDesc('tagihan_iuran_id')
                                ->lockForUpdate()
                                ->value('tagihan_iuran_id');

                            $seq = 1;
                            if (is_string($last) && str_starts_with($last, $base)) {
                                $suffix = substr($last, strlen($base));
                                $num = (int) $suffix;
                                if ($num > 0) {
                                    $seq = $num + 1;
                                }
                            }

                            $prefixSeq[$base] = $seq;
                        }

                        $rows = [];
                        foreach ($wargaNiks as $wargaNik) {
                            foreach ($periodeIds as $periodeId) {
                                $key = ((string) $wargaNik).'|'.((string) $periodeId);
                                if (isset($existingPairs[$key])) {
                                    continue;
                                }
                                $per = $periodeRecords->get($periodeId);
                                if (! $per) {
                                    continue;
                                }
                                $mm = str_pad((string) ((int) $per->bulan), 2, '0', STR_PAD_LEFT);
                                $yyyy = (string) ((int) $per->tahun);
                                $base = 'TG-'.$day.$mm.$yyyy.'-';
                                $seq = $prefixSeq[$base] ?? 1;
                                $prefixSeq[$base] = $seq + 1;

                                $rows[] = [
                                    'tagihan_iuran_id' => $base.str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
                                    'warga_nik' => $wargaNik,
                                    'iuran_id' => $iuranId,
                                    'periode_iuran_id' => $periodeId,
                                    'nominal_tagihan' => $iuranNominal,
                                    'status_id' => $statusId,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                                $created++;
                            }
                        }

                        if (! empty($rows)) {
                            foreach (array_chunk($rows, 500) as $chunk) {
                                DB::table((new TagihanIuranWarga)->getTable())->insert($chunk);
                            }
                        }
                        DB::commit();
                        if ($created > 0) {
                            Notification::make()
                                ->title('Generate tagihan berhasil')
                                ->body('Jumlah dibuat: '.$created)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak ada tagihan baru yang dibuat')
                                ->body('Semua tagihan yang dipilih sudah tersedia.')
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()->title('Gagal generate tagihan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
