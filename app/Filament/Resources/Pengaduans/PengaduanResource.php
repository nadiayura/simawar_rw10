<?php

namespace App\Filament\Resources\Pengaduans;

use App\Filament\Resources\Pengaduans\Pages\CreatePengaduan;
use App\Filament\Resources\Pengaduans\Pages\EditPengaduan;
use App\Filament\Resources\Pengaduans\Pages\GroupedPengaduans;
use App\Filament\Resources\Pengaduans\Pages\ViewPengaduan;
use App\Filament\Resources\Pengaduans\Schemas\PengaduanForm;
use App\Filament\Resources\Pengaduans\Tables\PengaduansTable;
use App\Models\Pengaduan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PengaduanResource extends Resource
{
    protected static ?string $model = Pengaduan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Warga';

    protected static ?string $navigationLabel = 'Pengaduan Warga';

    protected static ?string $pluralModelLabel = 'Pengaduan Warga';

    protected static ?string $recordTitleAttribute = 'pengaduan_id';

    public static function getGlobalSearchEloquentQuery(): Builder
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
        return PengaduanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengaduansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return $query->whereRaw('1 = 0');
        }

        // RW: lihat semua pengaduan
        if ($user->role->isRW()) {
            return $query;
        }

        // RT: hanya lihat pengaduan dari warga dengan no_rt_id yang sama
        if ($user->role->isRT()) {
            if ($user->warga && $user->warga->no_rt_id) {
                return $query->whereHas('warga', function ($q) use ($user) {
                    $q->where('no_rt_id', $user->warga->no_rt_id);
                });
            } else {
                // Jika RT belum memiliki no_rt_id, tampilkan kosong
                return $query->whereRaw('1 = 0');
            }
        }

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
            'index' => GroupedPengaduans::route('/'),
            'create' => CreatePengaduan::route('/create'),
            'edit' => EditPengaduan::route('/{record:pengaduan_id}/edit'),
            'view' => ViewPengaduan::route('/{record:pengaduan_id}'),
        ];
    }
}
