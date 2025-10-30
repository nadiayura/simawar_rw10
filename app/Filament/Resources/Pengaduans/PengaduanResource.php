<?php

namespace App\Filament\Resources\Pengaduans;

use App\Filament\Resources\Pengaduans\Pages\CreatePengaduan;
use App\Filament\Resources\Pengaduans\Pages\EditPengaduan;
use App\Filament\Resources\Pengaduans\Pages\ListPengaduans;
use App\Filament\Resources\Pengaduans\Schemas\PengaduanForm;
use App\Filament\Resources\Pengaduans\Tables\PengaduansTable;
use App\Models\Pengaduan;
use App\Models\Tenant;
use BackedEnum;
use Filament\Facades\Filament;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string |UnitEnum|null $navigationGroup = 'Layanan Warga';

    protected static ?string $navigationLabel='Pengaduan Warga';

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

        if (!$user || !$user->role) {
            return $query->whereRaw('1 = 0'); // Return empty query if no user or role
        }

        /** @var Tenant $tenant */
        $tenant = Filament::getTenant();

        if ($user->role->isAdmin()) {
            // Admin dapat melihat semua pengaduan
            return $query;
        } elseif ($user->role->isRW()) {
            // RW dapat melihat semua pengaduan dari RT dalam RW-nya
            if ($tenant) {
                // Konversi format RW tenant (2 digit) ke format warga (3 digit)
                $tenantRwPadded = str_pad($tenant->rw, 3, '0', STR_PAD_RIGHT);
                
                $query->whereHas('warga', function($q) use ($tenantRwPadded) {
                    $q->where('rw', $tenantRwPadded);
                });
            }
        } elseif ($user->role->isRT()) {
            // RT hanya dapat melihat pengaduan dari RT sendiri
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            }
        } else {
            // Role lain tidak dapat mengakses pengaduan
            return $query->whereRaw('1 = 0');
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
            'index' => ListPengaduans::route('/'),
            'create' => CreatePengaduan::route('/create'),
            'edit' => EditPengaduan::route('/{record}/edit'),
        ];
    }
}
