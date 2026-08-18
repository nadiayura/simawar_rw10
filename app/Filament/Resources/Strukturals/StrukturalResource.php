<?php

namespace App\Filament\Resources\Strukturals;

use App\Filament\Resources\Strukturals\Pages\CreateStruktural;
use App\Filament\Resources\Strukturals\Pages\EditStruktural;
use App\Filament\Resources\Strukturals\Pages\ListStrukturals;
use App\Filament\Resources\Strukturals\Schemas\StrukturalForm;
use App\Filament\Resources\Strukturals\Tables\StrukturalsTable;
use App\Models\Struktural;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use UnitEnum;

class StrukturalResource extends Resource
{
    protected static ?string $model = Struktural::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Struktural RW';

    protected static ?string $pluralLabel = 'Struktural RW';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StrukturalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StrukturalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStrukturals::route('/'),
            'create' => CreateStruktural::route('/create'),
            'edit' => EditStruktural::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {

        $user = FacadesAuth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Tampilkan untuk Admin, RW, dan RT
        return $user->role->isAdmin() || $user->role->isRW();
    }
}
