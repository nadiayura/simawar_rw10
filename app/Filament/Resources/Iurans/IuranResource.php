<?php

namespace App\Filament\Resources\Iurans;

use App\Filament\Resources\Iurans\Pages\CreateIuran;
use App\Filament\Resources\Iurans\Pages\EditIuran;
use App\Filament\Resources\Iurans\Pages\ListIurans;
use App\Filament\Resources\Iurans\Schemas\IuranForm;
use App\Filament\Resources\Iurans\Tables\IuransTable;
use App\Models\Iuran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IuranResource extends Resource
{
    protected static ?string $model = Iuran::class;

    protected static ?string $navigationLabel = 'Jenis Iuran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $pluralModelLabel = 'Jenis Iuran';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Layanan';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return IuranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IuransTable::configure($table);
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
            'index' => ListIurans::route('/'),
            'create' => CreateIuran::route('/create'),
            'edit' => EditIuran::route('/{record}/edit'),
        ];
    }
}
