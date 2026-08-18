<?php

namespace App\Filament\Resources\TagihanIuranWargas;

use App\Filament\Resources\TagihanIuranWargas\Pages\CreateTagihanIuranWarga;
use App\Filament\Resources\TagihanIuranWargas\Pages\EditTagihanIuranWarga;
use App\Filament\Resources\TagihanIuranWargas\Schemas\TagihanIuranWargaForm;
use App\Filament\Resources\TagihanIuranWargas\Tables\TagihanIuranWargasTable;
use App\Models\TagihanIuranWarga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TagihanIuranWargaResource extends Resource
{
    protected static ?string $model = TagihanIuranWarga::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan Warga';

    protected static ?string $navigationLabel = 'Tagihan Iuran Warga';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'tagihan_iuran_id';

    public static function form(Schema $schema): Schema
    {
        return TagihanIuranWargaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        $table = $table->query(
            TagihanIuranWarga::query()->with(['warga', 'periode'])
        );

        return TagihanIuranWargasTable::configure($table);
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
            'index' => \App\Filament\Resources\TagihanIuranWargas\Pages\GroupedTagihanIuranWargas::route('/'),
            'create' => CreateTagihanIuranWarga::route('/create'),
            'edit' => EditTagihanIuranWarga::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if ($user && $user->role && $user->role->isRT() && $user->warga && $user->warga->no_rt_id) {
            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('no_rt_id', $user->warga->no_rt_id);
            });
        }

        return $query;
    }
}
