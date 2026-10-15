<?php

namespace App\Filament\Resources\WebsiteSettings;

use App\Filament\Resources\WebsiteSettings\Pages\EditWebsiteSetting;
use App\Filament\Resources\WebsiteSettings\Pages\ListWebsiteSettings;
use App\Filament\Resources\WebsiteSettings\Schemas\WebsiteSettingForm;
use App\Filament\Resources\WebsiteSettings\Tables\WebsiteSettingsTable;
use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WebsiteSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Beranda';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Tampilan Website';

    protected static ?string $modelLabel = 'Tampilan Website';

    protected static ?string $pluralModelLabel = 'Tampilan Website';

    /**
     * Pengaturan bersifat tunggal (singleton): tidak ada penambahan record.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return WebsiteSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsiteSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsiteSettings::route('/'),
            'edit' => EditWebsiteSetting::route('/{record}/edit'),
        ];
    }
}
