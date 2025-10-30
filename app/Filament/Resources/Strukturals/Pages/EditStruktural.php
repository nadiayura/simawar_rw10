<?php

namespace App\Filament\Resources\Strukturals\Pages;

use App\Filament\Resources\Strukturals\StrukturalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;

class EditStruktural extends EditRecord
{
    protected static string $resource = StrukturalResource::class;

    public function mount(int | string $record): void
    {
        // Check authorization before mounting the page
        $user = auth()->user();
        
        if (!$user || !$user->role || !$user->role->isRW()) {
            throw new AuthorizationException('You do not have permission to edit this resource.');
        }
        
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => StrukturalResource::canDelete($this->record)),
        ];
    }
}
