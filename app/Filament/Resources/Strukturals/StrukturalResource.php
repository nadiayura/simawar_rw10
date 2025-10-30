<?php

namespace App\Filament\Resources\Strukturals;

use App\Filament\Resources\Strukturals\Pages\CreateStruktural;
use App\Filament\Resources\Strukturals\Pages\EditStruktural;
use App\Filament\Resources\Strukturals\Pages\ListStrukturals;
use App\Filament\Resources\Strukturals\Schemas\StrukturalForm;
use App\Filament\Resources\Strukturals\Tables\StrukturalsTable;
use App\Models\Struktural;
use BackedEnum;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
class StrukturalResource extends Resource
{
    protected static ?string $model = Struktural::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string |UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Struktural RW';

    protected static ?string $modelLabel = 'Struktural RW';

    protected static ?string $pluralModelLabel = 'Struktural RW';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        // Tampilkan semua data struktural tanpa filter tenant
        // Karena kita sudah tidak menggunakan no_rt
        return Struktural::query()->with('warga')->ordered();
    }
    
    // Override panel method untuk memastikan resource ini tidak menggunakan tenant scoping
    public static function canAccessPanel(Panel $panel): bool
    {
        // Pastikan hanya admin yang bisa mengakses
        $user = Auth::user();
        return $user && $user->role && $user->role->isRW();
    }



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

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        // Only allow RW (Admin) and Ketua RW to view the page
        // Ketua RT will have separate structural chart view
        return $user && $user->role->isRW();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        // Only RW (Admin) can create new structural records
        return $user && $user->role->isRW();
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        // Only RW (Admin) can edit structural records, regardless of current RT context
        return $user && $user->role->isRW();
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        // Only RW (Admin) can delete structural records
        return $user && $user->role->isRW();
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        // Only RW (Admin) can delete structural records
        return $user && $user->role->isRW();
    }
}
