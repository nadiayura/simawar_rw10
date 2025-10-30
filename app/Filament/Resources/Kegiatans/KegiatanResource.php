<?php

namespace App\Filament\Resources\Kegiatans;

use App\Filament\Resources\Kegiatans\Pages\CreateKegiatan;
use App\Filament\Resources\Kegiatans\Pages\EditKegiatan;
use App\Filament\Resources\Kegiatans\Pages\ListKegiatans;
use App\Filament\Resources\Kegiatans\Schemas\KegiatanForm;
use App\Filament\Resources\Kegiatans\Tables\KegiatansTable;
use App\Models\Kegiatan;
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

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static ?string $navigationLabel = 'Kegiatan';

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'nama_kegiatan';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var Tenant $tenant */
        $tenant = Filament::getTenant();
        $user = Auth::user();

        if ($tenant && $user && $user->role) {
            if ($user->role->isRT()) {
                // RT users can only see activities for their specific RT
                $query->where('tenant_id', $tenant->id);
            } elseif ($user->role->isRW()) {
                // RW users can see activities for all RTs in their RW
                $query->whereHas('tenant', function ($q) use ($tenant) {
                    $q->where('rw', $tenant->rw);
                });
            }
            // Admin users can see all activities (no additional filtering)
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return KegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegiatansTable::configure($table);
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
            'index' => ListKegiatans::route('/'),
            'create' => CreateKegiatan::route('/create'),
            'edit' => EditKegiatan::route('/{record}/edit'),
        ];
    }

    /**
     * Determine if the resource should be registered in navigation.
     * Allow access for all authenticated users (RT, RW, and admin).
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role;
    }

    /**
     * Determine if user can view any records.
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role;
    }

    /**
     * Determine if user can create records.
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->role;
    }

    /**
     * Determine if user can edit a specific record.
     */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if (!$user || !$user->role || !$tenant) {
            return false;
        }

        // Admin can edit all records
        if ($user->role->isAdmin()) {
            return true;
        }

        // RW users can edit activities in their RW
        if ($user->role->isRW() && $record->tenant && $record->tenant->rw === $tenant->rw) {
            return true;
        }

        // RT users can only edit activities for their specific RT
        if ($user->role->isRT() && $record->tenant_id === $tenant->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete a specific record.
     */
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if (!$user || !$user->role || !$tenant) {
            return false;
        }

        // Admin can delete all records
        if ($user->role->isAdmin()) {
            return true;
        }

        // RW users can delete activities in their RW
        if ($user->role->isRW() && $record->tenant && $record->tenant->rw === $tenant->rw) {
            return true;
        }

        // RT users can only delete activities for their specific RT
        if ($user->role->isRT() && $record->tenant_id === $tenant->id) {
            return true;
        }

        return false;
    }
}
