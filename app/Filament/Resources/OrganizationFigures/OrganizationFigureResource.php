<?php

namespace App\Filament\Resources\OrganizationFigures;

use App\Filament\Resources\OrganizationFigures\Pages\CreateOrganizationFigure;
use App\Filament\Resources\OrganizationFigures\Pages\EditOrganizationFigure;
use App\Filament\Resources\OrganizationFigures\Pages\ListOrganizationFigures;
use App\Filament\Resources\OrganizationFigures\Schemas\OrganizationFigureForm;
use App\Filament\Resources\OrganizationFigures\Tables\OrganizationFiguresTable;
use App\Models\OrganizationFigure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrganizationFigureResource extends Resource
{
    protected static ?string $model = OrganizationFigure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Tokoh Organisasi';

    protected static ?string $modelLabel = 'Tokoh Organisasi';

    protected static ?string $pluralModelLabel = 'Tokoh Organisasi';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OrganizationFigureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationFiguresTable::configure($table);
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
            'index' => ListOrganizationFigures::route('/'),
            'create' => CreateOrganizationFigure::route('/create'),
            'edit' => EditOrganizationFigure::route('/{record}/edit'),
        ];
    }
}
