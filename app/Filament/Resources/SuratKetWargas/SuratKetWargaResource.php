<?php

namespace App\Filament\Resources\SuratKetWargas;

use App\Filament\Resources\SuratKetWargas\Pages\CreateSuratKetWarga;
use App\Filament\Resources\SuratKetWargas\Pages\ViewSuratKetWarga;
use App\Filament\Resources\SuratKetWargas\Schemas\SuratKetWargaForm;
use App\Filament\Resources\SuratKetWargas\Tables\SuratKetWargasTable;
use App\Models\SuratKetWarga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SuratKetWargaResource extends Resource
{
    protected static ?string $model = SuratKetWarga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Warga';

    protected static ?string $navigationLabel = 'Pengajuan Surat Warga';

    protected static ?string $pluralLabel = 'Pengajuan Surat Warga';

    protected static ?string $recordTitleAttribute = 'surat_ket_warga_id';

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

        if (! $user || ! $user->role) {
            return $query->whereRaw('1 = 0');
        }

        // Ketua RT hanya melihat surat dari warga RT yang sama
        if ($user->role->isRT()) {
            if ($user->warga && $user->warga->no_rt_id) {
                return $query->whereHas('warga', function ($q) use ($user) {
                    $q->where('no_rt_id', $user->warga->no_rt_id);
                });
            }

            return $query->whereRaw('1 = 0');
        }

        // RW/Admin melihat semua
        return $query;
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
            'index' => Pages\GroupedSuratKetWargas::route('/'),
            'create' => CreateSuratKetWarga::route('/create'),
            'grouped' => Pages\GroupedSuratKetWargas::route('/grouped'),
            'view' => ViewSuratKetWarga::route('/{record:surat_ket_warga_id}'),
        ];
    }
}
