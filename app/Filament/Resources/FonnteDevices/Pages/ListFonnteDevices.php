<?php

namespace App\Filament\Resources\FonnteDevices\Pages;

use App\Filament\Resources\FonnteDevices\FonnteDeviceResource;
use App\Models\FonnteDevice;
use App\Services\FonnteService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;

class ListFonnteDevices extends ListRecords
{
    protected static string $resource = FonnteDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('sync_devices')
                ->label('Sync Devices')
                ->action(function () {
                    $service = app(FonnteService::class);
                    $resp = $service->getAllDevices();
                    if (! $resp || empty($resp['status'])) {
                        Notification::make()->title('Gagal sinkron')->danger()->send();

                        return;
                    }
                    $payload = $resp['data'] ?? [];
                    $list = Arr::get($payload, 'data', $payload);
                    $count = 0;
                    foreach ((array) $list as $d) {
                        $deviceNo = Arr::get($d, 'device');
                        if (! $deviceNo) {
                            continue;
                        }
                        FonnteDevice::updateOrCreate(
                            ['device' => $deviceNo],
                            [
                                'name' => Arr::get($d, 'name'),
                                'token' => Arr::get($d, 'token'),
                                'status' => (string) (Arr::get($d, 'status') ?? Arr::get($d, 'connected')),
                                'last_synced_at' => now(),
                            ]
                        );
                        $count++;
                    }
                    Notification::make()->title('Sinkron selesai')->body('Devices: '.$count)->success()->send();
                }),
        ];
    }
}
