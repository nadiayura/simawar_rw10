<?php

namespace App\Filament\Resources\KetuaRts\Pages;

use App\Filament\Resources\KetuaRts\KetuaRtResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;

class EditKetuaRt extends EditRecord
{
    protected static string $resource = KetuaRtResource::class;

    public function mount(int | string $record): void
    {
        // Check authorization before mounting the page
        $user = auth()->user();
        
        if (!$user || !$user->role || $user->role->isRT()) {
            throw new AuthorizationException('You do not have permission to edit this resource.');
        }
        
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => KetuaRtResource::canDelete($this->record)),
        ];
    }
}
