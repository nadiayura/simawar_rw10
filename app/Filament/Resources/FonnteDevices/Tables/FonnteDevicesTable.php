<?php

namespace App\Filament\Resources\FonnteDevices\Tables;

use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FonnteDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('device')
                    ->label('Perangkat')
                    ->searchable(),
                // TextColumn::make('token')
                //     ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'connect', 'connected' => 'success',
                        'disconnect', 'disconnected' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('last_synced_at')
                    ->label('Terakhir Sinkronisasi')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diupdate Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('connect')
                    ->label('Connect')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn ($record) => route('fonnte.qr', ['device' => $record]))
                    ->openUrlInNewTab(),
                Action::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $service = app(FonnteService::class);
                        $resp = $service->disconnectDevice($record->token);
                        if (! $resp['status']) {
                            Notification::make()->title('Gagal disconnect')->body($resp['error'] ?? 'Error')->danger()->send();

                            return;
                        }
                        Notification::make()->title('Berhasil disconnect')->success()->send();
                    }),
                Action::make('broadcast')
                    ->label('Broadcast Pesan')
                    ->icon('heroicon-o-megaphone')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('target')->required()->label('Nomor Tujuan'),
                        \Filament\Forms\Components\Textarea::make('message')->required()->label('Pesan'),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(FonnteService::class);
                        $resp = $service->sendWhatsAppMessage($data['target'], $data['message'], $record->token);
                        if (! $resp['status'] || (isset($resp['data']['status']) && ! $resp['data']['status'])) {
                            $err = $resp['data']['reason'] ?? $resp['error'] ?? 'Error';
                            Notification::make()->title('Gagal kirim pesan')->body($err)->danger()->send();

                            return;
                        }
                        Notification::make()->title('Pesan terkirim')->success()->send();
                    }),
                Action::make('info')
                    ->label('Info Perangkat')
                    ->icon('heroicon-o-information-circle')
                    ->action(function ($record) {
                        $service = app(FonnteService::class);
                        $resp = $service->getDeviceProfile($record->token);
                        if (! $resp['status']) {
                            Notification::make()->title('Gagal ambil info')->body($resp['error'] ?? 'Error')->danger()->send();

                            return;
                        }
                        $name = $resp['data']['name'] ?? $record->name;
                        $status = $resp['data']['status.keterangan'] ?? $record->status;
                        $keterangan = $resp['data']['keterangan'] ?? '';
                        Notification::make()->title('Info Perangkat')->body('Nama: '.$name.' Status: '.$status)->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
