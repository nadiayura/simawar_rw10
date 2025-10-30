<?php

namespace App\Filament\Resources\KetuaRts;

use App\Filament\Resources\KetuaRts\Pages\CreateKetuaRt;
use App\Filament\Resources\KetuaRts\Pages\EditKetuaRt;
use App\Filament\Resources\KetuaRts\Pages\ListKetuaRts;
use App\Filament\Resources\KetuaRts\Schemas\KetuaRtForm;
use App\Filament\Resources\KetuaRts\Tables\KetuaRtsTable;
use App\Models\KetuaRt;
use App\Models\Tenant;
use BackedEnum;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class KetuaRtResource extends Resource
{

    protected static ?string $model = KetuaRt::class;

    protected static string |UnitEnum|null $navigationGroup = 'Manajemen Data';

    protected static ?string $navigationLabel = 'Struktural RT';

    protected static ?string $modelLabel = 'Struktural RT';

    protected static ?string $pluralModelLabel = 'Struktural RT';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var Tenant $tenant */
        $tenant = Filament::getTenant();

        if ($tenant) {
            // Filter ketua RT by tenant's RT
            $query->where('no_rt', $tenant->no_rt);
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
            'index' => ListKetuaRts::route('/'),
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

        if (!$user || !$user->role) {
            return false;
        }

        // Allow access for admin (no specific role check needed for admin)
        // Allow access for RW users
        // Deny access for RT users
        return !$user->role->isRT();
    }

    /**
     * Determine if user can view any records.
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->role) {
            return false;
        }

        // Allow admin and RW, deny RT
        return !$user->role->isRT();
    }

    /**
     * Determine if user can create records.
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->role) {
            return false;
        }

        // Allow admin and RW, deny RT
        return !$user->role->isRT();
    }

    /**
     * Determine if user can edit a specific record.
     */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (!$user || !$user->role) {
            return false;
        }

        // Allow admin and RW, deny RT
        return !$user->role->isRT();
    }

    /**
     * Determine if user can delete a specific record.
     */
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        if (!$user || !$user->role) {
            return false;
        }

        // Allow admin and RW, deny RT
        return !$user->role->isRT();
    }

}
