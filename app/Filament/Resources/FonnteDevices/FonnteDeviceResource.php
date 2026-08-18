<?php

namespace App\Filament\Resources\FonnteDevices;

use App\Filament\Resources\FonnteDevices\Pages\CreateFonnteDevice;
use App\Filament\Resources\FonnteDevices\Pages\EditFonnteDevice;
use App\Filament\Resources\FonnteDevices\Pages\ListFonnteDevices;
use App\Filament\Resources\FonnteDevices\Schemas\FonnteDeviceForm;
use App\Filament\Resources\FonnteDevices\Tables\FonnteDevicesTable;
use App\Models\FonnteDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FonnteDeviceResource extends Resource
{
    protected static ?string $model = FonnteDevice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Whatsapp';

    protected static ?string $navigationLabel = 'Akun Whatsapp';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $pluralLabel = 'Perangkat Whatsapp';

    public static function form(Schema $schema): Schema
    {
        return FonnteDeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FonnteDevicesTable::configure($table);
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
            'index' => ListFonnteDevices::route('/'),
            'create' => CreateFonnteDevice::route('/create'),
            'edit' => EditFonnteDevice::route('/{record}/edit'),
        ];
    }
}
