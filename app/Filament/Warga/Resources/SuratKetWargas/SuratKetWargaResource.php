<?php

namespace App\Filament\Warga\Resources\SuratKetWargas;

use App\Filament\Warga\Resources\SuratKetWargas\Pages\CreateSuratKetWarga;
use App\Filament\Warga\Resources\SuratKetWargas\Pages\EditSuratKetWarga;
use App\Filament\Warga\Resources\SuratKetWargas\Pages\KategoriSuratSelect;
use App\Filament\Warga\Resources\SuratKetWargas\Pages\ListSuratKetWargas;
use App\Filament\Warga\Resources\SuratKetWargas\Schemas\SuratKetWargaForm;
use App\Filament\Warga\Resources\SuratKetWargas\Tables\SuratKetWargasTable;
use App\Models\SuratKetWarga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SuratKetWargaResource extends Resource
{
    protected static ?string $model = SuratKetWarga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $recordTitleAttribute = 'surat_ket_warga_id';

    protected static ?string $navigationLabel = 'Pengajuan Surat';

    protected static ?string $pluralModelLabel = 'Pengajuan Surat';

    public static function form(Schema $schema): Schema
    {
        return SuratKetWargaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuratKetWargasTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if ($user && $user->warga_nik) {
            return $query->where('warga_nik', $user->warga_nik);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => KategoriSuratSelect::route('/'),
            'list' => ListSuratKetWargas::route('/list'),
            'create' => CreateSuratKetWarga::route('/create'),
            'edit' => EditSuratKetWarga::route('/{record:surat_ket_warga_id}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->role) {
            return false;
        }

        return ! ($user->role->isTamu() || (int) $user->role_id === 8);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->role) {
            return false;
        }

        return ! ($user->role->isTamu() || (int) $user->role_id === 8);
    }
}
