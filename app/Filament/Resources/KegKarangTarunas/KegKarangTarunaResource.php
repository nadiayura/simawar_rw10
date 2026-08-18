<?php

namespace App\Filament\Resources\KegKarangTarunas;

use App\Filament\Resources\KegKarangTarunas\Pages\CreateKegKarangTaruna;
use App\Filament\Resources\KegKarangTarunas\Pages\EditKegKarangTaruna;
use App\Filament\Resources\KegKarangTarunas\Pages\GroupedKegKarangTarunas;
use App\Filament\Resources\KegKarangTarunas\Schemas\KegKarangTarunaForm;
use App\Filament\Resources\KegKarangTarunas\Tables\KegKarangTarunasTable;
use App\Models\KegKarangTaruna;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KegKarangTarunaResource extends Resource
{
    protected static ?string $model = KegKarangTaruna::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan Warga';

    protected static ?string $navigationLabel = 'Kegiatan Karang Taruna';

    protected static ?string $pluralModelLabel = 'Kegiatan Karang Taruna';

    protected static ?string $recordTitleAttribute = 'KegKarangTaruna';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRT();
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRT();
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRT();
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRT();
    }

    public static function form(Schema $schema): Schema
    {
        return KegKarangTarunaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegKarangTarunasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => GroupedKegKarangTarunas::route('/'),
            'create' => CreateKegKarangTaruna::route('/create'),
            'edit' => EditKegKarangTaruna::route('/{record}/edit'),
        ];
    }
}
