<?php

namespace App\Filament\Resources\KegKesehatans;

use App\Filament\Resources\KegKesehatans\Pages\CreateKegKesehatan;
use App\Filament\Resources\KegKesehatans\Pages\EditKegKesehatan;
use App\Filament\Resources\KegKesehatans\Pages\GroupedKegKesehatans;
use App\Filament\Resources\KegKesehatans\Schemas\KegKesehatanForm;
use App\Filament\Resources\KegKesehatans\Tables\KegKesehatansTable;
use App\Models\KegKesehatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KegKesehatanResource extends Resource
{
    protected static ?string $model = KegKesehatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan Warga';

    protected static ?string $navigationLabel = 'Kegiatan Kesehatan';

    protected static ?string $pluralModelLabel = 'Kegiatan Kesehatan';

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

        if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        if ($user->role->isRT() && strtolower((string) $user->role->name) === 'rt') {
            return false;
        }

        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    public static function getPages(): array
    {
        return [
            'index' => GroupedKegKesehatans::route('/'),
            'create' => CreateKegKesehatan::route('/create'),
            'edit' => EditKegKesehatan::route('/{record}/edit'),
        ];
    }
}
