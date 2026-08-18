<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Auth\Access\AuthorizationException;

class ListKetuaRts extends ListRecords
{
    protected static string $resource = KetuaRtResource::class;

    public function mount(): void
    {
        // Hapus blok yang melempar AuthorizationException untuk RT
        // Check authorization before mounting the page

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => KetuaRtResource::canCreate()),
        ];
    }
}
