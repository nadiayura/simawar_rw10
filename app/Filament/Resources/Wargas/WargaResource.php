<?php

namespace App\Filament\Resources\Wargas;

use App\Filament\Resources\Wargas\Pages\CreateWarga;
use App\Filament\Resources\Wargas\Pages\EditWarga;
use App\Filament\Resources\Wargas\Pages\ListWargas;
use App\Filament\Resources\Wargas\Schemas\WargaForm;
use App\Filament\Resources\Wargas\Tables\WargasTable;
use App\Models\Warga;
use App\Models\Tenant;
use BackedEnum;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Panel;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WargaResource extends Resource
{
    protected static ?string $model = Warga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string |UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Warga';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // If user is RW, show all warga
        if ($user && $user->role && $user->role->isRW()) {
            return $query;
        }

        // If user is RT, filter by their RT
        if ($user && $user->role && $user->role->isRT()) {
            $rtNumber = $user->getRTNumber();
            $rwNumber = $user->getRWNumber();

            if ($rtNumber && $rwNumber) {
                $query->where('id_rt', $rtNumber)
                      ->where('rw', $rwNumber);
            } else {
                // If RT/RW info not available, return empty result
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }



    public static function form(Schema $schema): Schema
    {
        return WargaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WargasTable::configure($table);
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
            'index' => ListWargas::route('/'),
            'create' => CreateWarga::route('/create'),
            'edit' => EditWarga::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role && ($user->role->isRW() || $user->role->isRT());
    }
}
