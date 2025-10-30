<?php

namespace App\Filament\Resources\KegKesehatans;

use App\Filament\Resources\KegKesehatans\Pages\CreateKegKesehatan;
use App\Filament\Resources\KegKesehatans\Pages\EditKegKesehatan;
use App\Filament\Resources\KegKesehatans\Pages\ListKegKesehatans;
use App\Filament\Resources\KegKesehatans\Schemas\KegKesehatanForm;
use App\Filament\Resources\KegKesehatans\Tables\KegKesehatansTable;
use App\Models\KegKesehatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KegKesehatanResource extends Resource
{
    protected static ?string $model = KegKesehatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return KegKesehatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegKesehatansTable::configure($table);
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
            'index' => ListKegKesehatans::route('/'),
            'create' => CreateKegKesehatan::route('/create'),
            'edit' => EditKegKesehatan::route('/{record}/edit'),
        ];
    }
}
