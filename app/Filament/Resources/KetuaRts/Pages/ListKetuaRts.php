<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;

class ListKetuaRts extends ListRecords
{
    protected static string $resource = KetuaRtResource::class;

    public function mount(): void
    {
        // Check authorization before mounting the page
        $user = auth()->user();
        
        if (!$user || !$user->role || $user->role->isRT()) {
            throw new AuthorizationException('You do not have permission to access this resource.');
        }
        
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
