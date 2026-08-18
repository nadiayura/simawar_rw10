<?php

namespace App\Filament\Warga\Resources\PembayaranIurans;

use App\Filament\Warga\Resources\PembayaranIurans\Pages\ListPembayaranIurans;
use App\Filament\Warga\Resources\PembayaranIurans\Tables\PembayaranIuransTable;
use App\Models\TagihanIuranWarga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PembayaranIuranResource extends Resource
{
    protected static ?string $model = TagihanIuranWarga::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $pluralModelLabel = 'Pembayaran Iuran';

    protected static ?string $navigationLabel = 'Pembayaran Iuran';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return PembayaranIuransTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['periode', 'status', 'pembayaranTunai', 'pembayaranMidtrans'])
            ->leftJoin('periode_iurans', 'periode_iurans.periode_iuran_id', '=', 'tagihan_iuran_wargas.periode_iuran_id')
            ->select('tagihan_iuran_wargas.*')
            ->selectRaw('periode_iurans.tahun as periode_tahun, periode_iurans.bulan as periode_bulan')
            ->orderByDesc('periode_iurans.tahun')
            ->orderBy('periode_iurans.bulan');

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
            'index' => ListPembayaranIurans::route('/'),
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
