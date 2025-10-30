<?php

namespace App\Filament\Resources\BaganStrukturals;

use App\Filament\Resources\BaganStrukturals\Pages;
use App\Models\Struktural;
use BackedEnum;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BaganStrukturalResource extends Resource
{
    protected static ?string $model = Struktural::class;

    // Menentukan relasi yang digunakan untuk tenant ownership
    protected static ?string $tenantOwnershipRelationshipName = 'warga';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;


    protected static string |UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Bagan Struktural';

    protected static ?string $modelLabel = 'Bagan Struktural';

    protected static ?string $pluralModelLabel = 'Bagan Struktural';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        // This resource is only for viewing, no actual query needed
        // Data will be handled in the custom page
        return Struktural::query()->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        // No form needed for this resource
        return $schema;
    }

    public static function table(Table $table): Table
    {
        // No table needed for this resource
        return $table;
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
            'index' => Pages\ViewBaganStruktural::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        // Only allow RT (Ketua RT) to view the structural chart
        return $user && $user->role->isRT();
    }

    public static function canCreate(): bool
    {
        // No create functionality for this resource
        return false;
    }

    public static function canEdit($record): bool
    {
        // No edit functionality for this resource
        return false;
    }

    public static function canDelete($record): bool
    {
        // No delete functionality for this resource
        return false;
    }

    public static function canDeleteAny(): bool
    {
        // No delete functionality for this resource
        return false;
    }
}
