<?php

namespace App\Filament\Resources\RekapKeuangans;

use App\Filament\Resources\RekapKeuangans\Pages\CreateRekapKeuangan;
use App\Filament\Resources\RekapKeuangans\Pages\EditRekapKeuangan;
use App\Filament\Resources\RekapKeuangans\Schemas\RekapKeuanganForm;
use App\Filament\Resources\RekapKeuangans\Tables\RekapKeuangansTable;
use App\Models\RekapKeuangan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RekapKeuanganResource extends Resource
{
    protected static ?string $model = RekapKeuangan::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan Warga';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'rekap_keuangan_id';

    protected static ?string $navigationLabel = 'Rekap Keuangan';

    protected static ?string $pluralModelLabel = 'Rekap Keuangan';

    public static function form(Schema $schema): Schema
    {
        return RekapKeuanganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RekapKeuangansTable::configure($table);
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
            'index' => \App\Filament\Resources\RekapKeuangans\Pages\GroupedRekapKeuangan::route('/'),
            'create' => CreateRekapKeuangan::route('/create'),
            'edit' => EditRekapKeuangan::route('/{record}/edit'),
            'report' => \App\Filament\Resources\RekapKeuangans\Pages\ReportRekapKeuangan::route('/report'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }
}
