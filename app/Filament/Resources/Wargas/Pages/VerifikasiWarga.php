<?php

namespace App\Filament\Resources\Wargas\Pages;

use App\Filament\Resources\Wargas\WargaResource;
use App\Models\FonnteDevice;
use App\Models\Iuran;
use App\Models\User;
use App\Models\Warga;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiWarga extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = WargaResource::class;

    protected string $view = 'filament.resources.wargas.pages.verifikasi-warga';

    protected function getTabs(): array
    {
        return [
            'index' => Tab::make('Data Warga')
                ->url(WargaResource::getUrl('index'))
                ->icon('heroicon-o-users'),
            'verifikasi' => Tab::make('Verifikasi')
                ->url(WargaResource::getUrl('verifikasi'))
                ->icon('heroicon-o-check-circle'),
        ];
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        $query = Warga::query()
            ->whereHas('user', function (Builder $query) {
                $query->whereHas('role', function (Builder $query) {
                    $query->where('name', 'tamu');
                });
            });

        if ($user && $user->role && $user->role->isRT()) {
            if ($user->warga && $user->warga->no_rt_id) {
                $query->where('no_rt_id', $user->warga->no_rt_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('warga_nik')
                    ->label('NIK')
                    ->formatStateUsing(fn ($state) => Warga::maskedNik($state))
                    ->searchable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('no_rt.nama_rt')
                    ->label('No.RT'),
                TextColumn::make('no_hp')
                    ->label('No. HP'),
                TextColumn::make('alamat')
                    ->label('Alamat'),
                TextColumn::make('status_tinggal')
                    ->label('Status Tinggal'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->recordActions([
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
                    ->action(function (Warga $record, array $data): void {
                        DB::beginTransaction();

                        try {
                            $record->iuran_id = $data['iuran_id'] ?? $record->iuran_id;
                            $record->save();

                            $user = User::where('warga_nik', $record->warga_nik)->first();

                            if ($user) {
                                $user->role_id = 1;
                                $user->save();

                                Notification::make()
                                    ->title('Warga berhasil diverifikasi')
                                    ->body('Jenis iuran: '.(Iuran::find($record->iuran_id)?->nama_iuran ?? '-'))
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('User tidak ditemukan')
                                    ->danger()
                                    ->send();

                                throw new Halt;
                            }

                            DB::commit();

                            try {
                                $this->sendWargaVerifiedWhatsApp($record);
                            } catch (\Throwable $e) {
                                Log::error('Gagal kirim WhatsApp verifikasi warga', [
                                    'warga_nik' => $record->getKey(),
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Terjadi kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            throw new Halt;
                        }
                    }),
                Action::make('tolak')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(function (Warga $record): void {
                        Notification::make()
                            ->title('Verifikasi warga ditolak')
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada warga yang perlu diverifikasi')
            ->emptyStateDescription('Semua warga sudah diverifikasi.');
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
            Log::error('Gagal mengirim WhatsApp verifikasi warga', ['warga_nik' => $warga->getKey(), 'error' => $error]);
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
