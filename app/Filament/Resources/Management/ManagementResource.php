<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\Pages\CreateManagement;
use App\Filament\Resources\Management\Pages\EditManagement;
use App\Filament\Resources\Management\Pages\ListManagement;
use App\Filament\Resources\Management\Schemas\ManagementForm;
use App\Filament\Resources\Management\Tables\ManagementTable;
use App\Models\Management;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ManagementResource extends Resource
{
    protected static ?string $model = Management::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pengurus';

    protected static ?string $modelLabel = 'Pengurus';

    protected static ?string $pluralModelLabel = 'Pengurus';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ManagementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManagementTable::configure($table);
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
            'index' => ListManagement::route('/'),
            'create' => CreateManagement::route('/create'),
            'edit' => EditManagement::route('/{record}/edit'),
        ];
    }
}
