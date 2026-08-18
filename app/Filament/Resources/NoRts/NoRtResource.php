<?php

namespace App\Filament\Resources\NoRts;

use App\Filament\Resources\NoRts\Pages\CreateNoRt;
use App\Filament\Resources\NoRts\Pages\EditNoRt;
use App\Filament\Resources\NoRts\Pages\ListNoRts;
use App\Filament\Resources\NoRts\Schemas\NoRtForm;
use App\Filament\Resources\NoRts\Tables\NoRtsTable;
use App\Models\NoRt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NoRtResource extends Resource
{
    protected static ?string $model = NoRt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNumberedList;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $recordTitleAttribute = 'nomor';

    protected static ?string $navigationLabel = 'Daftar RT';

    protected static ?string $pluralLabel = 'Daftar RT';

    public static function form(Schema $schema): Schema
    {
        return NoRtForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoRtsTable::configure($table);
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
            'index' => ListNoRts::route('/'),
            'create' => CreateNoRt::route('/create'),
            'edit' => EditNoRt::route('/{record}/edit'),
        ];
    }
}
