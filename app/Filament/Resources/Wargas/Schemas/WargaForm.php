<?php

namespace App\Filament\Resources\Wargas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use App\Models\KetuaRt;
use Filament\Schemas\Components\Section as ComponentsSection;

class WargaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nik')
                    ->required(),
                TextInput::make('nama')
                    ->required(),
                Select::make('jenis_kelamin')
                    ->options(['L' => 'L', 'P' => 'P'])
                    ->required(),
                TextInput::make('agama')
                    ->required(),
                Select::make('status_tinggal')
                    ->options(['Tetap' => 'Tetap', 'Kontrak' => 'Kontrak', 'Sementara' => 'Sementara'])
                    ->required(),
                Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
                Select::make('id_rt')
                    ->options(function () {
                        $options = [];
                        for ($i = 1; $i <= 6; $i++) {
                            // Coba kedua format: "1" dan "001"
                            $ketuaRt = KetuaRt::where(function($query) use ($i) {
                                $query->where('no_rt', (string)$i)
                                      ->orWhere('no_rt', str_pad($i, 3, '0', STR_PAD_LEFT));
                            })
                            ->where('is_active', true)
                            ->with('warga')
                            ->first();

                            $rtLabel = 'RT ' . str_pad($i, 2, '0', STR_PAD_LEFT);
                            if ($ketuaRt && $ketuaRt->warga) {
                                $rtLabel .= ' - ' . $ketuaRt->warga->nama;
                            } else {
                                $rtLabel .= ' - (Belum ada ketua)';
                            }

                            $options[str_pad($i, 3, '0', STR_PAD_LEFT)] = $rtLabel;
                        }
                        return $options;
                    })
                    ->required(),
                TextInput::make('rw')
                    ->required(),
                TextInput::make('no_hp'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                
                ComponentsSection::make('Akun Login (Opsional)')
                    ->description('Jika email diisi, akun login akan otomatis dibuat. Password default adalah "password123" jika tidak diisi.')
                    ->schema([
                        TextInput::make('user_password')
                            ->label('Password Login')
                            ->password()
                            ->dehydrated(false)
                            ->helperText('Kosongkan untuk menggunakan "password123" sebagai password default'),
                        TextInput::make('user_password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->dehydrated(false)
                            ->same('user_password'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
