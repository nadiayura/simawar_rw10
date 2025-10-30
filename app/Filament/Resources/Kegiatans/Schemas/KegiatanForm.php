<?php

namespace App\Filament\Resources\Kegiatans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class KegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('tenant_id')
                    ->default(function () {
                        $tenant = Filament::getTenant();
                        return $tenant ? $tenant->id : null;
                    }),

                TextInput::make('nama_kegiatan')
                    ->required(),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->rows(3),

                DatePicker::make('tanggal')
                    ->required(),
                Select::make('jenis_kegiatan')
                    ->options([
            'karang_taruna' => 'Karang taruna',
            'posyandu' => 'Posyandu',
            'posmaja' => 'Posmaja',
            'umum' => 'Umum',
        ])
                    ->required(),

                FileUpload::make('foto_kegiatan')
                    ->label('Foto Kegiatan')
                    ->image()
                    ->directory('public/Kegiatan')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048), // 2MB max
            ]);
    }
}
