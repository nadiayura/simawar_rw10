<?php

namespace App\Filament\Resources\KetuaRts;

use App\Filament\Resources\KetuaRts\Pages\CreateKetuaRt;
use App\Filament\Resources\KetuaRts\Pages\EditKetuaRt;
use App\Filament\Resources\KetuaRts\Schemas\KetuaRtForm;
use App\Filament\Resources\KetuaRts\Tables\KetuaRtsTable;
use App\Models\KetuaRt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KetuaRtResource extends Resource
{
    protected static ?string $model = KetuaRt::class;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Struktural RT';

    protected static ?string $modelLabel = 'Struktural RT';

    protected static ?string $pluralModelLabel = 'Struktural RT';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'ketua_rt_id';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // Jika user adalah Ketua RT, batasi data ke RT-nya (berdasarkan warga.no_rt_id)
        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
            $query->where('no_rt_id', $user->warga->no_rt_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return KetuaRtForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KetuaRtsTable::configure($table);
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
            'index' => \App\Filament\Resources\KetuaRts\Pages\GroupedKetuaRts::route('/'),
            'create' => CreateKetuaRt::route('/create'),
            'edit' => EditKetuaRt::route('/{record}/edit'),
        ];
    }

    /**
     * Determine if the resource should be registered in navigation.
     * Only show for admin and RW users, hide from RT users.
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Tampilkan untuk Admin, RW, dan RT
        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    /**
     * Determine if user can view any records.
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Izinkan Admin, RW, dan RT
        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    /**
     * Determine if user can create records.
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Admin & RW boleh buat; RT boleh buat untuk RT miliknya
        return $user->role->isAdmin() || $user->role->isRW() || $user->role->isRT();
    }

    /**
     * Determine if user can edit a specific record.
     */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Admin & RW boleh edit; RT hanya jika record RT sama
        if ($user->role->isAdmin() || $user->role->isRW()) {
            return true;
        }

        return $user->role->isRT()
            && $user->warga
            && $user->warga->no_rt_id
            && (string) $record->no_rt_id === (string) $user->warga->no_rt_id;
    }

    /**
     * Determine if user can delete a specific record.
     */
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return false;
        }

        // Admin & RW boleh delete; RT hanya jika record RT sama
        if ($user->role->isAdmin() || $user->role->isRW()) {
            return true;
        }

        return $user->role->isRT()
            && $user->warga
            && $user->warga->no_rt_id
            && (string) $record->no_rt_id === (string) $user->warga->no_rt_id;
    }
}
