<?php

namespace App\Filament\Resources\PanelSettings;

use App\Filament\Resources\PanelSettings\Pages\EditPanelSetting;
use App\Filament\Resources\PanelSettings\Pages\ListPanelSettings;
use App\Filament\Resources\PanelSettings\Schemas\PanelSettingForm;
use App\Filament\Resources\PanelSettings\Tables\PanelSettingsTable;
use App\Models\PanelSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PanelSettingResource extends Resource
{
    protected static ?string $model = PanelSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tampilan Panel';

    protected static ?string $modelLabel = 'Tampilan Panel';

    protected static ?string $pluralModelLabel = 'Tampilan Panel';

    /**
     * Pengaturan bersifat tunggal (singleton): tidak ada penambahan record.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PanelSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanelSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPanelSettings::route('/'),
            'edit' => EditPanelSetting::route('/{record}/edit'),
        ];
    }
}
