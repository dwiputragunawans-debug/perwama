<?php

namespace App\Filament\Resources\OrganizationHistories;

use App\Filament\Resources\OrganizationHistories\Pages\CreateOrganizationHistory;
use App\Filament\Resources\OrganizationHistories\Pages\EditOrganizationHistory;
use App\Filament\Resources\OrganizationHistories\Pages\ListOrganizationHistories;
use App\Filament\Resources\OrganizationHistories\Schemas\OrganizationHistoryForm;
use App\Filament\Resources\OrganizationHistories\Tables\OrganizationHistoriesTable;
use App\Models\OrganizationHistory;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrganizationHistoryResource extends Resource
{
    protected static ?string $model = OrganizationHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Tentang';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Sejarah';

    protected static ?string $modelLabel = 'Sejarah';

    protected static ?string $pluralModelLabel = 'Sejarah';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return OrganizationHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationHistoriesTable::configure($table);
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
            'index' => ListOrganizationHistories::route('/'),
            'create' => CreateOrganizationHistory::route('/create'),
            'edit' => EditOrganizationHistory::route('/{record}/edit'),
        ];
    }
}
