<?php

namespace App\Filament\Resources\KetuaRts\Schemas;

use App\Models\Warga;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class KetuaRtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('no_rt')
                    ->label('Nomor RT')
                    ->required()
                    ->maxLength(3),
                Select::make('jabatan')
                    ->label('Jabatan')
                    ->options([
                        'Ketua RT' => 'Ketua RT',
                        'Sekretaris RT' => 'Sekretaris RT',
                        'Bendahara RT' => 'Bendahara RT',
                    ])
                    ->default('Ketua RT')
                    ->required(),

                Select::make('id_warga')
                    ->label('Pilih dari Data Warga')
                    ->relationship('warga', 'nama')
                    ->getOptionLabelFromRecordUsing(fn (Warga $record) => "RT {$record->id_rt} - {$record->nama}")
                    ->getSearchResultsUsing(function (string $search) {
                        $user = Auth::user();
                        $tenant = Filament::getTenant();

                        if (!$user || !$user->role) {
                            return [];
                        }

                        $query = Warga::where('nama', 'like', "%{$search}%");

                        // For structural data, admin should be able to access all warga data
                        // regardless of current tenant context
                        if ($user->role->isRW() || $user->role->isAdmin()) {
                            // RW/Admin can select warga from all RTs
                            // No additional filtering needed - show all warga
                        } elseif ($user->role->isRT()) {
                            // RT users should not normally access this form, but if they do,
                            // limit to their own RT
                            if ($tenant) {
                                $query->where('id_rt', $tenant->no_rt)
                                      ->where('rw', str_pad($tenant->rw, 3, '0', STR_PAD_RIGHT));
                            }
                        } else {
                            // Other roles cannot select warga
                            return [];
                        }

                        return $query->limit(50)->get()->mapWithKeys(function ($warga) {
                            return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                        })->toArray();
                    })
                    ->getOptionLabelsUsing(function (array $values) {
                        return Warga::whereIn('id', $values)->get()->mapWithKeys(function ($warga) {
                            return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                        })->toArray();
                    })
                    ->searchable()
                    ->nullable(),
                DatePicker::make('periode_mulai')
                    ->label('Periode Mulai'),
                DatePicker::make('periode_selesai')
                    ->label('Periode Selesai'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
