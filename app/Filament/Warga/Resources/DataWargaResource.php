<?php

namespace App\Filament\Warga\Resources;

use App\Filament\Warga\Resources\DataWargaResource\Pages;
use App\Models\NoRt;
use App\Models\Role;
use App\Models\User;
use App\Models\Warga;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DataWargaResource extends Resource
{
    protected static ?string $model = Warga::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Data Diri';

    protected static ?string $modelLabel = 'Data Diri';

    protected static ?string $pluralModelLabel = 'Data Diri';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('warga_nik')
                    ->label('NIK')
                    ->required()
                    ->maxLength(16)
                    ->dehydrateStateUsing(fn ($state) => $state ? ('WRG-'.$state) : null)
                    ->afterStateHydrated(fn ($state, callable $set) => $state ? $set('warga_nik', (string) str_replace('WRG-', '', (string) $state)) : null)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('jenis_kelamin')
                    ->required()
                    ->options(['L' => 'L', 'P' => 'P']),
                Forms\Components\Select::make('agama')
                    ->required()
                    ->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                        'Lainnya' => 'Lainnya',
                    ]),
                Forms\Components\Select::make('status_tinggal')
                    ->options(['Tetap' => 'Tetap', 'Kontrak' => 'Kontrak', 'Sementara' => 'Sementara'])
                    ->required(),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('no_rt_id')
                    ->label('Nomor RT')
                    ->options(NoRt::query()->pluck('nomor', 'no_rt_id'))
                    ->required(),
                Forms\Components\TextInput::make('no_hp')
                    ->tel()
                    ->required()
                    ->maxLength(15),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ]);
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
            'index' => Pages\IndexDataWarga::route('/'),
            'create' => Pages\CreateDataWarga::route('/create'),
            'edit' => Pages\EditDataWarga::route('/{record:warga_nik}/edit'),
            'view' => Pages\ViewDataWarga::route('/{record:warga_nik}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Jika user memiliki role tamu
        if ($user && $user->role && $user->role->name === 'tamu') {
            // Jika user sudah memiliki warga_nik, tampilkan hanya data warga tersebut
            if ($user->warga_nik) {
                return parent::getEloquentQuery()->where('warga_nik', $user->warga_nik);
            } else {
                // Jika belum memiliki warga_nik, tampilkan query kosong
                return parent::getEloquentQuery()->where('warga_nik', '');
            }
        }

        // Untuk mencegah input data diri lebih dari satu kali
        // Cek apakah user sudah memiliki data warga
        if ($user && $user->warga_nik) {
            return parent::getEloquentQuery()->where('warga_nik', $user->warga_nik);
        }

        // Untuk role lain, tampilkan semua data
        return parent::getEloquentQuery();
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $user = Auth::user();
        if ($user && $user->warga_nik) {
            return static::getUrl('view', ['record' => $user->warga_nik, ...$parameters], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
        }

        return static::getUrl('create', $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }
}
