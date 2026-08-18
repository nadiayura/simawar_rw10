<?php

namespace App\Filament\Resources\PeriodeIurans;

use App\Filament\Resources\PeriodeIurans\Pages\CreatePeriodeIuran;
use App\Filament\Resources\PeriodeIurans\Pages\EditPeriodeIuran;
use App\Filament\Resources\PeriodeIurans\Pages\GroupedPeriodeIurans;
use App\Filament\Resources\PeriodeIurans\Pages\ListPeriodeIurans;
use App\Filament\Resources\PeriodeIurans\Schemas\PeriodeIuranForm;
use App\Filament\Resources\PeriodeIurans\Tables\PeriodeIuransTable;
use App\Models\PeriodeIuran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PeriodeIuranResource extends Resource
{
    protected static ?string $model = PeriodeIuran::class;

    protected static ?string $navigationLabel = 'Periode Iuran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan Warga';

    protected static ?string $pluralModelLabel = 'Periode Iuran';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PeriodeIuranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        $table = $table->query(
            PeriodeIuran::query()
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan')
        );

        return PeriodeIuransTable::configure($table);
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
            'index' => GroupedPeriodeIurans::route('/'),
            'list' => ListPeriodeIurans::route('/list'),
            'create' => CreatePeriodeIuran::route('/create'),
            'edit' => EditPeriodeIuran::route('/{record}/edit'),
        ];
    }
}
