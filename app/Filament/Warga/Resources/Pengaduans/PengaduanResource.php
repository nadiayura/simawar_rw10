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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

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
        if ($user && $user->warga_id) {
            return $query->where('id_warga', $user->warga_id);
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
            'edit' => EditPengaduan::route('/{record}/edit'),
        ];
    }
}
