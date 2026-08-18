<?php

namespace App\Filament\Resources\Wargas\Pages;

use App\Filament\Resources\Wargas\WargaResource;
use App\Models\FonnteDevice;
use App\Models\Iuran;
use App\Models\Warga;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewWarga extends ViewRecord
{
    protected static string $resource = WargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifikasi')
                ->label('Verifikasi')
                ->color('success')
                ->icon('heroicon-o-check')
                ->form([
                    Select::make('iuran_id')
                        ->label('Jenis Iuran')
                        ->options(function () {
                            return Iuran::query()
                                ->get()
                                ->mapWithKeys(function ($iuran) {
                                    return [$iuran->iuran_id => $iuran->nama_iuran.' - '.$iuran->jumlah_default];
                                });
                        })
                        ->placeholder('Pilih jenis iuran')
                        ->searchable()
                        ->native(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    DB::beginTransaction();
                    try {
                        $record = $this->record;
                        $record->iuran_id = $data['iuran_id'] ?? $record->iuran_id;
                        $record->save();

                        $user = $record->user;
                        if ($user) {
                            $user->role_id = 1;
                            $user->save();
                        }

                        Notification::make()
                            ->title('Warga berhasil diverifikasi')
                            ->body('Jenis iuran: '.(Iuran::find($record->iuran_id)?->nama_iuran ?? '-'))
                            ->success()
                            ->send();
                        DB::commit();

                        try {
                            $this->sendWargaVerifiedWhatsApp($record);
                        } catch (\Throwable $e) {
                            Log::error('Gagal kirim WhatsApp verifikasi warga (view)', [
                                'warga_nik' => $record->getKey(),
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Terjadi kesalahan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('tolak')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->record;
                    $user = $record->user;
                    if ($user) {
                        $user->delete();
                    }
                    Notification::make()
                        ->title('Verifikasi warga ditolak')
                        ->warning()
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Data Warga')
                    ->schema([
                        TextEntry::make('warga_nik')
                            ->label('NIK')
                            ->formatStateUsing(fn ($state) => Warga::maskedNik($state)),
                        TextEntry::make('nama')->label('Nama'),
                        TextEntry::make('jenis_kelamin')->label('Jenis kelamin'),
                        TextEntry::make('status_tinggal')->label('Status tinggal'),
                        TextEntry::make('no_rt_id')->label('RT'),
                        TextEntry::make('no_hp')->label('No hp'),
                        TextEntry::make('iuran.nama_iuran')->label('Iuran'),
                        TextEntry::make('alamat')->label('Alamat'),
                    ]),
            ]);
    }

    protected function sendWargaVerifiedWhatsApp(Warga $warga): void
    {
        $rawNoHp = (string) ($warga->no_hp ?? '');
        if (trim($rawNoHp) === '') {
            return;
        }

        $deviceToken = $this->resolveDeviceToken();
        if (! $deviceToken) {
            return;
        }

        $target = $this->normalizePhone($rawNoHp);
        if ($target === '') {
            return;
        }

        $message = 'akun sudah di verifikasi, layanan sudah bisa digunakan';

        $response = app(FonnteService::class)->sendWhatsAppMessage($target, $message, $deviceToken);
        $sendOk = ($response['status'] ?? false) && (! isset($response['data']['status']) || (bool) $response['data']['status']);

        if (! $sendOk) {
            $error = $response['error'] ?? ($response['data']['reason'] ?? 'Unknown error');
            Log::error('Gagal mengirim WhatsApp verifikasi warga (view)', ['warga_nik' => $warga->getKey(), 'error' => $error]);
        }
    }

    protected function resolveDeviceToken(): ?string
    {
        $token = FonnteDevice::query()
            ->whereIn('status', ['connected', 'connect'])
            ->orderByDesc('last_synced_at')
            ->value('token');

        if ($token) {
            return $token;
        }

        return FonnteDevice::query()
            ->whereIn('status', ['connected', 'connect'])
            ->latest('updated_at')
            ->value('token');
    }

    protected function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if (! is_string($digits) || $digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}
