<?php

namespace App\Filament\Resources\Wargas;

use App\Filament\Resources\Wargas\Pages\CreateWarga;
use App\Filament\Resources\Wargas\Pages\EditWarga;
use App\Filament\Resources\Wargas\Pages\VerifikasiWarga;
use App\Filament\Resources\Wargas\Schemas\WargaForm;
use App\Filament\Resources\Wargas\Tables\WargasTable;
use App\Models\Warga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class WargaResource extends Resource
{
    protected static ?string $model = Warga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Warga';

    protected static ?string $pluralLabel = ' Data Warga';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // RW: lihat semua warga
        if ($user && $user->role && $user->role->isRW()) {
            return $query;
        }

        // RT: batasi warga ke RT yang sama via warga.no_rt_id
        if ($user && $user->role && $user->role->isRT()) {
            if ($user->warga && $user->warga->no_rt_id) {
                $query->where('no_rt_id', $user->warga->no_rt_id);
            } else {
                // Jika no_rt_id belum terisi, tampilkan kosong
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
            'index' => \App\Filament\Resources\Wargas\Pages\GroupedWargas::route('/'),
            'create' => CreateWarga::route('/create'),
            'edit' => EditWarga::route('/{record}/edit'),
            'view' => \App\Filament\Resources\Wargas\Pages\ViewWarga::route('/{record:warga_nik}'),
            'verifikasi' => VerifikasiWarga::route('/verifikasi'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user && $user->role && ($user->role->isRW() || $user->role->isRT() || $user->role->isAdmin());
    }
}
