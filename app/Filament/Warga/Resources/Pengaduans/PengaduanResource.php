<?php

namespace App\Filament\Warga\Resources\Pengaduans;

use App\Filament\Warga\Resources\Pengaduans\Pages\CreatePengaduan;
use App\Filament\Warga\Resources\Pengaduans\Pages\EditPengaduan;
use App\Filament\Warga\Resources\Pengaduans\Pages\ListPengaduans;
use App\Filament\Warga\Resources\Pengaduans\Schemas\PengaduanForm;
use App\Filament\Warga\Resources\Pengaduans\Tables\PengaduansTable;
use App\Models\Pengaduan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengaduanResource extends Resource
{
    protected static ?string $model = Pengaduan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?string $recordTitleAttribute = 'pengaduan_id';

    protected static ?string $pluralModelLabel = 'Pengaduan';

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

        // Hanya tampilkan pengaduan dari warga yang sedang login
        if ($user && $user->warga_nik) {
            return $query->where('warga_nik', $user->warga_nik);
        }

        // Jika user tidak memiliki warga_id, tidak tampilkan data apapun
        return $query->whereRaw('1 = 0');
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
            'index' => ListPengaduans::route('/'),
            'create' => CreatePengaduan::route('/create'),
            'edit' => EditPengaduan::route('/{record:pengaduan_id}/edit'),
        ];
    }

    // Membatasi akses resource pengaduan untuk role tamu
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Jika user memiliki role tamu, sembunyikan resource pengaduan
        if ($user && $user->role && $user->role->name === 'tamu') {
            return false;
        }

        return true;
    }

    // Mencegah akses ke halaman pengaduan untuk role tamu
    public static function canAccess(): bool
    {
        $user = Auth::user();

        // Jika user memiliki role tamu, tolak akses ke resource pengaduan
        if ($user && $user->role && $user->role->name === 'tamu') {
            return false;
        }

        return true;
    }
}
