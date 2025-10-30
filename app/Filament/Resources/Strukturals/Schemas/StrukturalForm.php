<?php

namespace App\Filament\Resources\Strukturals\Schemas;

use App\Models\Warga;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StrukturalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Lengkap'),

                Select::make('id_warga')
                    ->label('Pilih dari Data Warga')
                    ->relationship('warga', 'nama')
                    ->getOptionLabelFromRecordUsing(fn (Warga $record) => "RT {$record->id_rt} - {$record->nama}")
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $warga = Warga::find($state);
                            if ($warga) {
                                // Auto-fill nama field with warga's name
                                $set('nama', $warga->nama);
                            }
                        }
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        $user = Auth::user();

                        if (!$user || !$user->role) {
                            return [];
                        }

                        // PENTING: Untuk admin, selalu tampilkan semua warga tanpa filter RT
                        if ($user->role->isRW() || $user->role->isAdmin()) {
                            // Tampilkan semua warga tanpa filter tenant/RT
                            $query = Warga::where('nama', 'like', "%{$search}%");

                            return $query->limit(50)->get()->mapWithKeys(function ($warga) {
                                return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                            })->toArray();
                        } elseif ($user->role->isRT()) {
                            // RT users should not normally access this form
                            $tenant = Filament::getTenant();
                            if ($tenant) {
                                $query = Warga::where('nama', 'like', "%{$search}%")
                                    ->where('id_rt', $tenant->no_rt)
                                    ->where('rw', str_pad($tenant->rw, 3, '0', STR_PAD_RIGHT));

                                return $query->limit(50)->get()->mapWithKeys(function ($warga) {
                                    return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                                })->toArray();
                            }
                        }

                        // Default: empty results
                        return [];
                    })
                    ->getOptionLabelsUsing(function (array $values) {
                        return Warga::whereIn('id', $values)->get()->mapWithKeys(function ($warga) {
                            return [$warga->id => "RT {$warga->id_rt} - {$warga->nama}"];
                        })->toArray();
                    })
                    ->searchable()
                    ->nullable(),

                Select::make('jabatan')
                    ->required()
                    ->label('Jabatan')
                    ->options([
                        'Ketua RW' => 'Ketua RW',
                        'Sekretaris RW' => 'Sekretaris RW',
                        'Bendahara RW' => 'Bendahara RW',
                        'Ketua RT' => 'Ketua RT',
                        'Sekretaris RT' => 'Sekretaris RT',
                        'Bendahara RT' => 'Bendahara RT'
                    ])
                    ->default('Ketua RW')
                    ->live()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                // Get the current record ID if editing
                                $recordId = request()->route('record');

                                // Get form data to check no_rt
                                $formData = request()->all();
                                $noRt = $formData['data']['no_rt'] ?? null;

                                // For RW positions, no_rt should be null
                                if (in_array($value, ['Ketua RW', 'Sekretaris RW', 'Bendahara RW'])) {
                                    $noRt = null;
                                }

                                // Check for duplicate jabatan + no_rt combination
                                $query = \App\Models\Struktural::where('jabatan', $value)
                                    ->where('is_active', true)
                                    ->when($recordId, function ($query) use ($recordId) {
                                        return $query->where('id', '!=', $recordId);
                                    });

                                // For RW positions, check where no_rt is null
                                if (in_array($value, ['Ketua RW', 'Sekretaris RW', 'Bendahara RW'])) {
                                    $query->whereNull('no_rt');

                                    // Strict validation for RW positions - must be unique across all RTs
                                    $existingRecord = $query->first();

                                    if ($existingRecord) {
                                        $fail("Jabatan {$value} sudah ada dan aktif. Setiap jabatan RW hanya boleh dipegang oleh satu orang.");
                                    }
                                } else {
                                    // For RT positions, check specific RT
                                    if ($noRt) {
                                        $query->where('no_rt', $noRt);

                                        $existingRecord = $query->first();

                                        if ($existingRecord) {
                                            $fail("Jabatan {$value} untuk RT {$noRt} sudah ada dan aktif. Setiap jabatan RT hanya boleh dipegang oleh satu orang per RT.");
                                        }
                                    } else {
                                        $fail("Nomor RT harus diisi untuk jabatan {$value}.");
                                    }
                                }
                            };
                        }
                    ]),

                // Select::make('no_rt')
                //     ->label('Nomor RT')
                //     ->options([
                //         '001' => 'RT 001',
                //         '002' => 'RT 002',
                //         '003' => 'RT 003',
                //         '004' => 'RT 004',
                //         '005' => 'RT 005',
                //         '006' => 'RT 006'
                //     ])
                //     ->nullable()
                //     ->helperText('Kosongkan untuk jabatan tingkat RW (Ketua RW, Sekretaris RW, Bendahara RW)')
                //     ->hidden(fn (callable $get) => in_array($get('jabatan'), ['Ketua RW', 'Sekretaris RW', 'Bendahara RW']))
                //     ->rules([
                //         function () {
                //             return function (string $attribute, $value, \Closure $fail) {
                //                 $formData = request()->all();
                //                 $jabatan = $formData['jabatan'] ?? null;

                //                 // RT positions must have no_rt
                //                 if (in_array($jabatan, ['Ketua RT', 'Sekretaris RT', 'Bendahara RT']) && empty($value)) {
                //                     $fail('Nomor RT wajib diisi untuk jabatan tingkat RT.');
                //                 }

                //                 // RW positions should not have no_rt
                //                 if (in_array($jabatan, ['Ketua RW', 'Sekretaris RW', 'Bendahara RW']) && !empty($value)) {
                //                     $fail('Nomor RT tidak boleh diisi untuk jabatan tingkat RW.');
                //                 }
                //             };
                //         }
                //     ]),

                FileUpload::make('foto')
                    ->required()
                    ->label('Foto Profil')
                    ->directory('public/Struktural')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->maxSize(2048)
                    ->helperText('Upload foto profil (maksimal 2MB, format: JPG, PNG)'),

                TextInput::make('periode_mulai')
                    ->required()
                    ->maxLength(255)
                    ->label('Periode Mulai')
                    ->placeholder('Contoh: 2024')
                    ->helperText('Tahun mulai menjabat'),

                TextInput::make('periode_selesai')
                    ->required()
                    ->maxLength(255)
                    ->label('Periode Selesai')
                    ->placeholder('Contoh: 2027')
                    ->helperText('Tahun selesai menjabat'),

                Toggle::make('is_active')
                    ->default(true)
                    ->label('Status Aktif'),
            ]);
    }
}
